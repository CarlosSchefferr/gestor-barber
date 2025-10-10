<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows dashboard for authenticated user', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get(route('dashboard'))->assertStatus(200);
});
