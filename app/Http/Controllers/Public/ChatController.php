<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\ChatConfirmRequest;
use App\Http\Requests\Chat\ChatMessageRequest;
use App\Http\Requests\Chat\ChatProposalCustomerRequest;
use App\Models\AgendaConfig;
use App\Models\ChatBookingProposal;
use App\Models\ChatSession;
use App\Services\Agenda\Exceptions\SlotUnavailableException;
use App\Services\Chat\ChatBookingService;
use App\Services\Chat\ChatOrchestrator;
use App\Services\Chat\ChatSessionManager;
use App\Services\Chat\Exceptions\ChatBookingException;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function __construct(
        private readonly ChatOrchestrator $orchestrator,
        private readonly ChatSessionManager $sessions,
        private readonly ChatBookingService $bookingService,
    ) {}

    /**
     * Inicia uma sessão de chat para a barbearia da página pública.
     */
    public function start(Request $request, string $publicToken): JsonResponse
    {
        $config = $this->resolveConfig($publicToken);

        if (! $this->orchestrator->available()) {
            return response()->json([
                'ok' => true,
                'ai_enabled' => false,
                'message' => 'Atendimento por IA indisponível. Use o agendamento tradicional.',
            ]);
        }

        $session = $this->sessions->start($config, $request->ip());

        return response()->json([
            'ok' => true,
            'ai_enabled' => true,
            'session_token' => $session->session_token,
            'greeting' => 'Olá! 👋 Sou o assistente da '.($config->nome_barbearia ?: 'barbearia')
                .'. Posso te ajudar a agendar um horário. O que você gostaria de fazer hoje?',
        ]);
    }

    /**
     * Recebe uma mensagem do cliente e devolve a resposta do assistente.
     */
    public function message(ChatMessageRequest $request, string $publicToken): JsonResponse
    {
        $config = $this->resolveConfig($publicToken);

        if (! $this->orchestrator->available()) {
            return response()->json(['ok' => false, 'ai_enabled' => false, 'message' => 'Atendimento por IA indisponível.'], 503);
        }

        $session = $this->resolveActiveSession($config, $request->validated('session_token'));
        if (! $session) {
            return response()->json(['ok' => false, 'error' => 'session_expired', 'message' => 'Sua sessão expirou. Recarregue para começar de novo.'], 410);
        }

        if ($this->sessions->reachedMessageLimit($session)) {
            return response()->json(['ok' => false, 'error' => 'message_limit', 'message' => 'Chegamos ao limite desta conversa. Você pode usar o agendamento tradicional abaixo.'], 429);
        }

        try {
            $result = $this->orchestrator->converse($config, $session, $request->validated('message'));
        } catch (\Throwable $e) {
            Log::error('chat.message_failed', ['session' => $session->id, 'message' => $e->getMessage()]);

            return response()->json([
                'ok' => true,
                'assistant' => 'Tive um problema para responder agora. Você pode tentar de novo ou usar o agendamento tradicional logo abaixo.',
                'ui' => [],
                'status' => 'error',
            ]);
        }

        return response()->json([
            'ok' => true,
            'assistant' => $result['assistant'],
            'ui' => $result['ui'],
            'status' => $result['status'],
            'message_count' => $session->fresh()->message_count,
        ]);
    }

    /**
     * Anexa os dados pessoais (coletados em campo seguro) a uma proposta.
     * Esses dados nunca são enviados ao modelo.
     */
    public function proposalCustomer(ChatProposalCustomerRequest $request, string $publicToken): JsonResponse
    {
        $config = $this->resolveConfig($publicToken);
        $session = $this->resolveActiveSession($config, $request->validated('session_token'));
        if (! $session) {
            return response()->json(['ok' => false, 'error' => 'session_expired'], 410);
        }

        $proposal = $this->resolveProposal($config, $session, $request->validated('proposal_token'));
        if (! $proposal) {
            return response()->json(['ok' => false, 'error' => 'proposal_invalid', 'message' => 'Proposta inválida ou expirada.'], 422);
        }

        try {
            $this->bookingService->attachCustomer($proposal, [
                'nome' => $request->validated('nome'),
                'email' => $request->validated('email'),
                'telefone' => $request->validated('telefone'),
                'observacoes' => $request->validated('observacoes'),
            ]);
        } catch (ChatBookingException $e) {
            return response()->json(['ok' => false, 'error' => $e->reason, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Cria o agendamento de forma transacional e idempotente após confirmação
     * explícita do frontend.
     */
    public function confirm(ChatConfirmRequest $request, string $publicToken): JsonResponse
    {
        $config = $this->resolveConfig($publicToken);
        $session = $this->resolveActiveSession($config, $request->validated('session_token'));
        if (! $session) {
            return response()->json(['ok' => false, 'error' => 'session_expired'], 410);
        }

        $proposal = $this->resolveProposal($config, $session, $request->validated('proposal_token'), allowConfirmed: true);
        if (! $proposal) {
            return response()->json(['ok' => false, 'error' => 'proposal_invalid', 'message' => 'Proposta inválida ou expirada.'], 422);
        }

        try {
            $agendamento = $this->bookingService->confirm($proposal, $request->validated('idempotency_key'));
        } catch (SlotUnavailableException $e) {
            return response()->json([
                'ok' => false,
                'error' => 'slot_unavailable',
                'message' => $e->getMessage() ?: 'Esse horário acabou de ficar indisponível. Vamos escolher outro?',
            ], 409);
        } catch (ChatBookingException $e) {
            return response()->json(['ok' => false, 'error' => $e->reason, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            Log::error('chat.confirm_failed', ['session' => $session->id, 'message' => $e->getMessage()]);

            return response()->json(['ok' => false, 'error' => 'internal', 'message' => 'Não foi possível concluir agora. Tente novamente.'], 500);
        }

        $agendamento->loadMissing('barbeiro');

        return response()->json([
            'ok' => true,
            'agendamento' => [
                'id' => $agendamento->id,
                'servico' => $agendamento->servico,
                'profissional' => $agendamento->barbeiro?->professional_name ?: $agendamento->barbeiro?->name,
                'data' => CarbonImmutable::parse($agendamento->starts_at)->format('d/m/Y'),
                'inicio' => CarbonImmutable::parse($agendamento->starts_at)->format('H:i'),
                'fim' => $agendamento->ends_at ? CarbonImmutable::parse($agendamento->ends_at)->format('H:i') : null,
                'preco_label' => $agendamento->price !== null ? 'R$ '.number_format((float) $agendamento->price, 2, ',', '.') : null,
            ],
        ]);
    }

    private function resolveConfig(string $publicToken): AgendaConfig
    {
        return AgendaConfig::query()
            ->where('public_token', $publicToken)
            ->where('ativa', true)
            ->firstOrFail();
    }

    private function resolveActiveSession(AgendaConfig $config, string $token): ?ChatSession
    {
        $session = $this->sessions->resolve($config, $token);

        if (! $session || ! $session->isActive()) {
            return null;
        }

        return $session;
    }

    private function resolveProposal(AgendaConfig $config, ChatSession $session, string $token, bool $allowConfirmed = false): ?ChatBookingProposal
    {
        $proposal = ChatBookingProposal::query()
            ->where('agenda_config_id', $config->id)
            ->where('chat_session_id', $session->id)
            ->where('token', $token)
            ->first();

        if (! $proposal) {
            return null;
        }

        if ($allowConfirmed && $proposal->status === 'confirmed') {
            return $proposal;
        }

        return $proposal->isPending() ? $proposal : null;
    }
}
