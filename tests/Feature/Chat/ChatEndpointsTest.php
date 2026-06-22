<?php

use Illuminate\Support\Facades\Http;

it('start retorna ai_enabled=false sem credenciais (fallback)', function () {
    config(['chat.enabled' => true, 'chat.openai.api_key' => null, 'chat.openai.model' => null]);
    ['config' => $config] = makeBarbershop();

    $this->postJson("/t/{$config->public_token}/api/chat/start")
        ->assertOk()
        ->assertJson(['ai_enabled' => false]);
});

it('message retorna 503 quando IA desabilitada', function () {
    config(['chat.enabled' => false]);
    ['config' => $config] = makeBarbershop();

    $this->postJson("/t/{$config->public_token}/api/chat/message", [
        'session_token' => \Illuminate\Support\Str::uuid()->toString(),
        'message' => 'oi',
    ])->assertStatus(503);
});

it('start cria sessão quando IA habilitada', function () {
    enableChatAi();
    ['config' => $config] = makeBarbershop();

    $this->postJson("/t/{$config->public_token}/api/chat/start")
        ->assertOk()
        ->assertJson(['ai_enabled' => true])
        ->assertJsonStructure(['session_token', 'greeting']);
});

it('message com sessão inexistente retorna 410', function () {
    enableChatAi();
    Http::fake();
    ['config' => $config] = makeBarbershop();

    $this->postJson("/t/{$config->public_token}/api/chat/message", [
        'session_token' => \Illuminate\Support\Str::uuid()->toString(),
        'message' => 'oi',
    ])->assertStatus(410);
});

it('honeypot preenchido é rejeitado', function () {
    enableChatAi();
    ['config' => $config] = makeBarbershop();
    $start = $this->postJson("/t/{$config->public_token}/api/chat/start")->json();

    $this->postJson("/t/{$config->public_token}/api/chat/message", [
        'session_token' => $start['session_token'],
        'message' => 'oi',
        'website' => 'http://spam',
    ])->assertStatus(422);
});

it('token inexistente retorna 404', function () {
    $this->postJson('/t/inexistente/api/chat/start')->assertNotFound();
});
