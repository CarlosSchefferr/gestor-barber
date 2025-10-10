<?php

use App\Models\Cliente;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('allows authenticated user to create a cliente', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $data = [
        'nome' => 'João Silva',
        'email' => 'joao@example.com',
        'telefone' => '123456789',
    ];

    $res = $this->post(route('clientes.store'), $data);
    $res->assertRedirect(route('clientes.index'));

    $this->assertDatabaseHas('clientes', ['email' => 'joao@example.com']);
});
