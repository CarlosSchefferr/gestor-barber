<?php

use App\Models\Agendamento;

function submitPayload(array $over = []): array
{
    $start = nextSlotStart(10);

    return array_merge([
        'cliente_nome' => 'João Cliente',
        'cliente_email' => 'joao@example.com',
        'cliente_telefone' => '11999990000',
        'data_agendamento' => $start->format('Y-m-d'),
        'hora_agendamento' => '10:00',
        'observacoes' => null,
    ], $over);
}

it('cria agendamento pelo fluxo tradicional usando duração real e origem', function () {
    ['config' => $config, 'service' => $service, 'barber' => $barber] = makeBarbershop(['duration' => 45]);

    $res = $this->postJson("/t/{$config->public_token}/api/submit", submitPayload([
        'barbeiro_id' => $barber->id,
        'service_id' => $service->id,
    ]));

    $res->assertOk()->assertJson(['success' => true]);

    $ag = Agendamento::first();
    expect($ag)->not->toBeNull()
        ->and($ag->origin)->toBe('publico_tradicional')
        ->and((int) \Carbon\Carbon::parse($ag->starts_at)->diffInMinutes(\Carbon\Carbon::parse($ag->ends_at)))->toBe(45);
});

it('rejeita horário indisponível (ocupado) com 409', function () {
    ['config' => $config, 'service' => $service, 'barber' => $barber] = makeBarbershop();

    // Primeiro agendamento ocupa 10:00.
    $this->postJson("/t/{$config->public_token}/api/submit", submitPayload([
        'barbeiro_id' => $barber->id,
        'service_id' => $service->id,
    ]))->assertOk();

    // Segundo no mesmo horário deve ser recusado.
    $this->postJson("/t/{$config->public_token}/api/submit", submitPayload([
        'barbeiro_id' => $barber->id,
        'service_id' => $service->id,
        'cliente_email' => 'outro@example.com',
    ]))->assertStatus(409);

    expect(Agendamento::count())->toBe(1);
});

it('rejeita serviço inválido', function () {
    ['config' => $config, 'barber' => $barber] = makeBarbershop();

    $this->postJson("/t/{$config->public_token}/api/submit", submitPayload([
        'barbeiro_id' => $barber->id,
        'service_id' => 99999,
    ]))->assertStatus(422);
});

it('rejeita profissional fora do escopo', function () {
    ['config' => $config, 'service' => $service] = makeBarbershop();
    $cliente = \App\Models\User::factory()->create(['role' => 'owner']); // não barbeiro válido? owner é válido
    // Usa um id inexistente como profissional.
    $this->postJson("/t/{$config->public_token}/api/submit", submitPayload([
        'barbeiro_id' => 999999,
        'service_id' => $service->id,
    ]))->assertStatus(422);
});
