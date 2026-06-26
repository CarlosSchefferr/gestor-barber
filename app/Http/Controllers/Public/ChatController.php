<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\ChatConfirmRequest;
use App\Http\Requests\Chat\ChatMessageRequest;
use App\Http\Requests\Chat\ChatProposalCustomerRequest;
use App\Models\AgendaConfig;
use App\Models\ChatBookingProposal;
use App\Models\ChatSession;
use App\Models\Service;
use App\Services\Agenda\AvailabilityService;
use App\Services\Agenda\Exceptions\SlotUnavailableException;
use App\Services\Chat\ChatBookingService;
use App\Services\Chat\ChatOrchestrator;
use App\Services\Chat\ChatSessionManager;
use App\Services\Chat\Exceptions\ChatBookingException;
use App\Services\Chat\ProposalBuilder;
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
        private readonly AvailabilityService $availability,
        private readonly ProposalBuilder $proposalBuilder,
    ) {}

    /**
     * Inicia uma sessão de chat para a barbearia da página pública.
     */
    public function start(Request $request, string $publicToken): JsonResponse
    {
        $config = $this->resolveConfig($publicToken);

        // A sessão é sempre criada: o montador de agendamento do site usa o
        // fluxo de proposta/confirmação mesmo quando a IA está desabilitada.
        // A IA controla apenas o atendimento conversacional.
        $session = $this->sessions->start($config, $request->ip());
        $aiEnabled = $this->orchestrator->available();

        return response()->json([
            'ok' => true,
            'ai_enabled' => $aiEnabled,
            'session_token' => $session->session_token,
            'greeting' => $aiEnabled
                ? 'Oi! 👋 Aqui é da '.($config->nome_barbearia ?: 'barbearia')
                    .' 💈 Que bom te ver por aqui! Quer marcar um horário? Me conta o que você precisa que eu te ajudo.'
                : null,
        ]);
    }

    /**
     * Recebe uma mensagem do cliente e devolve a resposta do assistente.
     */
    public function message(ChatMessageRequest $request, string $publicToken): JsonResponse
    {
        // ETAPA 1 — Resolve a barbearia a partir do token público da URL.
        // Se o token não corresponder a uma agenda ativa, dispara 404.
        $config = $this->resolveConfig($publicToken);

        // ETAPA 2 — Verifica se a IA está configurada/disponível.
        // Sem chave/modelo configurados, devolvemos 503 e o frontend cai no
        // agendamento tradicional.
        if (! $this->orchestrator->available()) {
            return response()->json(['ok' => false, 'ai_enabled' => false, 'message' => 'Atendimento por IA indisponível.'], 503);
        }

        // ETAPA 3 — Valida a sessão informada pelo cliente.
        // A sessão precisa existir e estar ativa (não expirada). Caso contrário,
        // 410 sinaliza ao frontend que ele deve recarregar e começar de novo.
        $session = $this->resolveActiveSession($config, $request->validated('session_token'));
        if (! $session) {
            return response()->json(['ok' => false, 'error' => 'session_expired', 'message' => 'Sua sessão expirou. Recarregue para começar de novo.'], 410);
        }

        // ETAPA 4 — Aplica o limite de mensagens por conversa.
        // Protege contra abuso e custo: ao atingir o teto, 429 encerra o canal
        // de IA e oferece o agendamento tradicional.
        if ($this->sessions->reachedMessageLimit($session)) {
            return response()->json(['ok' => false, 'error' => 'message_limit', 'message' => 'Chegamos ao limite desta conversa. Você pode usar o agendamento tradicional abaixo.'], 429);
        }

        // ETAPA 5 — Delega a conversa ao orquestrador.
        // É ele que fala com o modelo, executa as ferramentas e devolve texto +
        // estruturas. Qualquer exceção inesperada é registrada e convertida em
        // uma resposta amigável (status 200 com texto de fallback), para a
        // interface nunca quebrar diante de um erro do backend.
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

        // ETAPA 6 — Devolve a resposta ao frontend.
        // Inclui o texto do assistente, as estruturas de UI e o total atualizado
        // de mensagens (lido com fresh() para refletir o que acabou de ser gravado).
        return response()->json([
            'ok' => true,
            'assistant' => $result['assistant'],
            'ui' => $result['ui'],
            'status' => $result['status'],
            'message_count' => $session->fresh()->message_count,
        ]);
    }

    /**
     * Cria uma proposta a partir de uma seleção montada no site (serviço,
     * profissional, data e hora explícitos). Robusto: não depende de o modelo
     * interpretar a mensagem. A interface mostra o resumo e o cliente confirma.
     */
    public function prepareFromSelection(Request $request, string $publicToken): JsonResponse
    {
        $config = $this->resolveConfig($publicToken);

        $data = $request->validate([
            'session_token' => 'required|uuid',
            'service_id' => 'required|integer',
            'professional_id' => 'nullable|integer',
            'data' => 'required|date_format:Y-m-d',
            'hora' => 'required|date_format:H:i',
        ]);

        $session = $this->resolveActiveSession($config, $data['session_token']);
        if (! $session) {
            return response()->json(['ok' => false, 'error' => 'session_expired'], 410);
        }

        $service = Service::where('active', true)->find($data['service_id']);
        if (! $service) {
            return response()->json(['ok' => false, 'message' => 'Serviço indisponível.'], 422);
        }

        $professional = null;
        if (! empty($data['professional_id'])) {
            $professional = $this->availability->professionalsForService($config, $service)
                ->firstWhere('id', (int) $data['professional_id']);
            if (! $professional) {
                return response()->json(['ok' => false, 'message' => 'Profissional indisponível.'], 422);
            }
        }

        try {
            $start = CarbonImmutable::createFromFormat('Y-m-d H:i', $data['data'].' '.$data['hora'], $this->availability->timezone());
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => 'Data ou hora inválida.'], 422);
        }

        $built = $this->proposalBuilder->build($config, $session, $service, $professional, $start);
        if (! $built) {
            return response()->json(['ok' => false, 'error' => 'slot_unavailable', 'message' => 'Esse horário acabou de ficar indisponível. Escolha outro.'], 409);
        }

        $proposal = $built['proposal'];

        return response()->json([
            'ok' => true,
            'proposal' => array_merge($built['summary'], [
                'token' => $proposal->token,
                'expires_at' => $proposal->expires_at->toIso8601String(),
                'missing_fields' => ['nome', 'email', 'telefone'],
            ]),
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
            ->forPublicIdentifier($publicToken)
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
