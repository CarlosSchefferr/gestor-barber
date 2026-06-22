<?php

namespace App\Services\Agenda;

use App\Models\AgendaConfig;
use App\Models\Agendamento;
use App\Models\Cliente;
use App\Models\User;
use App\Services\Agenda\Exceptions\SlotUnavailableException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Criação transacional e segura de agendamentos, compartilhada pelo chat com
 * IA e pelo formulário público tradicional.
 *
 * Garante, contra concorrência:
 * 1. Lock pessimista no profissional (serializa criações do mesmo profissional).
 * 2. Revalidação de disponibilidade dentro da transação.
 * 3. Inserção única apenas se ainda disponível.
 * 4. Conflito recuperável via SlotUnavailableException.
 */
class BookingService
{
    public function __construct(private readonly AvailabilityService $availability) {}

    /**
     * @param  array{nome:string,email:string,telefone:string}  $customer
     *
     * @throws SlotUnavailableException
     */
    public function create(
        AgendaConfig $config,
        ResolvedSlot $slot,
        array $customer,
        ?string $observacoes,
        string $origin
    ): Agendamento {
        return DB::transaction(function () use ($config, $slot, $customer, $observacoes, $origin) {
            // Serializa criações concorrentes para o mesmo profissional.
            $professional = User::query()
                ->whereKey($slot->professional->id)
                ->lockForUpdate()
                ->first();

            if (! $professional || ! in_array($professional->role, ['barber', 'owner'], true)) {
                throw new SlotUnavailableException('Profissional indisponível.');
            }

            $startsAt = $slot->startsAt;
            $endsAt = $slot->endsAt;

            if ($this->availability->hasConflict($professional->id, $startsAt, $endsAt)) {
                throw new SlotUnavailableException('Horário recém-ocupado.');
            }

            $cliente = $this->resolveCliente($customer);

            return Agendamento::create([
                'cliente_id' => $cliente->id,
                'barbeiro_id' => $professional->id,
                'user_id' => $config->user_id,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'status' => 'agendado',
                'servico' => $slot->service->name,
                'price' => $slot->price,
                'observacoes' => $observacoes,
                'public_token' => (string) Str::uuid(),
                'origin' => $origin,
            ]);
        }, 3);
    }

    /**
     * Localiza um cliente pelo e-mail ou cria um novo. Mantém a regra atual do
     * projeto: dados de cliente existente não são sobrescritos pelo público.
     *
     * @param  array{nome:string,email:string,telefone:string}  $customer
     */
    private function resolveCliente(array $customer): Cliente
    {
        $cliente = Cliente::query()
            ->where('email', $customer['email'])
            ->first();

        if ($cliente) {
            return $cliente;
        }

        return Cliente::create([
            'nome' => $customer['nome'],
            'email' => $customer['email'],
            'telefone' => $customer['telefone'],
            'active' => true,
        ]);
    }
}
