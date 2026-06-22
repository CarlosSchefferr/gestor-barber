<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/**
 * Cria um cenário mínimo de barbearia para os testes de agenda/chat.
 *
 * @return array{owner:\App\Models\User,barber:\App\Models\User,service:\App\Models\Service,config:\App\Models\AgendaConfig}
 */
function makeBarbershop(array $overrides = []): array
{
    $owner = \App\Models\User::factory()->create(['role' => 'owner', 'name' => 'Dono Teste']);

    $barber = \App\Models\User::factory()->create([
        'role' => 'barber',
        'name' => 'Bruno Barbeiro',
        'professional_name' => 'Bruno',
    ]);

    $service = \App\Models\Service::create([
        'name' => $overrides['service_name'] ?? 'Corte de Cabelo',
        'duration' => $overrides['duration'] ?? 30,
        'price' => $overrides['price'] ?? 50,
        'active' => $overrides['service_active'] ?? true,
        'type' => 'service',
    ]);

    \App\Models\ProfessionalService::create([
        'user_id' => $barber->id,
        'service_id' => $service->id,
        'time_minutes' => $overrides['duration'] ?? 30,
        'price' => $overrides['price'] ?? 50,
        'commission_percentage' => 0,
    ]);

    $config = \App\Models\AgendaConfig::create([
        'user_id' => $owner->id,
        'nome_barbearia' => 'Barbearia Teste',
        'public_token' => (string) \Illuminate\Support\Str::uuid(),
        'horario_inicio' => '08:00',
        'horario_fim' => '18:00',
        'intervalo_slots' => 30,
        'dias_atendimento' => ['segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo'],
        'ativa' => $overrides['ativa'] ?? true,
    ]);

    return compact('owner', 'barber', 'service', 'config');
}

/**
 * Próximo início válido (amanhã num horário dado, no fuso oficial).
 */
function nextSlotStart(int $hour = 10, int $minute = 0): \Carbon\CarbonImmutable
{
    $tz = config('chat.scheduling.timezone', 'America/Sao_Paulo');

    return \Carbon\CarbonImmutable::now($tz)->addDay()->setTime($hour, $minute, 0);
}

/**
 * Habilita o chat IA em testes (sem chave real).
 */
function enableChatAi(): void
{
    config([
        'chat.enabled' => true,
        'chat.openai.api_key' => 'sk-test-fake',
        'chat.openai.model' => 'gpt-test',
        'chat.moderation.enabled' => false,
    ]);
}

/**
 * Monta uma resposta fake da Responses API com uma function_call.
 */
function fakeResponsesFunctionCall(string $name, array $arguments, string $callId = 'call_1'): array
{
    return [
        'id' => 'resp_'.\Illuminate\Support\Str::random(6),
        'model' => 'gpt-test',
        'output' => [[
            'type' => 'function_call',
            'call_id' => $callId,
            'name' => $name,
            'arguments' => json_encode($arguments),
        ]],
        'usage' => ['input_tokens' => 50, 'output_tokens' => 10],
    ];
}

/**
 * Monta uma resposta fake da Responses API com texto final.
 */
function fakeResponsesMessage(string $text): array
{
    return [
        'id' => 'resp_'.\Illuminate\Support\Str::random(6),
        'model' => 'gpt-test',
        'output' => [[
            'type' => 'message',
            'content' => [['type' => 'output_text', 'text' => $text]],
        ]],
        'usage' => ['input_tokens' => 60, 'output_tokens' => 20],
    ];
}
