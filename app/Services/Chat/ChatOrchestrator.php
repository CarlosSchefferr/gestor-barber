<?php

namespace App\Services\Chat;

use App\Models\AgendaConfig;
use App\Models\ChatSession;
use App\Models\ChatToolCall;
use App\Models\ChatUsage;
use App\Services\Agenda\AvailabilityService;
use App\Services\Chat\Tools\ToolContext;
use App\Services\Chat\Tools\ToolRegistry;
use App\Services\OpenAI\Exceptions\OpenAiException;
use App\Services\OpenAI\OpenAiClient;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;

/**
 * Orquestra a conversa: monta a requisição da Responses API, executa o loop
 * de function calling com tools controladas pelo Laravel, registra consumo e
 * auditoria, e devolve texto + estruturas para a interface.
 *
 * O modelo nunca acessa o banco: apenas solicita tools, que o backend valida
 * e executa.
 */
class ChatOrchestrator
{
    public function __construct(
        private readonly OpenAiClient $client,
        private readonly ToolRegistry $tools,
        private readonly ChatSessionManager $sessions,
        private readonly AvailabilityService $availability,
    ) {}

    public function available(): bool
    {
        return $this->client->isConfigured();
    }

    /**
     * @return array{assistant:string,ui:array<string,mixed>,status:string}
     */
    public function converse(AgendaConfig $config, ChatSession $session, string $userMessage): array
    {
        // 1. Moderação de entrada.
        if ($this->isFlagged($userMessage)) {
            $reply = 'Desculpe, não posso ajudar com isso. Posso te ajudar a agendar um horário?';
            $this->sessions->recordAssistantMessage($session, $reply);

            return ['assistant' => $reply, 'ui' => [], 'status' => 'moderated'];
        }

        $this->sessions->recordUserMessage($session, $userMessage);
        $this->sessions->touch($session);

        $context = new ToolContext($config, $session, $this->availability);
        $instructions = $this->buildInstructions($config);

        $input = [];
        foreach ($this->sessions->historyForModel($session) as $msg) {
            $input[] = ['role' => $msg['role'], 'content' => $msg['content']];
        }

        $maxIterations = (int) config('chat.limits.max_tool_iterations', 4);
        $ui = [];
        $toolCallCount = 0;
        $assistantText = '';

        for ($iteration = 0; $iteration <= $maxIterations; $iteration++) {
            $startedAt = microtime(true);

            try {
                $response = $this->client->responses($this->payload($instructions, $input));
            } catch (OpenAiException $e) {
                Log::warning('chat.openai_error', [
                    'session' => $session->id,
                    'reason' => $e->reason,
                    'status' => $e->status,
                ]);
                $this->recordUsage($session, $config, null, $startedAt, $e->reason, $toolCallCount);

                return [
                    'assistant' => 'Estou com uma instabilidade no atendimento automático agora. Você pode usar o agendamento tradicional logo abaixo ou tentar novamente em instantes.',
                    'ui' => $ui,
                    'status' => 'ai_error',
                ];
            }

            $this->recordUsage($session, $config, $response, $startedAt, 'ok', $toolCallCount);

            $functionCalls = [];
            foreach (($response['output'] ?? []) as $item) {
                $type = $item['type'] ?? null;
                if ($type === 'function_call') {
                    $functionCalls[] = $item;
                } elseif ($type === 'message') {
                    $assistantText .= $this->extractText($item);
                }
            }

            if (empty($functionCalls)) {
                break;
            }

            if ($iteration >= $maxIterations) {
                $assistantText = $assistantText ?: 'Vamos com calma: me diga em uma frase o que você precisa agendar.';
                break;
            }

            foreach ($functionCalls as $call) {
                $toolCallCount++;
                [$outputJson, $callUi] = $this->executeTool($call, $context);
                $ui = array_merge($ui, $callUi);

                // Reenvia o function_call e seu resultado ao modelo.
                $input[] = [
                    'type' => 'function_call',
                    'call_id' => $call['call_id'] ?? '',
                    'name' => $call['name'] ?? '',
                    'arguments' => $call['arguments'] ?? '{}',
                ];
                $input[] = [
                    'type' => 'function_call_output',
                    'call_id' => $call['call_id'] ?? '',
                    'output' => $outputJson,
                ];
            }
        }

        $assistantText = trim($assistantText) ?: 'Como posso te ajudar com seu agendamento?';
        $this->sessions->recordAssistantMessage($session, $assistantText);

        return ['assistant' => $assistantText, 'ui' => $ui, 'status' => 'ok'];
    }

