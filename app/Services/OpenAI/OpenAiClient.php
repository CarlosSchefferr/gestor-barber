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
        $cfg = $this->config()['openai'];
        $apiKey = $cfg['api_key'] ?? null;

        if (! filled($apiKey)) {
            throw new OpenAiException('OpenAI API key ausente.', 'missing_key');
        }

        $url = $cfg['base_url'].'/'.ltrim($path, '/');

        try {
            $response = $this->request($cfg)->post($url, $payload);
        } catch (\Throwable $e) {
            throw new OpenAiException('Falha de rede ao contatar a OpenAI.', 'network');
        }

        if ($response->status() === 429) {
            throw new OpenAiException('Limite de requisições da OpenAI atingido.', 'rate_limited', 429);
        }

        if ($response->status() === 401 || $response->status() === 403) {
            throw new OpenAiException('Credencial da OpenAI inválida.', 'unauthorized', $response->status());
        }

        if (! $response->successful()) {
            throw new OpenAiException('Resposta de erro da OpenAI.', 'http_error', $response->status());
        }

        $json = $response->json();
        if (! is_array($json)) {
            throw new OpenAiException('Resposta inválida da OpenAI.', 'invalid_response');
        }

        return $json;
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
