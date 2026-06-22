<?php

use App\Models\ChatBookingProposal;
use App\Models\ChatSession;
use App\Services\Agenda\AvailabilityService;
use App\Services\Chat\Tools\GetAvailableTimesTool;
use App\Services\Chat\Tools\ListProfessionalsTool;
use App\Services\Chat\Tools\ListServicesTool;
use App\Services\Chat\Tools\PrepareBookingTool;
use App\Services\Chat\Tools\ToolContext;
use Illuminate\Support\Str;

function toolContext(array $ctx): ToolContext
{
    $session = ChatSession::create([
        'agenda_config_id' => $ctx['config']->id,
        'session_token' => (string) Str::uuid(),
        'status' => 'active',
        'expires_at' => now()->addHour(),
    ]);

    return new ToolContext($ctx['config'], $session, app(AvailabilityService::class));
}

it('list_services retorna apenas serviços ativos', function () {
    $ctx = makeBarbershop();
    \App\Models\Service::create(['name' => 'Inativo', 'duration' => 30, 'price' => 10, 'active' => false, 'type' => 'service']);

    $result = (new ListServicesTool)->handle(['busca' => null], toolContext($ctx));

    $nomes = collect($result->output['servicos'])->pluck('nome');
    expect($nomes)->toContain('Corte de Cabelo')
        ->and($nomes)->not->toContain('Inativo');
});

it('list_professionals rejeita service_id inválido', function () {
    $ctx = makeBarbershop();

    $result = (new ListProfessionalsTool)->handle(['service_id' => 999999], toolContext($ctx));

    expect($result->status)->toBe('invalid');
});

it('get_available_times rejeita data malformada', function () {
    $ctx = makeBarbershop();

    $result = (new GetAvailableTimesTool)->handle([
        'service_id' => $ctx['service']->id,
        'data' => '31/02/2026',
        'professional_id' => null,
    ], toolContext($ctx));

    expect($result->status)->toBe('invalid');
});

it('prepare_booking cria proposta para horário válido sem expor token ao modelo', function () {
    $ctx = makeBarbershop();
    $start = nextSlotStart(10);

    $result = (new PrepareBookingTool)->handle([
        'service_id' => $ctx['service']->id,
        'data' => $start->format('Y-m-d'),
        'hora' => '10:00',
        'professional_id' => $ctx['barber']->id,
    ], toolContext($ctx));

    expect($result->status)->toBe('ok')
        ->and($result->output)->not->toHaveKey('token')         // token nunca vai ao modelo
        ->and($result->ui['proposal']['token'])->not->toBeEmpty() // token vai à interface
        ->and(ChatBookingProposal::where('status', 'pending')->count())->toBe(1);
});

it('prepare_booking recusa horário indisponível', function () {
    $ctx = makeBarbershop();
    $start = nextSlotStart(10);
    // Ocupa o horário.
    $cliente = \App\Models\Cliente::create(['nome' => 'X', 'active' => true]);
    \App\Models\Agendamento::create([
        'cliente_id' => $cliente->id, 'barbeiro_id' => $ctx['barber']->id,
        'starts_at' => $start, 'ends_at' => $start->addMinutes(30), 'status' => 'agendado',
    ]);

    $result = (new PrepareBookingTool)->handle([
        'service_id' => $ctx['service']->id,
        'data' => $start->format('Y-m-d'),
        'hora' => '10:00',
        'professional_id' => $ctx['barber']->id,
    ], toolContext($ctx));

    expect($result->status)->toBe('invalid')
        ->and(ChatBookingProposal::count())->toBe(0);
});
