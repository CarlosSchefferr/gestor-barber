<?php

use App\Models\Agendamento;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can list agendamentos', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Agendamento::factory()->count(3)->create();

    $response = $this->get(route('agendamentos.index'));

    $response->assertStatus(200);
    $response->assertSee('Agendamentos');
});
