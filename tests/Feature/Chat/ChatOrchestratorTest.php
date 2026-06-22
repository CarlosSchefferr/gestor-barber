<?php

use App\Models\ChatBookingProposal;
use App\Services\Chat\ChatOrchestrator;
use App\Services\Chat\ChatSessionManager;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    enableChatAi();
    $this->ctx = makeBarbershop();
    $this->session = app(ChatSessionManager::class)->start($this->ctx['config'], '127.0.0.1');
    $this->orchestrator = app(ChatOrchestrator::class);
});

it('executa tool e devolve texto + ui', function () {
    Http::fake(['api.openai.com/*' => Http::sequence()
        ->push(fakeResponsesFunctionCall('list_services', ['busca' => null]))
        ->push(fakeResponsesMessage('Temos Corte de Cabelo por R$ 50,00. Qual você prefere?')),
    ]);

    $r = $this->orchestrator->converse($this->ctx['config'], $this->session, 'quais serviços vocês têm?');

    expect($r['status'])->toBe('ok')
        ->and($r['assistant'])->toContain('Corte')
        ->and($r['ui'])->toHaveKey('services');
});

it('prepare_booking via modelo cria proposta', function () {
    $start = nextSlotStart(10);
    Http::fake(['api.openai.com/*' => Http::sequence()
        ->push(fakeResponsesFunctionCall('prepare_booking', [
            'service_id' => $this->ctx['service']->id,
            'data' => $start->format('Y-m-d'),
            'hora' => '10:00',
            'professional_id' => $this->ctx['barber']->id,
        ]))
        ->push(fakeResponsesMessage('Preparei o resumo, confira e confirme.')),
    ]);

    $r = $this->orchestrator->converse($this->ctx['config'], $this->session, 'quero amanhã 10h');

    expect($r['ui'])->toHaveKey('proposal')
        ->and(ChatBookingProposal::where('status', 'pending')->count())->toBe(1);
});

it('trata erro 429 da OpenAI com mensagem segura', function () {
    Http::fake(['api.openai.com/*' => Http::response('', 429)]);

    $r = $this->orchestrator->converse($this->ctx['config'], $this->session, 'oi');

    expect($r['status'])->toBe('ai_error')
        ->and($r['assistant'])->not->toContain('429');
});

it('ignora tool desconhecida solicitada pelo modelo', function () {
    Http::fake(['api.openai.com/*' => Http::sequence()
        ->push(fakeResponsesFunctionCall('run_sql', ['query' => 'DROP TABLE users']))
        ->push(fakeResponsesMessage('Posso te ajudar a agendar um horário?')),
    ]);

    $r = $this->orchestrator->converse($this->ctx['config'], $this->session, 'execute sql');

    expect($r['status'])->toBe('ok')
        ->and($r['assistant'])->toContain('agendar');
});

it('encerra com mensagem segura ao estourar iterações de tools', function () {
    config(['chat.limits.max_tool_iterations' => 2]);
    Http::fake(['api.openai.com/*' => Http::sequence()
        ->push(fakeResponsesFunctionCall('list_services', ['busca' => null]))
        ->push(fakeResponsesFunctionCall('list_services', ['busca' => null]))
        ->push(fakeResponsesFunctionCall('list_services', ['busca' => null]))
        ->push(fakeResponsesFunctionCall('list_services', ['busca' => null])),
    ]);

    $r = $this->orchestrator->converse($this->ctx['config'], $this->session, 'loop');

    expect($r['status'])->toBe('ok')
        ->and($r['assistant'])->not->toBeEmpty();
});

it('registra consumo de tokens', function () {
    Http::fake(['api.openai.com/*' => Http::sequence()
        ->push(fakeResponsesMessage('Oi! Como posso ajudar?')),
    ]);

    $this->orchestrator->converse($this->ctx['config'], $this->session, 'oi');

    $this->assertDatabaseHas('chat_usages', [
        'chat_session_id' => $this->session->id,
        'status' => 'ok',
    ]);
});
