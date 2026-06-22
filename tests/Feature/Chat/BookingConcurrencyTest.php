<?php

use App\Models\Agendamento;
use App\Models\ChatBookingProposal;
use App\Models\ChatSession;
use App\Services\Agenda\AvailabilityService;
use App\Services\Agenda\BookingService;
use App\Services\Agenda\Exceptions\SlotUnavailableException;
use App\Services\Chat\ChatBookingService;
use Illuminate\Support\Str;

function makeProposal(array $ctx, array $over = []): ChatBookingProposal
{
    $session = ChatSession::create([
        'agenda_config_id' => $ctx['config']->id,
        'session_token' => (string) Str::uuid(),
        'status' => 'active',
        'expires_at' => now()->addHour(),
    ]);

    $start = nextSlotStart(10);

    return ChatBookingProposal::create(array_merge([
        'chat_session_id' => $session->id,
        'agenda_config_id' => $ctx['config']->id,
        'token' => (string) Str::uuid(),
        'service_id' => $ctx['service']->id,
        'professional_id' => $ctx['barber']->id,
        'starts_at' => $start,
        'ends_at' => $start->addMinutes(30),
        'price' => 50,
        'duration_minutes' => 30,
        'customer_name' => 'Maria',
        'customer_email' => 'maria@example.com',
        'customer_phone' => '11988887777',
        'status' => 'pending',
        'expires_at' => now()->addMinutes(10),
    ], $over));
}

it('segunda criação no mesmo horário recebe conflito', function () {
    $ctx = makeBarbershop();
    $booking = app(BookingService::class);
    $availability = app(AvailabilityService::class);
    $start = nextSlotStart(10);

    $slot = $availability->resolveSlot($ctx['config'], $ctx['service'], $ctx['barber'], $start);
    $booking->create($ctx['config'], $slot, ['nome' => 'A', 'email' => 'a@a.com', 'telefone' => '1'], null, 'teste');

    $slot2 = $availability->resolveSlot($ctx['config'], $ctx['service'], $ctx['barber'], $start);
    // resolveSlot já retorna null (ocupado), então não há como criar de novo.
    expect($slot2)->toBeNull();

    // Mesmo forçando o slot antigo, o create revalida e lança conflito.
    expect(fn () => $booking->create($ctx['config'], $slot, ['nome' => 'B', 'email' => 'b@b.com', 'telefone' => '2'], null, 'teste'))
        ->toThrow(SlotUnavailableException::class);

    expect(Agendamento::count())->toBe(1);
});

it('confirmação de proposta é idempotente', function () {
    $ctx = makeBarbershop();
    $proposal = makeProposal($ctx);
    $service = app(ChatBookingService::class);

    $key = (string) Str::uuid();
    $first = $service->confirm($proposal, $key);
    $second = $service->confirm($proposal->fresh(), $key);

    expect($first->id)->toBe($second->id)
        ->and(Agendamento::count())->toBe(1);
});

it('proposta expirada é rejeitada na confirmação', function () {
    $ctx = makeBarbershop();
    $proposal = makeProposal($ctx, ['expires_at' => now()->subMinute()]);
    $service = app(ChatBookingService::class);

    expect(fn () => $service->confirm($proposal, (string) Str::uuid()))
        ->toThrow(\App\Services\Chat\Exceptions\ChatBookingException::class);

    expect(Agendamento::count())->toBe(0);
});

it('proposta sem dados pessoais não confirma', function () {
    $ctx = makeBarbershop();
    $proposal = makeProposal($ctx, ['customer_name' => null, 'customer_email' => null, 'customer_phone' => null]);
    $service = app(ChatBookingService::class);

    expect(fn () => $service->confirm($proposal, (string) Str::uuid()))
        ->toThrow(\App\Services\Chat\Exceptions\ChatBookingException::class);
});
