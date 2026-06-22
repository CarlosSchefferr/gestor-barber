<?php

namespace App\Console\Commands;

use App\Models\ChatBookingProposal;
use App\Models\ChatSession;
use Illuminate\Console\Command;

/**
 * Expira sessões/propostas vencidas e remove conversas antigas conforme a
 * retenção configurada. Nunca remove agendamentos ou clientes.
 */
class PurgeExpiredChatSessions extends Command
{
    protected $signature = 'chat:purge-expired';

    protected $description = 'Expira sessões/propostas de chat vencidas e remove conversas além da retenção.';

    public function handle(): int
    {
        $now = now();

        $expiredProposals = ChatBookingProposal::query()
            ->where('status', 'pending')
            ->where('expires_at', '<', $now)
            ->update(['status' => 'expired']);

        $expiredSessions = ChatSession::query()
            ->where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', $now)
            ->update(['status' => 'expired']);

        $retentionDays = (int) config('chat.limits.retention_days', 30);
        $cutoff = now()->subDays(max(1, $retentionDays));

        // A exclusão das sessões remove em cascata mensagens, tools e propostas.
        $deleted = ChatSession::query()
            ->where('updated_at', '<', $cutoff)
            ->whereIn('status', ['expired', 'closed'])
            ->delete();

        $this->info("Propostas expiradas: {$expiredProposals} | Sessões expiradas: {$expiredSessions} | Sessões removidas: {$deleted}.");

        return self::SUCCESS;
    }
}
