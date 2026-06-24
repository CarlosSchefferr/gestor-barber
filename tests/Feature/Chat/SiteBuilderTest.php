<?php

use App\Models\Agendamento;
use App\Models\ChatBookingProposal;
use App\Models\Cliente;

it('config inclui serviços por profissional', function () {
    ['config' => $config, 'barber' => $barber] = makeBarbershop();

    $res = $this->getJson("/t/{$config->public_token}/api/config")->json();
    $prof = collect($res['barbeiros'])->firstWhere('id', $barber->id);

    expect($prof)->not->toBeNull()
        ->and($prof['servicos'])->toBeArray()
        ->and(collect($prof['servicos'])->pluck('nome'))->toContain('Corte de Cabelo');
});

it('endpoint de profissionais retorna aptos ao serviço', function () {
    ['config' => $config, 'service' => $service, 'barber' => $barber] = makeBarbershop();

    $res = $this->getJson("/t/{$config->public_token}/api/professionals?service_id={$service->id}")->json();

    expect(collect($res['profissionais'])->pluck('id'))->toContain($barber->id);
});

it('endpoint de horários exclui ocupados', function () {
    ['config' => $config, 'service' => $service, 'barber' => $barber] = makeBarbershop();
    $start = nextSlotStart(10);
    $cliente = Cliente::create(['nome' => 'X', 'active' => true]);
    Agendamento::create(['cliente_id' => $cliente->id, 'barbeiro_id' => $barber->id, 'starts_at' => $start, 'ends_at' => $start->addMinutes(30), 'status' => 'agendado']);

    $res = $this->getJson("/t/{$config->public_token}/api/available-times?service_id={$service->id}&data={$start->format('Y-m-d')}&professional_id={$barber->id}")->json();
    $horarios = collect($res['horarios'])->pluck('time');

    expect($horarios)->not->toContain('10:00')
        ->and($horarios)->toContain('10:30');
});

it('montador do site prepara proposta e confirma (handoff robusto)', function () {
    enableChatAi();
    ['config' => $config, 'service' => $service, 'barber' => $barber] = makeBarbershop();
    $start = nextSlotStart(10);

    $session = $this->postJson("/t/{$config->public_token}/api/chat/start")->json('session_token');

    $prep = $this->postJson("/t/{$config->public_token}/api/chat/prepare-from-selection", [
        'session_token' => $session,
        'service_id' => $service->id,
        'professional_id' => $barber->id,
        'data' => $start->format('Y-m-d'),
        'hora' => '10:00',
    ]);
    $prep->assertOk()->assertJson(['ok' => true]);
    $token = $prep->json('proposal.token');
    expect($token)->not->toBeEmpty()
        ->and(ChatBookingProposal::where('status', 'pending')->count())->toBe(1);

    // Dados pessoais + confirmação.
    $this->postJson("/t/{$config->public_token}/api/chat/proposal/customer", [
        'session_token' => $session, 'proposal_token' => $token,
        'nome' => 'Site Cliente', 'email' => 'site@example.com', 'telefone' => '11999998888',
    ])->assertOk();

    $this->postJson("/t/{$config->public_token}/api/chat/confirm", [
        'session_token' => $session, 'proposal_token' => $token,
        'idempotency_key' => (string) \Illuminate\Support\Str::uuid(),
    ])->assertOk();

    expect(Agendamento::count())->toBe(1)
        ->and(Agendamento::first()->origin)->toBe('chat_ia');
});

it('montador rejeita horário indisponível', function () {
    enableChatAi();
    ['config' => $config, 'service' => $service, 'barber' => $barber] = makeBarbershop();
    $start = nextSlotStart(10);
    $cliente = Cliente::create(['nome' => 'X', 'active' => true]);
    Agendamento::create(['cliente_id' => $cliente->id, 'barbeiro_id' => $barber->id, 'starts_at' => $start, 'ends_at' => $start->addMinutes(30), 'status' => 'agendado']);

    $session = $this->postJson("/t/{$config->public_token}/api/chat/start")->json('session_token');

    $this->postJson("/t/{$config->public_token}/api/chat/prepare-from-selection", [
        'session_token' => $session,
        'service_id' => $service->id,
        'professional_id' => $barber->id,
        'data' => $start->format('Y-m-d'),
        'hora' => '10:00',
    ])->assertStatus(409);
});
