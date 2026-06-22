<?php

use App\Models\Agendamento;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

function startAndPropose(array $ctx): array
{
    $token = $ctx['config']->public_token;
    $start = nextSlotStart(10);

    $session = test()->postJson("/t/{$token}/api/chat/start")->json('session_token');

    Http::fake(['api.openai.com/*' => Http::sequence()
        ->push(fakeResponsesFunctionCall('prepare_booking', [
            'service_id' => $ctx['service']->id,
            'data' => $start->format('Y-m-d'),
            'hora' => '10:00',
            'professional_id' => $ctx['barber']->id,
        ]))
        ->push(fakeResponsesMessage('Confira o resumo e confirme.')),
    ]);

    $msg = test()->postJson("/t/{$token}/api/chat/message", [
        'session_token' => $session,
        'message' => 'quero amanhã às 10h',
    ])->json();

    return ['session' => $session, 'proposal' => $msg['ui']['proposal']['token'], 'token' => $token];
}

beforeEach(function () {
    enableChatAi();
    $this->ctx = makeBarbershop();
});

it('fluxo completo: proposta, dados e confirmação cria agendamento', function () {
    $f = startAndPropose($this->ctx);

    test()->postJson("/t/{$f['token']}/api/chat/proposal/customer", [
        'session_token' => $f['session'],
        'proposal_token' => $f['proposal'],
        'nome' => 'Carlos Cliente',
        'email' => 'carlos@example.com',
        'telefone' => '11999990000',
    ])->assertOk();

    $res = test()->postJson("/t/{$f['token']}/api/chat/confirm", [
        'session_token' => $f['session'],
        'proposal_token' => $f['proposal'],
        'idempotency_key' => (string) Str::uuid(),
    ]);

    $res->assertOk()->assertJson(['ok' => true]);
    expect(Agendamento::count())->toBe(1)
        ->and(Agendamento::first()->origin)->toBe('chat_ia');
});

it('confirmação duplicada (mesma chave) é idempotente', function () {
    $f = startAndPropose($this->ctx);
    test()->postJson("/t/{$f['token']}/api/chat/proposal/customer", [
        'session_token' => $f['session'], 'proposal_token' => $f['proposal'],
        'nome' => 'C', 'email' => 'c@example.com', 'telefone' => '119',
    ])->assertOk();

    $key = (string) Str::uuid();
    $a = test()->postJson("/t/{$f['token']}/api/chat/confirm", ['session_token' => $f['session'], 'proposal_token' => $f['proposal'], 'idempotency_key' => $key])->json();
    $b = test()->postJson("/t/{$f['token']}/api/chat/confirm", ['session_token' => $f['session'], 'proposal_token' => $f['proposal'], 'idempotency_key' => $key])->json();

    expect($a['agendamento']['id'])->toBe($b['agendamento']['id'])
        ->and(Agendamento::count())->toBe(1);
});

it('token de proposta manipulado é rejeitado', function () {
    $f = startAndPropose($this->ctx);

    test()->postJson("/t/{$f['token']}/api/chat/confirm", [
        'session_token' => $f['session'],
        'proposal_token' => (string) Str::uuid(), // token inventado
        'idempotency_key' => (string) Str::uuid(),
    ])->assertStatus(422);

    expect(Agendamento::count())->toBe(0);
});

it('proposta não pode ser usada por outra sessão', function () {
    $f = startAndPropose($this->ctx);
    // Nova sessão na mesma barbearia.
    $outra = test()->postJson("/t/{$f['token']}/api/chat/start")->json('session_token');

    test()->postJson("/t/{$f['token']}/api/chat/proposal/customer", [
        'session_token' => $outra,
        'proposal_token' => $f['proposal'],
        'nome' => 'Intruso', 'email' => 'i@example.com', 'telefone' => '11',
    ])->assertStatus(422);
});

it('não cria agendamento sem dados pessoais', function () {
    $f = startAndPropose($this->ctx);

    // Confirma sem ter enviado os dados pessoais.
    test()->postJson("/t/{$f['token']}/api/chat/confirm", [
        'session_token' => $f['session'],
        'proposal_token' => $f['proposal'],
        'idempotency_key' => (string) Str::uuid(),
    ])->assertStatus(422);

    expect(Agendamento::count())->toBe(0);
});
