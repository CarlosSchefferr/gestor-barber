<?php

namespace App\Services\Chat;

use App\Models\AgendaConfig;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Services\Chat\Support\PiiMasker;
use Illuminate\Support\Str;

/**
 * Criação, resolução e persistência de sessões/mensagens do chat. Garante o
 * isolamento por barbearia: uma sessão pertence a uma AgendaConfig e nunca
 * pode ser usada por outra.
 */
class ChatSessionManager
{
    // Cria a sessão: token único, estado vazio, IP só como hash e validade (TTL) a partir de agora.
    public function start(AgendaConfig $config, ?string $ip, string $locale = 'pt_BR'): ChatSession
    {
        $ttl = (int) config('chat.limits.session_ttl_minutes', 120);

        return ChatSession::create([
            'agenda_config_id' => $config->id,
            'session_token' => (string) Str::uuid(),
            'status' => 'active',
            'state' => $this->emptyState(),
            'locale' => $locale,
            'ip_hash' => $ip ? hash('sha256', $ip) : null,
            'message_count' => 0,
            'last_activity_at' => now(),
            'expires_at' => now()->addMinutes($ttl),
        ]);
    }

    /**
     * Resolve a sessão SOMENTE dentro do escopo da AgendaConfig informada.
     */
    public function resolve(AgendaConfig $config, string $token): ?ChatSession
    {
        return ChatSession::query()
            ->where('agenda_config_id', $config->id)
            ->where('session_token', $token)
            ->first();
    }

    // Renova a atividade e empurra a expiração para frente (mantém a sessão viva durante o uso).
    public function touch(ChatSession $session): void
    {
        $ttl = (int) config('chat.limits.session_ttl_minutes', 120);
        $session->forceFill([
            'last_activity_at' => now(),
            'expires_at' => now()->addMinutes($ttl),
        ])->save();
    }

    // Grava a mensagem do cliente (com PII mascarada) e conta para o limite por sessão.
    public function recordUserMessage(ChatSession $session, string $content): ChatMessage
    {
        $session->increment('message_count');

        return $session->messages()->create([
            'role' => 'user',
            'content' => PiiMasker::mask($content),
        ]);
    }

    // Grava a resposta do assistente (não conta no limite, que é só de falas do cliente).
    public function recordAssistantMessage(ChatSession $session, string $content, array $meta = []): ChatMessage
    {
        return $session->messages()->create([
            'role' => 'assistant',
            'content' => $content,
            'meta' => $meta ?: null,
        ]);
    }

    /**
     * Histórico recente já mascarado, pronto para o modelo.
     *
     * @return array<int,array{role:string,content:string}>
     */
    public function historyForModel(ChatSession $session): array
    {
        // Pega as N falas mais recentes (janela), inverte para ordem cronológica e remascara a PII.
        $window = (int) config('chat.limits.history_window', 16);

        return $session->messages()
            ->whereIn('role', ['user', 'assistant'])
            ->orderByDesc('id')
            ->limit($window)
            ->get(['role', 'content'])
            ->reverse()
            ->map(fn (ChatMessage $m) => [
                'role' => $m->role,
                'content' => PiiMasker::mask((string) $m->content),
            ])
            ->values()
            ->all();
    }

    public function close(ChatSession $session): void
    {
        $session->update(['status' => 'closed']);
    }

    // Teto de mensagens do cliente por sessão (trava contra abuso/custo).
    public function reachedMessageLimit(ChatSession $session): bool
    {
        $max = (int) config('chat.limits.max_messages_per_session', 40);

        return $session->message_count >= $max;
    }

    // Estado inicial da reserva: tudo vazio, ainda "coletando" as escolhas do cliente.
    private function emptyState(): array
    {
        return [
            'intent' => null,
            'service_id' => null,
            'professional_id' => null,
            'date' => null,
            'time' => null,
            'proposal_token' => null,
            'status' => 'collecting',
        ];
    }
}
