<?php

namespace App\Services\OpenAI;

use App\Services\OpenAI\Exceptions\OpenAiException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Camada única e desacoplada de comunicação com a OpenAI.
 *
 * Responsável apenas pelo transporte HTTP (Responses API e Moderação). Não
 * conhece regras de agendamento nem monta prompts. A chave nunca sai daqui.
 */
class OpenAiClient
{
    /**
     * @return array<string,mixed> config('chat')
     */
    private function config(): array
    {
        return (array) config('chat');
    }

    public function isConfigured(): bool
    {
        $cfg = $this->config();

        return ($cfg['enabled'] ?? false)
            && filled($cfg['openai']['api_key'] ?? null)
            && filled($cfg['openai']['model'] ?? null);
    }

    /**
     * Chama a Responses API.
     *
     * @param  array<string,mixed>  $payload
     * @return array<string,mixed>
     *
     * @throws OpenAiException
     */
    public function responses(array $payload): array
    {
        return $this->send('responses', $payload);
    }

    /**
     * Endpoint de moderação (gratuito). Retorna o primeiro resultado.
     *
     * @return array{flagged:bool,categories:array<string,bool>}
     *
     * @throws OpenAiException
     */
    public function moderate(string $input): array
    {
        $cfg = $this->config();
        $model = (string) ($cfg['moderation']['model'] ?? 'omni-moderation-latest');

        $data = $this->send('moderations', [
            'model' => $model,
            'input' => $input,
        ]);

        $result = $data['results'][0] ?? null;

        return [
            'flagged' => (bool) ($result['flagged'] ?? false),
            'categories' => (array) ($result['categories'] ?? []),
        ];
    }

    /**
     * @param  array<string,mixed>  $payload
     * @return array<string,mixed>
     *
     * @throws OpenAiException
     */
    private function send(string $path, array $payload): array
    {
        // 1) Valida a credencial (restrita a esta classe). Sem chave, aborta.
        $cfg = $this->config()['openai'];
        $apiKey = $cfg['api_key'] ?? null;

        if (! filled($apiKey)) {
            throw new OpenAiException('OpenAI API key ausente.', 'missing_key');
        }

        // 2) Monta a URL do endpoint (base_url + caminho).
        $url = $cfg['base_url'].'/'.ltrim($path, '/');

        // 3) Nº de tentativas em caso de 429 (limite por minuto do projeto).
        $maxAttempts = max(1, (int) ($cfg['max_retries'] ?? 2));

        // 4) Envia com retentativa só para 429, respeitando o Retry-After da API.
        for ($attempt = 1; ; $attempt++) {
            // 4.1) POST; falha de transporte vira erro de rede tratável.
            try {
                $response = $this->request($cfg)->post($url, $payload);
            } catch (\Throwable $e) {
                throw new OpenAiException('Falha de rede ao contatar a OpenAI.', 'network');
            }

            // 4.2) Não é 429: segue para avaliar o resultado.
            if ($response->status() !== 429) {
                break;
            }

            // 4.3) Acabaram as tentativas: sinaliza rate limit.
            if ($attempt >= $maxAttempts) {
                throw new OpenAiException('Limite de requisições da OpenAI atingido.', 'rate_limited', 429);
            }

            // 4.4) Espera o tempo recomendado e tenta de novo.
            usleep($this->retryDelayMicros($response, $attempt));
        }

        // 5) Credencial inválida (chave errada/sem permissão).
        if ($response->status() === 401 || $response->status() === 403) {
            throw new OpenAiException('Credencial da OpenAI inválida.', 'unauthorized', $response->status());
        }

        // 6) Qualquer outro status fora de 2xx é erro HTTP.
        if (! $response->successful()) {
            throw new OpenAiException('Resposta de erro da OpenAI.', 'http_error', $response->status());
        }

        // 7) Garante corpo JSON válido antes de devolver.
        $json = $response->json();
        if (! is_array($json)) {
            throw new OpenAiException('Resposta inválida da OpenAI.', 'invalid_response');
        }

        return $json;
    }

    /**
     * Tempo de espera (em microssegundos) antes de tentar de novo após um 429.
     * Prioriza o header Retry-After da OpenAI; senão usa backoff exponencial.
     * Limitado a 5s para não segurar a requisição do cliente por muito tempo.
     */
    private function retryDelayMicros(\Illuminate\Http\Client\Response $response, int $attempt): int
    {
        $retryAfter = (float) ($response->header('Retry-After') ?: 0);

        $seconds = $retryAfter > 0
            ? $retryAfter
            : min(5.0, 0.5 * (2 ** ($attempt - 1)));

        return (int) (min($seconds, 5.0) * 1_000_000);
    }

    /**
     * @param  array<string,mixed>  $cfg
     */
    private function request(array $cfg): PendingRequest
    {
        $headers = [];
        if (filled($cfg['organization'] ?? null)) {
            $headers['OpenAI-Organization'] = $cfg['organization'];
        }
        if (filled($cfg['project'] ?? null)) {
            $headers['OpenAI-Project'] = $cfg['project'];
        }

        return Http::withToken((string) $cfg['api_key'])
            ->withHeaders($headers)
            ->timeout((int) ($cfg['timeout'] ?? 20))
            ->connectTimeout((int) ($cfg['connect_timeout'] ?? 5))
            ->retry(
                max(1, (int) ($cfg['max_retries'] ?? 2)),
                200,
                function (\Throwable $e, PendingRequest $request) {
                    // Retry apenas em falhas transitórias de conexão.
                    return $e instanceof \Illuminate\Http\Client\ConnectionException;
                },
                throw: false
            )
            ->acceptJson();
    }
}
