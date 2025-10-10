<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Cliente;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Agendamento>
 */
class AgendamentoFactory extends Factory
{
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('+1 days', '+30 days');
        $end = (clone $start)->modify('+45 minutes');

        return [
            'cliente_id' => Cliente::factory(),
            'barbeiro_id' => User::factory()->state(['role' => 'barber']),
            'user_id' => null,
            'starts_at' => $start,
            'ends_at' => $end,
            'status' => 'agendado',
            'servico' => fake()->randomElement(['Corte', 'Barba', 'Corte + Barba']),
            'price' => fake()->randomFloat(2, 30, 150),
            'observacoes' => fake()->optional()->sentence(),
        ];
    }
}