    /**
     * @return array{0:string,1:array<string,mixed>}
     */
    private function executeTool(array $call, ToolContext $context): array
    {
        $name = (string) ($call['name'] ?? '');
        $startedAt = microtime(true);

        $tool = $this->tools->get($name);
        if (! $tool) {
            $this->auditTool($context->session, $name, [], 'invalid', $startedAt);

            return [json_encode(['error' => 'Ferramenta não disponível.'], JSON_UNESCAPED_UNICODE), []];
        }

        $arguments = json_decode((string) ($call['arguments'] ?? '{}'), true);
        if (! is_array($arguments)) {
            $this->auditTool($context->session, $name, [], 'invalid', $startedAt);

            return [json_encode(['error' => 'Argumentos inválidos.'], JSON_UNESCAPED_UNICODE), []];
        }

        try {
            $result = $tool->handle($arguments, $context);
        } catch (\Throwable $e) {
            Log::warning('chat.tool_error', ['tool' => $name, 'session' => $context->session->id]);
            $this->auditTool($context->session, $name, $arguments, 'error', $startedAt);

            return [json_encode(['error' => 'Não foi possível executar a consulta.'], JSON_UNESCAPED_UNICODE), []];
        }

        $this->auditTool($context->session, $name, $arguments, $result->status, $startedAt);

        return [
            json_encode($result->output, JSON_UNESCAPED_UNICODE) ?: '{}',
            $result->ui,
        ];
    }

    /**
     * @return array<string,mixed>
     */
    private function payload(string $instructions, array $input): array
    {
        $cfg = (array) config('chat.openai');

        return [
            'model' => $cfg['model'],
            'instructions' => $instructions,
            'input' => $input,
            'tools' => $this->tools->definitions(),
            'tool_choice' => 'auto',
            'parallel_tool_calls' => false,
            'store' => false,
            'max_output_tokens' => (int) ($cfg['max_output_tokens'] ?? 500),
        ];
    }

    private function buildInstructions(AgendaConfig $config): string
    {
        $tz = $this->availability->timezone();
        $now = CarbonImmutable::now($tz);

        return SystemPrompt::build($config)
            ."\n\nCONTEXTO TEMPORAL\n- Fuso: {$tz}.\n- Agora: ".$now->format('Y-m-d H:i')." ({$now->translatedFormat('l')}).";
    }

    private function extractText(array $messageItem): string
    {
        $text = '';
        foreach (($messageItem['content'] ?? []) as $part) {
            if (($part['type'] ?? null) === 'output_text') {
                $text .= $part['text'] ?? '';
            }
        }

        return $text;
    }

    private function isFlagged(string $message): bool
    {
        if (! config('chat.moderation.enabled', true)) {
            return false;
        }

        try {
            return $this->client->moderate($message)['flagged'];
        } catch (\Throwable $e) {
            // Moderação indisponível não deve bloquear o atendimento legítimo.
            return false;
        }
    }

    private function auditTool(ChatSession $session, string $tool, array $arguments, string $status, float $startedAt): void
    {
        ChatToolCall::create([
            'chat_session_id' => $session->id,
            'tool' => mb_substr($tool, 0, 60),
            'arguments' => $this->sanitizeArguments($arguments),
            'status' => $status,
            'duration_ms' => (int) ((microtime(true) - $startedAt) * 1000),
        ]);
    }

    private function sanitizeArguments(array $arguments): array
    {
        // As tools não recebem PII por contrato; ainda assim removemos qualquer
        // chave fora da allowlist conhecida por segurança.
        $allowed = ['busca', 'service_id', 'professional_id', 'data', 'hora'];

        return array_intersect_key($arguments, array_flip($allowed));
    }

    private function recordUsage(ChatSession $session, AgendaConfig $config, ?array $response, float $startedAt, string $status, int $toolCalls): void
    {
        $usage = $response['usage'] ?? [];

        ChatUsage::create([
            'chat_session_id' => $session->id,
            'agenda_config_id' => $config->id,
            'usage_date' => now()->toDateString(),
            'model' => $response ? ($response['model'] ?? config('chat.openai.model')) : config('chat.openai.model'),
            'input_tokens' => (int) ($usage['input_tokens'] ?? 0),
            'cached_tokens' => (int) ($usage['input_tokens_details']['cached_tokens'] ?? 0),
            'output_tokens' => (int) ($usage['output_tokens'] ?? 0),
            'tool_calls' => $toolCalls,
            'latency_ms' => (int) ((microtime(true) - $startedAt) * 1000),
            'status' => $status,
            'response_id' => $response['id'] ?? null,
        ]);
    }
}
