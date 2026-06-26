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
        // 1) Modera a entrada: conteúdo impróprio nem chega ao modelo principal.
        if ($this->isFlagged($userMessage)) {
            $reply = 'Desculpe, não posso ajudar com isso. Posso te ajudar a agendar um horário?';
            $this->sessions->recordAssistantMessage($session, $reply);

            return ['assistant' => $reply, 'ui' => [], 'status' => 'moderated'];
        }

        // 2) Grava a mensagem do cliente e renova a sessão (evita expirar no meio).
        $this->sessions->recordUserMessage($session, $userMessage);
        $this->sessions->touch($session);

        // 3) Contexto que as ferramentas usam para ler o banco (o modelo nunca lê).
        $context = new ToolContext($config, $session, $this->availability);

        // 4) Restaura a seleção da reserva salva entre turnos (serviço/profissional/data/hora).
        $selection = is_array($session->state) ? $session->state : [];

        // 5) Instruções = system prompt + contexto temporal + catálogo + seleção atual.
        $instructions = $this->buildInstructions($config, $selection);

        // 6) Converte o histórico para o formato da Responses API (memória de curto prazo).
        $input = [];
        foreach ($this->sessions->historyForModel($session) as $msg) {
            $input[] = ['role' => $msg['role'], 'content' => $msg['content']];
        }

        // 7) Prepara o loop de function calling (limite de iterações evita custo/loop infinito).
        $maxIterations = (int) config('chat.limits.max_tool_iterations', 4);
        $ui = [];
        $toolCallCount = 0;
        $assistantText = '';

        // 8) Conversa com o modelo: repete enquanto ele pedir ferramentas; para no texto final.
        for ($iteration = 0; $iteration <= $maxIterations; $iteration++) {
            $startedAt = microtime(true);

            // 8.1) Chama o modelo; em erro da OpenAI, registra consumo e cai no fallback.
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

            // 8.2) Contabiliza tokens/latência desta chamada (controle de custo).
            $this->recordUsage($session, $config, $response, $startedAt, 'ok', $toolCallCount);

            // 8.3) Separa a saída em pedidos de ferramenta (function_call) e texto (message).
            $functionCalls = [];
            $iterationText = '';
            foreach (($response['output'] ?? []) as $item) {
                $type = $item['type'] ?? null;
                if ($type === 'function_call') {
                    $functionCalls[] = $item;
                } elseif ($type === 'message') {
                    $iterationText .= $this->extractText($item);
                }
            }

            // 8.4) O texto mais recente prevalece (evita duplicar preâmbulo + resposta final).
            if (trim($iterationText) !== '') {
                $assistantText = $iterationText;
            }

            // 8.5) Sem ferramentas a executar = resposta final; encerra o turno.
            if (empty($functionCalls)) {
                break;
            }

            // 8.6) Trava de segurança: estourou o limite de iterações, encerra pedindo objetividade.
            if ($iteration >= $maxIterations) {
                $assistantText = $assistantText ?: 'Vamos com calma: me diga em uma frase o que você precisa agendar.';
                break;
            }

            // 8.7) Executa cada ferramenta, acumula UI/seleção e devolve chamada+resultado ao modelo.
            foreach ($functionCalls as $call) {
                $toolCallCount++;
                [$outputJson, $callUi] = $this->executeTool($call, $context);
                $ui = array_merge($ui, $callUi);
                $selection = $this->mergeSelection($selection, $call);

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

        // 9) Nunca devolve texto vazio: usa uma frase neutra como fallback.
        $assistantText = trim($assistantText) ?: 'Como posso te ajudar com seu agendamento?';

        // 10) Salva a resposta no histórico.
        $this->sessions->recordAssistantMessage($session, $assistantText);

        // 11) Persiste a seleção da reserva para o próximo turno continuar de onde parou.
        $session->forceFill(['state' => $selection])->save();

        // 12) Retorna ao controller: texto do chat + estruturas de UI + status do turno.
        return ['assistant' => $assistantText, 'ui' => $ui, 'status' => 'ok'];
    }

    /**
     * Captura serviço/profissional/data/hora dos argumentos de uma tool call.
     *
     * @param  array<string,mixed>  $selection
     * @param  array<string,mixed>  $call
     * @return array<string,mixed>
     */
    private function mergeSelection(array $selection, array $call): array
    {
        $args = json_decode((string) ($call['arguments'] ?? '{}'), true);
        if (! is_array($args)) {
            return $selection;
        }

        if (array_key_exists('service_id', $args) && $args['service_id']) {
            $selection['service_id'] = (int) $args['service_id'];
        }
        if (array_key_exists('professional_id', $args)) {
            // null = "qualquer profissional" (seleção válida).
            $selection['professional_id'] = $args['professional_id'] !== null ? (int) $args['professional_id'] : null;
            $selection['professional_any'] = $args['professional_id'] === null;
        }
        if (array_key_exists('data', $args) && is_string($args['data']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $args['data'])) {
            $selection['data'] = $args['data'];
        }
        if (array_key_exists('hora', $args) && is_string($args['hora']) && preg_match('/^\d{2}:\d{2}$/', $args['hora'])) {
            $selection['hora'] = $args['hora'];
        }

        return $selection;
    }

    /**
     * Bloco de contexto com a seleção atual, para a IA não perder o fio.
     *
     * @param  array<string,mixed>  $selection
     */
    private function stateBlock(array $selection): string
    {
        $linhas = [];

        if (! empty($selection['service_id'])) {
            $svc = \App\Models\Service::where('active', true)->find($selection['service_id']);
            if ($svc) {
                $linhas[] = '- Serviço escolhido: '.$svc->name;
            }
        }
        if (array_key_exists('professional_id', $selection)) {
            if (! empty($selection['professional_any'])) {
                $linhas[] = '- Profissional: qualquer disponível';
            } elseif (! empty($selection['professional_id'])) {
                $prof = \App\Models\User::find($selection['professional_id']);
                if ($prof) {
                    $linhas[] = '- Profissional escolhido: '.($prof->professional_name ?: $prof->name);
                }
            }
        }
        if (! empty($selection['data'])) {
            // Mostra a data de forma legível (ex.: "29/06/2026 (segunda-feira)") para a IA citá-la com naturalidade.
            try {
                $d = CarbonImmutable::createFromFormat('Y-m-d', $selection['data'], $this->availability->timezone());
                $linhas[] = '- Data escolhida: '.$d->format('d/m/Y').' ('.$d->translatedFormat('l').')';
            } catch (\Throwable $e) {
                $linhas[] = '- Data escolhida: '.$selection['data'];
            }
        }
        if (! empty($selection['hora'])) {
            $linhas[] = '- Horário escolhido: '.$selection['hora'];
        }

        if (empty($linhas)) {
            return "\n\nSELEÇÃO ATUAL\n- Nada escolhido ainda.";
        }

        return "\n\nSELEÇÃO ATUAL (não pergunte de novo o que já está aqui)\n".implode("\n", $linhas);
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

        // Monta o corpo enviado à Responses API. Cada campo significa:
        // - model: qual modelo da OpenAI usar (vem da config).
        // - instructions: o system prompt completo (regras + catálogo + estado).
        // - input: o histórico/turnos da conversa a serem considerados.
        // - tools: as ferramentas que o modelo PODE pedir para executar.
        // - tool_choice 'auto': o modelo decide se chama ferramenta ou responde.
        // - parallel_tool_calls false: uma ferramenta por vez, para o backend
        //   validar cada passo em ordem e manter o estado coerente.
        // - store false: a OpenAI não retém a conversa; o histórico é nosso.
        // - max_output_tokens: teto de tamanho da resposta (controle de custo).
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

    private function buildInstructions(AgendaConfig $config, array $selection = []): string
    {
        $tz = $this->availability->timezone();
        $now = CarbonImmutable::now($tz);

        return SystemPrompt::build($config)
            ."\n\nCONTEXTO TEMPORAL\n- Fuso: {$tz}.\n- Agora: ".$now->format('Y-m-d H:i')." ({$now->translatedFormat('l')})."
            .$this->catalogBlock($config)
            .$this->stateBlock($selection);
    }

    /**
     * Catálogo compacto (serviços e profissionais) com IDs estáveis, para a IA
     * sempre saber qual service_id/professional_id usar ao chamar as ferramentas,
     * sem perder o vínculo entre os turnos. Disponibilidade NÃO entra aqui — ela
     * vem sempre das ferramentas.
     */
    private function catalogBlock(AgendaConfig $config): string
    {
        $servicos = \App\Models\Service::where('active', true)->orderBy('name')->get(['id', 'name', 'price', 'duration']);
        $profissionais = $this->availability->professionals($config);

        $svcLinhas = $servicos->map(function ($s) {
            $preco = 'R$ '.number_format((float) $s->price, 2, ',', '.');

            return "  [{$s->id}] {$s->name} — {$preco} — {$s->duration} min";
        })->implode("\n");

        $profLinhas = $profissionais->map(function ($p) {
            $nome = $p->professional_name ?: $p->name;

            return "  [{$p->id}] {$nome}";
        })->implode("\n");

        return "\n\nCATÁLOGO (use exatamente estes IDs ao chamar ferramentas; preços oficiais)\n"
            ."Serviços:\n{$svcLinhas}\n"
            ."Profissionais:\n{$profLinhas}\n"
            .'Regra: para descobrir QUEM atende um serviço e QUAIS datas/horários existem, use sempre as ferramentas. '
            .'O ID do serviço e do profissional vêm deste catálogo.';
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
