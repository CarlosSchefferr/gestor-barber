<?php

namespace App\Services\Chat;

use App\Models\Agendamento;
use App\Models\ChatBookingProposal;
use App\Models\Service;
use App\Models\User;
use App\Services\Agenda\AvailabilityService;
use App\Services\Agenda\BookingService;
use App\Services\Agenda\Exceptions\SlotUnavailableException;
use App\Services\Agenda\ResolvedSlot;
use App\Services\Chat\Exceptions\ChatBookingException;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Confirmação segura, transacional e idempotente de uma proposta de
 * agendamento gerada pelo chat. A criação definitiva só acontece aqui, após
 * ação explícita do frontend (token + idempotency key).
 */
class ChatBookingService
{
    public function __construct(
        private readonly AvailabilityService $availability,
        private readonly BookingService $booking,
    ) {}

    /**
     * Anexa os dados pessoais coletados em campo seguro à proposta.
     *
     * @param  array{nome:string,email:string,telefone:string,observacoes?:string|null}  $customer
     */
    public function attachCustomer(ChatBookingProposal $proposal, array $customer): ChatBookingProposal
    {
        if (! $proposal->isPending()) {
            throw new ChatBookingException('Proposta expirada ou já utilizada.', 'proposal_invalid');
        }

        $proposal->update([
            'customer_name' => $customer['nome'],
            'customer_email' => $customer['email'],
            'customer_phone' => $customer['telefone'],
            'observacoes' => $customer['observacoes'] ?? $proposal->observacoes,
        ]);

        return $proposal->refresh();
    }

    /**
     * Confirma a proposta e cria o agendamento de forma idempotente.
     *
     * @throws ChatBookingException|SlotUnavailableException
     */
    public function confirm(ChatBookingProposal $proposal, string $idempotencyKey): Agendamento
    {
        return DB::transaction(function () use ($proposal, $idempotencyKey) {
            /** @var ChatBookingProposal|null $p */
            $p = ChatBookingProposal::query()->whereKey($proposal->id)->lockForUpdate()->first();

            if (! $p) {
                throw new ChatBookingException('Proposta inválida.', 'proposal_invalid');
            }

            // Idempotência: já confirmada -> devolve o mesmo agendamento.
            if ($p->status === 'confirmed' && $p->agendamento_id) {
                $existing = Agendamento::find($p->agendamento_id);
                if ($existing) {
                    return $existing;
                }
            }

            if ($p->status !== 'pending') {
                throw new ChatBookingException('Proposta expirada ou já utilizada.', 'proposal_invalid');
            }

            if ($p->expires_at->isPast()) {
                $p->update(['status' => 'expired']);
                throw new ChatBookingException('Proposta expirada. Vamos escolher um novo horário?', 'proposal_expired');
            }

            if (! $p->hasCustomerData()) {
                throw new ChatBookingException('Faltam os dados pessoais para confirmar.', 'missing_customer');
            }

            $config = $p->agendaConfig()->first();
            if (! $config || ! $config->ativa) {
                throw new ChatBookingException('Agenda indisponível no momento.', 'agenda_inactive');
            }

            $service = Service::query()->where('active', true)->find($p->service_id);
            if (! $service) {
                throw new SlotUnavailableException('Esse serviço não está mais disponível.');
            }

            $professional = User::query()->whereIn('role', ['barber', 'owner'])->find($p->professional_id);
            if (! $professional) {
                throw new SlotUnavailableException('Esse profissional não está mais disponível.');
            }

            // Revalidação integral da disponibilidade (regras + ocupação).
            // Reinterpreta o horário gravado como wall-clock no fuso oficial.
            $start = CarbonImmutable::parse($p->starts_at->format('Y-m-d H:i:s'), $this->availability->timezone());
            $slot = $this->availability->resolveSlot($config, $service, $professional, $start);
            if (! $slot instanceof ResolvedSlot) {
                throw new SlotUnavailableException('Esse horário acabou de ficar indisponível.');
            }

            $agendamento = $this->booking->create(
                $config,
                $slot,
                [
                    'nome' => (string) $p->customer_name,
                    'email' => (string) $p->customer_email,
                    'telefone' => (string) $p->customer_phone,
                ],
                $p->observacoes,
                (string) config('chat.scheduling.chat_origin', 'chat_ia'),
            );

            $p->update([
                'status' => 'confirmed',
                'agendamento_id' => $agendamento->id,
                'confirmed_at' => now(),
                'idempotency_key' => $idempotencyKey,
            ]);

            return $agendamento;
        }, 3);
    }
}
