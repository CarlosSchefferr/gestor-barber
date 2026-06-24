<?php

use App\Models\AgendaConfig;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('lista todos os produtos ativos no config público (não só sellable)', function () {
    ['config' => $config] = makeBarbershop();
    Product::create(['name' => 'Pomada', 'price' => 30, 'active' => true, 'usage_type' => 'barbershop', 'registration_type' => 'product']);
    Product::create(['name' => 'Inativo', 'price' => 10, 'active' => false, 'usage_type' => 'sale', 'registration_type' => 'product']);

    $res = $this->getJson("/t/{$config->public_token}/api/config")->json();

    $nomes = collect($res['produtos'])->pluck('nome');
    expect($nomes)->toContain('Pomada')
        ->and($nomes)->not->toContain('Inativo');
});

it('resolve a página pública por slug e por token (compatível)', function () {
    ['config' => $config] = makeBarbershop();
    $config->update(['slug' => 'duarte-barbearia']);

    $this->get('/t/duarte-barbearia')->assertOk();
    $this->get("/t/{$config->public_token}")->assertOk();
    $this->get('/t/nao-existe')->assertNotFound();
});

it('config público inclui logo quando definido', function () {
    Storage::fake('public');
    ['config' => $config] = makeBarbershop();
    $config->update(['logo' => 'agenda-logos/x.png']);

    $res = $this->getJson("/t/{$config->public_token}/api/config")->json();
    expect($res['logo'])->toContain('agenda-logos/x.png');
});

it('check-slug informa disponibilidade', function () {
    ['owner' => $owner, 'config' => $config] = makeBarbershop();
    $config->update(['slug' => 'minha-barbearia']);
    // Outra barbearia ocupando um slug.
    $outroOwner = \App\Models\User::factory()->create(['role' => 'owner']);
    AgendaConfig::create(['user_id' => $outroOwner->id, 'nome_barbearia' => 'Outra', 'slug' => 'ocupado', 'public_token' => (string) \Illuminate\Support\Str::uuid(), 'horario_inicio' => '08:00', 'horario_fim' => '18:00', 'intervalo_slots' => 30, 'dias_atendimento' => ['segunda'], 'ativa' => true]);

    $this->actingAs($owner)->postJson(route('agenda.config.check-slug'), ['slug' => 'Livre Nome'])
        ->assertOk()->assertJson(['slug' => 'livre-nome', 'available' => true]);

    $this->actingAs($owner)->postJson(route('agenda.config.check-slug'), ['slug' => 'ocupado'])
        ->assertOk()->assertJson(['available' => false]);
});

it('update salva slug válido e rejeita duplicado/ inválido', function () {
    ['owner' => $owner, 'config' => $config] = makeBarbershop();
    $config->update(['slug' => 'principal']);
    $outroOwner = \App\Models\User::factory()->create(['role' => 'owner']);
    AgendaConfig::create(['user_id' => $outroOwner->id, 'nome_barbearia' => 'Outra', 'slug' => 'tomado', 'public_token' => (string) \Illuminate\Support\Str::uuid(), 'horario_inicio' => '08:00', 'horario_fim' => '18:00', 'intervalo_slots' => 30, 'dias_atendimento' => ['segunda'], 'ativa' => true]);

    $base = [
        'nome_barbearia' => 'Minha Barbearia',
        'horario_inicio' => '08:00', 'horario_fim' => '18:00', 'intervalo_slots' => 30,
        'dias_atendimento' => ['segunda', 'terca'], 'ativa' => 1,
    ];

    // Slug novo válido.
    $this->actingAs($owner)->putJson(route('agenda.config.update'), array_merge($base, ['slug' => 'novo-nome']))
        ->assertOk()->assertJson(['success' => true, 'slug' => 'novo-nome']);

    // Slug duplicado.
    $this->actingAs($owner)->putJson(route('agenda.config.update'), array_merge($base, ['slug' => 'tomado']))
        ->assertStatus(422);

    // Slug inválido (maiúsculas/espaço).
    $this->actingAs($owner)->putJson(route('agenda.config.update'), array_merge($base, ['slug' => 'Nome Invalido']))
        ->assertStatus(422);
});

it('update faz upload e remoção de logo', function () {
    Storage::fake('public');
    ['owner' => $owner] = makeBarbershop();

    $base = [
        'nome_barbearia' => 'Logo Teste', 'slug' => 'logo-teste',
        'horario_inicio' => '08:00', 'horario_fim' => '18:00', 'intervalo_slots' => 30,
        'dias_atendimento' => ['segunda'], 'ativa' => 1,
    ];

    $this->actingAs($owner)->put(route('agenda.config.update'), array_merge($base, [
        'logo' => UploadedFile::fake()->image('logo.png'),
    ]), ['Accept' => 'application/json'])->assertOk();

    $config = AgendaConfig::where('user_id', $owner->id)->first();
    expect($config->logo)->not->toBeNull();
    Storage::disk('public')->assertExists($config->logo);

    // Remoção.
    $this->actingAs($owner)->put(route('agenda.config.update'), array_merge($base, ['remover_logo' => 1]), ['Accept' => 'application/json'])->assertOk();
    expect(AgendaConfig::where('user_id', $owner->id)->first()->logo)->toBeNull();
});
