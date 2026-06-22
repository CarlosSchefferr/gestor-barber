<?php

use App\Models\Agendamento;
use App\Models\Cliente;
use App\Models\User;
use App\Services\Agenda\AvailabilityService;

beforeEach(function () {
    $this->availability = app(AvailabilityService::class);
});

function bookAt(int $barberId, \Carbon\CarbonImmutable $start, int $minutes = 30, string $status = 'agendado'): void
{
    $cliente = Cliente::create(['nome' => 'X', 'active' => true]);
    Agendamento::create([
        'cliente_id' => $cliente->id,
        'barbeiro_id' => $barberId,
        'starts_at' => $start,
        'ends_at' => $start->addMinutes($minutes),
        'status' => $status,
    ]);
}

it('lista horário livre para um serviço', function () {
    ['config' => $config, 'service' => $service, 'barber' => $barber] = makeBarbershop();
    $date = nextSlotStart(10)->startOfDay();

    $times = $this->availability->availableTimes($config, $service, $barber, $date);

    expect(collect($times)->pluck('time'))->toContain('10:00');
});

it('remove horário ocupado', function () {
    ['config' => $config, 'service' => $service, 'barber' => $barber] = makeBarbershop();
    $start = nextSlotStart(10);
    bookAt($barber->id, $start, 30);

    $times = collect($this->availability->availableTimes($config, $service, $barber, $start->startOfDay()))->pluck('time');

    expect($times)->not->toContain('10:00');
});

it('detecta sobreposição parcial no início', function () {
    ['barber' => $barber] = makeBarbershop();
    // Existente 09:45–10:15. Candidato 10:00–10:30 deve conflitar.
    bookAt($barber->id, nextSlotStart(9, 45), 30);

    $conflict = $this->availability->hasConflict($barber->id, nextSlotStart(10), nextSlotStart(10, 30));

    expect($conflict)->toBeTrue();
});

it('detecta sobreposição parcial no final', function () {
    ['barber' => $barber] = makeBarbershop();
    // Existente 10:15–10:45. Candidato 10:00–10:30 deve conflitar.
    bookAt($barber->id, nextSlotStart(10, 15), 30);

    $conflict = $this->availability->hasConflict($barber->id, nextSlotStart(10), nextSlotStart(10, 30));

    expect($conflict)->toBeTrue();
});

it('serviço mais longo que o slot bloqueia o período inteiro', function () {
    ['config' => $config, 'service' => $service, 'barber' => $barber] = makeBarbershop(['duration' => 45]);
    // Atendimento de 45 min às 10:00 bloqueia 10:00 e 10:30 (que terminaria 11:15 sobre 10:00–10:45).
    bookAt($barber->id, nextSlotStart(10), 45);

    $times = collect($this->availability->availableTimes($config, $service, $barber, nextSlotStart(10)->startOfDay()))->pluck('time');

    expect($times)->not->toContain('10:00')
        ->and($times)->not->toContain('10:30');
});

it('não oferece datas em dia sem atendimento', function () {
    ['config' => $config, 'service' => $service, 'barber' => $barber] = makeBarbershop();
    // Apenas segunda-feira atendida.
    $config->update(['dias_atendimento' => ['segunda']]);

    $dates = $this->availability->availableDates($config, $service, $barber, 14);

    foreach ($dates as $d) {
        expect(\Carbon\CarbonImmutable::parse($d)->dayOfWeek)->toBe(1);
    }
});

it('exclui horários no passado / abaixo da antecedência mínima', function () {
    ['config' => $config, 'service' => $service, 'barber' => $barber] = makeBarbershop();
    $today = \Carbon\CarbonImmutable::now(config('chat.scheduling.timezone'))->startOfDay();

    $times = collect($this->availability->availableTimes($config, $service, $barber, $today))->pluck('time');
    $now = \Carbon\CarbonImmutable::now(config('chat.scheduling.timezone'));

    expect($times->count())->toBeGreaterThanOrEqual(0);
    foreach ($times as $t) {
        [$h, $m] = explode(':', $t);
        $slot = $today->setTime((int) $h, (int) $m);
        expect($slot->greaterThan($now))->toBeTrue();
    }
});

it('rejeita profissional não apto ao serviço', function () {
    ['config' => $config, 'service' => $service] = makeBarbershop();
    // Outro barbeiro sem ProfessionalService para este serviço.
    $outro = User::factory()->create(['role' => 'barber', 'name' => 'Sem Aptidão']);

    $apt = $this->availability->professionalsForService($config, $service);

    expect($apt->pluck('id'))->not->toContain($outro->id);
});

it('agenda inativa não retorna datas', function () {
    ['config' => $config, 'service' => $service, 'barber' => $barber] = makeBarbershop(['ativa' => false]);

    expect($this->availability->availableDates($config, $service, $barber, 14))->toBeEmpty();
});

it('cancelado libera o horário', function () {
    ['config' => $config, 'service' => $service, 'barber' => $barber] = makeBarbershop();
    $start = nextSlotStart(10);
    bookAt($barber->id, $start, 30, 'cancelado');

    $times = collect($this->availability->availableTimes($config, $service, $barber, $start->startOfDay()))->pluck('time');

    expect($times)->toContain('10:00');
});
