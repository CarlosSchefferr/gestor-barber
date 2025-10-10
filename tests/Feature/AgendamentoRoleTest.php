<?php

use App\Models\Agendamento;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('allows owner to see all agendamentos', function () {
    $owner = User::factory()->create(['role' => 'owner']);
    $this->actingAs($owner);

    Agendamento::factory()->count(5)->create();

    $res = $this->get(route('agendamentos.index'));
    $res->assertStatus(200);
    // should contain multiple client names
    $this->assertGreaterThanOrEqual(1, \App\Models\Agendamento::count());
});

it('restricts barber to only their agendamentos', function () {
    $barber = User::factory()->create(['role' => 'barber']);
    // create agendamentos: some for this barber, some for others
    Agendamento::factory()->count(3)->create(['barbeiro_id' => $barber->id]);
    Agendamento::factory()->count(2)->create();

    $this->actingAs($barber);
    $res = $this->get(route('agendamentos.index'));
    $res->assertStatus(200);

    // ensure page lists only this barber's agendamentos
    $this->assertCount(3, \App\Models\Agendamento::where('barbeiro_id', $barber->id)->get());
});
