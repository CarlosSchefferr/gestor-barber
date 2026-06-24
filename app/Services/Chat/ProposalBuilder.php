<?php

namespace App\Services\Chat;

use App\Models\AgendaConfig;
use App\Models\ChatBookingProposal;
use App\Models\ChatSession;
use App\Models\Service;
use App\Models\User;
use App\Services\Agenda\AvailabilityService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

/**
 * Cria propostas de agendamento (não confirmadas) a partir de uma seleção
 * já validada. Compartilhado pela tool prepare_booking (chat IA) e pelo
 * montador de agendamento do site, garantindo regras idênticas.
 */
class ProposalBuilder
{
    public function __construct(private readonly AvailabilityService $availability) {}

    /**
     * @return array{proposal:ChatBookingProposal,summary:array<string,mixed>}|null null quando o horário está indisponível.
     */
    public function build(AgendaConfig $config, ChatSession $session, Service $service, ?User $professional, CarbonImmutable $start): ?array
    {
        $slot = $this->availability->resolveSlot($config, $service, $professional, $start);
        if (! $slot) {
            return null;
        }

        // Invalida propostas pendentes anteriores desta sessão.
        ChatBookingProposal::query()
            ->where('chat_session_id', $session->id)
            ->where('status', 'pending')
            ->update(['status' => 'cancelled']);

        $ttl = (int) config('chat.limits.proposal_ttl_minutes', 10);

        $proposal = ChatBookingProposal::create([
            'chat_session_id' => $session->id,
            'agenda_config_id' => $config->id,
            'token' => (string) Str::uuid(),
            'service_id' => $slot->service->id,
            'professional_id' => $slot->professional->id,
            'starts_at' => $slot->startsAt,
            'ends_at' => $slot->endsAt,
            'price' => $slot->price,
            'duration_minutes' => $slot->durationMinutes,
            'status' => 'pending',
            'expires_at' => now()->addMinutes($ttl),
        ]);

        $dias = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];

        $summary = [
            'servico' => $slot->service->name,
            'profissional' => $slot->professional->professional_name ?: $slot->professional->name,
            'data' => $slot->startsAt->format('Y-m-d'),
            'data_label' => $dias[$slot->startsAt->dayOfWeek].', '.$slot->startsAt->format('d/m/Y'),
            'inicio' => $slot->startsAt->format('H:i'),
            'fim' => $slot->endsAt->format('H:i'),
            'duracao_label' => $slot->durationMinutes.' min',
            'preco_label' => $slot->price !== null ? 'R$ '.number_format($slot->price, 2, ',', '.') : null,
        ];

        return ['proposal' => $proposal, 'summary' => $summary];
    }
}
