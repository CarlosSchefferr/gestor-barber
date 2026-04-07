<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RealisticBarbershopSeeder extends Seeder
{
    public function run(): void
    {
        $faker = FakerFactory::create('pt_BR');
        $year = (int) now()->year;

        $this->truncateDomainTables();

        $owner = User::query()->updateOrCreate(
            ['email' => 'proprietario@barberpro.com.br'],
            [
                'name' => 'Carlos Henrique Souza',
                'password' => '12345678',
                'role' => 'owner',
                'phone' => '(11) 98888-0001',
                'date_of_birth' => '1986-05-12',
                'email_verified_at' => now(),
            ]
        );

        $attendant = User::query()->updateOrCreate(
            ['email' => 'atendimento@barberpro.com.br'],
            [
                'name' => 'Fernanda Lima',
                'password' => '12345678',
                'role' => 'attendant',
                'phone' => '(11) 97777-0002',
                'date_of_birth' => '1994-09-03',
                'email_verified_at' => now(),
            ]
        );

        $barbers = collect([
            ['name' => 'Mateus Oliveira', 'email' => 'mateus.oliveira@barberpro.com.br', 'phone' => '(11) 98888-1001', 'date_of_birth' => '1991-02-11'],
            ['name' => 'Joao Vitor Santos', 'email' => 'joao.santos@barberpro.com.br', 'phone' => '(11) 98888-1002', 'date_of_birth' => '1995-06-21'],
            ['name' => 'Guilherme Ferreira', 'email' => 'guilherme.ferreira@barberpro.com.br', 'phone' => '(11) 98888-1003', 'date_of_birth' => '1990-11-02'],
            ['name' => 'Rafael Araujo', 'email' => 'rafael.araujo@barberpro.com.br', 'phone' => '(11) 98888-1004', 'date_of_birth' => '1993-01-16'],
            ['name' => 'Pedro Henrique Almeida', 'email' => 'pedro.almeida@barberpro.com.br', 'phone' => '(11) 98888-1005', 'date_of_birth' => '1997-07-29'],
            ['name' => 'Lucas Martins', 'email' => 'lucas.martins@barberpro.com.br', 'phone' => '(11) 98888-1006', 'date_of_birth' => '1992-03-18'],
        ])->map(function (array $barber) {
            return User::query()->updateOrCreate(
                ['email' => $barber['email']],
                [
                    'name' => $barber['name'],
                    'password' => '12345678',
                    'role' => 'barber',
                    'phone' => $barber['phone'],
                    'date_of_birth' => $barber['date_of_birth'],
                    'email_verified_at' => now(),
                ]
            );
        })->values();

        $services = $this->seedServices();
        $products = $this->seedProducts();
        $clientes = $this->seedClientes($faker, 650);
        $appointments = $this->seedAgendamentos($faker, $year, $clientes->pluck('id')->all(), $barbers->pluck('id')->all(), $services, [$owner->id, $attendant->id]);

        $this->seedAgendamentoProdutos($products);
        $this->seedMetas($year, $owner->id, $appointments['receita_concluida']);
        $this->seedTransacoes($faker, $year, $appointments['receita_concluida']);
        $this->seedAgendaPublica($barbers->pluck('id')->all());

        DB::update('
            UPDATE clientes c
            LEFT JOIN (
                SELECT cliente_id, MAX(starts_at) AS last_at
                FROM agendamentos
                GROUP BY cliente_id
            ) a ON a.cliente_id = c.id
            SET c.last_appointment_at = a.last_at
        ');
    }

    private function truncateDomainTables(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach (['agendamento_produto', 'agenda_imagens', 'agenda_configs', 'agendamentos', 'transacoes', 'metas', 'products', 'services', 'clientes'] as $table) {
            DB::table($table)->truncate();
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    private function seedServices(): array
    {
        $services = [
            ['name' => 'Corte Social', 'description' => 'Corte tradicional na tesoura e maquina.', 'duration' => 35, 'price' => 45.00, 'commission' => 18.00, 'active' => true],
            ['name' => 'Degrade', 'description' => 'Corte degrade com acabamento detalhado.', 'duration' => 40, 'price' => 55.00, 'commission' => 22.00, 'active' => true],
            ['name' => 'Barba Completa', 'description' => 'Modelagem com navalha e toalha quente.', 'duration' => 30, 'price' => 35.00, 'commission' => 14.00, 'active' => true],
            ['name' => 'Corte + Barba', 'description' => 'Pacote completo de corte e barba.', 'duration' => 65, 'price' => 80.00, 'commission' => 30.00, 'active' => true],
            ['name' => 'Sobrancelha', 'description' => 'Alinhamento e desenho de sobrancelha.', 'duration' => 15, 'price' => 18.00, 'commission' => 7.00, 'active' => true],
            ['name' => 'Hidratacao Capilar', 'description' => 'Tratamento rapido para cabelo.', 'duration' => 20, 'price' => 25.00, 'commission' => 9.00, 'active' => true],
            ['name' => 'Platinado', 'description' => 'Descoloracao e tonalizacao profissional.', 'duration' => 120, 'price' => 180.00, 'commission' => 60.00, 'active' => true],
            ['name' => 'Pezinho', 'description' => 'Acabamento rapido para manutencao.', 'duration' => 15, 'price' => 15.00, 'commission' => 6.00, 'active' => true],
            ['name' => 'Corte Infantil', 'description' => 'Corte especializado para criancas.', 'duration' => 30, 'price' => 38.00, 'commission' => 15.00, 'active' => true],
            ['name' => 'Progressiva Masculina', 'description' => 'Alinhamento e reducao de volume.', 'duration' => 90, 'price' => 140.00, 'commission' => 45.00, 'active' => false],
        ];

        DB::table('services')->insert(array_map(fn (array $service) => [
            ...$service,
            'created_at' => now(),
            'updated_at' => now(),
        ], $services));

        return $services;
    }

    private function seedProducts(): array
    {
        $products = [
            ['name' => 'Pomada Modeladora Fosca', 'description' => 'Fixacao media com efeito natural.', 'price' => 39.90, 'quantity' => 45],
            ['name' => 'Pomada Brilho Intenso', 'description' => 'Ideal para penteados classicos.', 'price' => 42.90, 'quantity' => 32],
            ['name' => 'Gel Fixador Extra Forte', 'description' => 'Fixacao prolongada para eventos.', 'price' => 29.90, 'quantity' => 38],
            ['name' => 'Shampoo Anticaspa', 'description' => 'Controle de oleosidade e caspas.', 'price' => 34.90, 'quantity' => 28],
            ['name' => 'Shampoo para Barba', 'description' => 'Higienizacao suave dos fios.', 'price' => 36.90, 'quantity' => 30],
            ['name' => 'Balm para Barba', 'description' => 'Hidratacao e alinhamento da barba.', 'price' => 44.90, 'quantity' => 34],
            ['name' => 'Oleo para Barba Premium', 'description' => 'Blend de oleos com fragrancia amadeirada.', 'price' => 49.90, 'quantity' => 26],
            ['name' => 'Loção Pos Barba', 'description' => 'Calmante para pele sensivel.', 'price' => 31.90, 'quantity' => 27],
            ['name' => 'Spray Texturizador', 'description' => 'Volume e textura sem pesar.', 'price' => 46.90, 'quantity' => 21],
            ['name' => 'Tonico Capilar', 'description' => 'Estimula o couro cabeludo.', 'price' => 59.90, 'quantity' => 18],
            ['name' => 'Escova de Barba', 'description' => 'Cerdas naturais para acabamento.', 'price' => 27.90, 'quantity' => 40],
            ['name' => 'Pente de Madeira', 'description' => 'Antiestatico para uso diario.', 'price' => 19.90, 'quantity' => 52],
            ['name' => 'Navalha Profissional', 'description' => 'Uso profissional com alto desempenho.', 'price' => 89.90, 'quantity' => 10],
            ['name' => 'Kit Barba Iniciante', 'description' => 'Shampoo, balm e pente.', 'price' => 99.90, 'quantity' => 15],
            ['name' => 'Cera Modeladora Flexivel', 'description' => 'Fixacao leve e remodelavel.', 'price' => 37.90, 'quantity' => 31],
        ];

        DB::table('products')->insert(array_map(fn (array $product) => [
            ...$product,
            'created_at' => now(),
            'updated_at' => now(),
        ], $products));

        return $products;
    }

    private function seedClientes($faker, int $total): \Illuminate\Support\Collection
    {
        $firstNames = ['Gabriel', 'Miguel', 'Arthur', 'Heitor', 'Theo', 'Davi', 'Bernardo', 'Samuel', 'Matheus', 'Pedro', 'Lucas', 'Nicolas', 'Rafael', 'Joao', 'Guilherme', 'Vitor', 'Enzo', 'Felipe', 'Bruno', 'Diego', 'Leandro', 'Caio', 'Anderson', 'Tiago', 'Rodrigo', 'Marcelo', 'Fernando', 'Ricardo', 'Alexandre', 'Henrique', 'Paulo', 'Eduardo', 'Antonio', 'Julio', 'Leonardo', 'Cristiano', 'Cesar', 'Igor', 'Vinicius', 'Wesley', 'Yuri', 'Renato', 'Douglas', 'Alan', 'Jorge', 'Marcos', 'Fabio', 'Claudio', 'Sergio', 'Murilo'];
        $lastNames = ['Silva', 'Santos', 'Oliveira', 'Souza', 'Pereira', 'Costa', 'Rodrigues', 'Almeida', 'Nascimento', 'Lima', 'Araujo', 'Fernandes', 'Carvalho', 'Gomes', 'Martins', 'Rocha', 'Dias', 'Barbosa', 'Ribeiro', 'Cardoso', 'Melo', 'Freitas', 'Correia', 'Moura', 'Batista', 'Teixeira', 'Monteiro', 'Moreira', 'Nunes', 'Sales', 'Machado', 'Leite', 'Pinto', 'Farias', 'Vieira', 'Assis', 'Cavalcanti', 'Peixoto', 'Rezende', 'Tavares'];

        $owner = User::where('email', 'proprietario@barberpro.com.br')->first();

        $used = [];
        $rows = [];
        for ($i = 1; $i <= $total; $i++) {
            do {
                $nome = $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)] . ' ' . $lastNames[array_rand($lastNames)];
            } while (isset($used[$nome]));
            $used[$nome] = true;

            $rows[] = [
                'nome' => $nome,
                'email' => Str::slug($nome, '.') . $i . '@gmail.com',
                'telefone' => sprintf('(11) 9%04d-%04d', random_int(1000, 9999), random_int(1000, 9999)),
                'data_nascimento' => $faker->dateTimeBetween('1960-01-01', '2008-12-31')->format('Y-m-d'),
                'cep' => sprintf('%05d-%03d', random_int(10000, 99999), random_int(100, 999)),
                'bairro' => $faker->citySuffix(),
                'foto' => null,
                'observacoes' => $faker->boolean(28) ? $faker->sentence() : null,
                'active' => $faker->boolean(94),
                'created_by' => $owner?->id,
                'updated_by' => $owner?->id,
                'last_appointment_at' => null,
                'created_at' => now()->subDays(random_int(20, 900)),
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($rows, 250) as $chunk) {
            DB::table('clientes')->insert($chunk);
        }

        return DB::table('clientes')->select('id')->orderBy('id')->get();
    }

    private function seedAgendamentos($faker, int $year, array $clienteIds, array $barberIds, array $services, array $creatorIds): array
    {
        $serviceColors = [
            'Corte Social' => '#2563eb',
            'Degrade' => '#0ea5e9',
            'Barba Completa' => '#16a34a',
            'Corte + Barba' => '#f59e0b',
            'Sobrancelha' => '#8b5cf6',
            'Hidratacao Capilar' => '#14b8a6',
            'Platinado' => '#ef4444',
            'Pezinho' => '#475569',
            'Corte Infantil' => '#22c55e',
            'Progressiva Masculina' => '#e11d48',
        ];

        $rows = [];
        $totalReceitaConcluida = 0.0;
        $start = Carbon::create($year, 1, 1)->startOfDay();
        $end = Carbon::create($year, 12, 31)->endOfDay();

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $rand = random_int(1, 100);

            if ($rand <= 28) {
                $appointmentsOnDay = 0;
            } elseif ($rand <= 55) {
                $appointmentsOnDay = random_int(1, 5);
            } elseif ($rand <= 78) {
                $appointmentsOnDay = random_int(5, 12);
            } else {
                $appointmentsOnDay = random_int(12, 22);
            }

            for ($i = 0; $i < $appointmentsOnDay; $i++) {
                $service = $services[array_rand($services)];
                $startsAt = $this->randomTimeForDate($date, $service['duration']);
                $endsAt = $startsAt->copy()->addMinutes((int) $service['duration']);

                $status = $this->pickStatus($startsAt);
                $price = round((float) $service['price'] * ($faker->randomFloat(2, 0.95, 1.20)), 2);

                if ($status === 'atendido') {
                    $totalReceitaConcluida += $price;
                }

                $rows[] = [
                    'cliente_id' => $clienteIds[array_rand($clienteIds)],
                    'barbeiro_id' => $barberIds[array_rand($barberIds)],
                    'user_id' => $faker->boolean(35) ? $creatorIds[array_rand($creatorIds)] : null,
                    'starts_at' => $startsAt,
                    'ends_at' => $endsAt,
                    'status' => $status,
                    'servico' => $service['name'],
                    'color' => $serviceColors[$service['name']] ?? '#3b82f6',
                    'price' => $price,
                    'observacoes' => $faker->boolean(18) ? $faker->sentence() : null,
                    'public_token' => $faker->boolean(35) ? (string) Str::uuid() : null,
                    'created_at' => $startsAt->copy()->subDays(random_int(1, 20)),
                    'updated_at' => $startsAt->copy()->subDays(random_int(0, 5)),
                ];
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('agendamentos')->insert($chunk);
        }

        return [
            'receita_concluida' => round($totalReceitaConcluida, 2),
            'total' => count($rows),
        ];
    }

    private function seedAgendamentoProdutos($products): void
    {
        $agendamentos = DB::table('agendamentos')->select('id', 'status', 'price')->where('status', 'atendido')->get();
        $rows = [];

        foreach ($agendamentos as $agendamento) {
            if (random_int(1, 100) <= 35) {
                $numProdutos = random_int(1, 3);
                for ($i = 0; $i < $numProdutos; $i++) {
                    $produto = (object) $products[array_rand($products)];
                    $quantity = random_int(1, 2);
                    $price = (float) $produto->price * $quantity;

                    $rows[] = [
                        'agendamento_id' => $agendamento->id,
                        'produto_id' => DB::table('products')->where('name', $produto->name)->value('id') ?? 1,
                        'quantity' => $quantity,
                        'price' => round($price, 2),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        if (!empty($rows)) {
            foreach (array_chunk($rows, 500) as $chunk) {
                DB::table('agendamento_produto')->insert($chunk);
            }
        }
    }

    private function randomTimeForDate(Carbon $date, int $duration): Carbon
    {
        $peakHours = [9, 10, 11, 14, 15, 16, 17, 18];
        $offPeakHours = [8, 12, 13, 19];
        $hours = random_int(1, 100) <= 75 ? $peakHours : $offPeakHours;
        $hour = $hours[array_rand($hours)];
        $minute = random_int(0, 1) ? 30 : 0;

        $startsAt = $date->copy()->setTime($hour, $minute, 0);
        $lastPossibleStart = $date->copy()->setTime(20, 0, 0)->subMinutes($duration);

        if ($startsAt->gt($lastPossibleStart)) {
            $startsAt = $lastPossibleStart;
        }

        return $startsAt;
    }

    private function pickStatus(Carbon $startsAt): string
    {
        if ($startsAt->isFuture()) {
            return $this->weightedPick([
                'agendado' => 90,
                'cancelado' => 10,
            ]);
        }

        if ($startsAt->isCurrentMonth()) {
            return $this->weightedPick([
                'agendado' => 55,
                'atendido' => 35,
                'cancelado' => 7,
                'não compareceu' => 3,
            ]);
        }

        return $this->weightedPick([
            'atendido' => 81,
            'cancelado' => 11,
            'não compareceu' => 8,
        ]);
    }

    private function weightedPick(array $weights): string
    {
        $sum = array_sum($weights);
        $rand = random_int(1, $sum);
        $running = 0;

        foreach ($weights as $value => $weight) {
            $running += $weight;
            if ($rand <= $running) {
                return $value;
            }
        }

        return array_key_first($weights);
    }

    private function seedMetas(int $year, int $ownerId, float $receitaConcluida): void
    {
        $rows = [];
        for ($month = 1; $month <= 12; $month++) {
            $inicio = Carbon::create($year, $month, 1);
            $limite = $inicio->copy()->endOfMonth();
            $valorMeta = random_int(32000, 46000);
            $valorAtual = round($valorMeta * (random_int(72, 105) / 100), 2);

            $rows[] = [
                'nome' => 'Meta de faturamento ' . $inicio->translatedFormat('F'),
                'descricao' => 'Acompanhar crescimento mensal da operacao.',
                'valor_meta' => $valorMeta,
                'valor_atual' => $valorAtual,
                'data_inicio' => $inicio->toDateString(),
                'data_limite' => $limite->toDateString(),
                'quem_tem_acesso' => 'all',
                'tipo' => 'financeira',
                'created_by' => $ownerId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $rows[] = [
            'nome' => 'Meta anual de faturamento',
            'descricao' => 'Consolidado anual para acompanhamento estrategico.',
            'valor_meta' => 480000,
            'valor_atual' => $receitaConcluida,
            'data_inicio' => Carbon::create($year, 1, 1)->toDateString(),
            'data_limite' => Carbon::create($year, 12, 31)->toDateString(),
            'quem_tem_acesso' => 'owners',
            'tipo' => 'financeira',
            'created_by' => $ownerId,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::table('metas')->insert($rows);
    }

    private function seedTransacoes($faker, int $year, float $receitaConcluida): void
    {
        $rows = [];
        $despesasFixas = [
            'Aluguel',
            'Energia eletrica',
            'Internet',
            'Reposicao de produtos',
            'Marketing digital',
            'Contabilidade',
        ];

        for ($month = 1; $month <= 12; $month++) {
            $inicio = Carbon::create($year, $month, 1);
            $fim = $inicio->copy()->endOfMonth();

            $rows[] = [
                'descricao' => 'Faturamento de servicos - ' . $inicio->translatedFormat('F/Y'),
                'tipo' => 'receita',
                'valor' => round($receitaConcluida / 12 * $faker->randomFloat(2, 0.88, 1.12), 2),
                'data' => $fim->toDateString(),
                'status' => 'Confirmado',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            for ($i = 0; $i < 3; $i++) {
                $rows[] = [
                    'descricao' => $despesasFixas[array_rand($despesasFixas)] . ' - ' . $inicio->translatedFormat('F/Y'),
                    'tipo' => 'despesa',
                    'valor' => $faker->randomFloat(2, 450, 6200),
                    'data' => $inicio->copy()->addDays(random_int(1, 25))->toDateString(),
                    'status' => 'Confirmado',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('transacoes')->insert($rows);
    }

    private function seedAgendaPublica(array $barberIds): void
    {
        $configs = [];
        foreach ($barberIds as $barberId) {
            $configs[] = [
                'user_id' => $barberId,
                'nome_barbearia' => 'Barber Pro Unidade ' . $barberId,
                'descricao' => 'Agenda publica para autoagendamento de clientes.',
                'telefone' => '(11) 4002-8922',
                'endereco' => 'Rua das Palmeiras, ' . random_int(80, 999) . ' - Sao Paulo/SP',
                'horario_inicio' => '08:00',
                'horario_fim' => '20:00',
                'intervalo_slots' => 30,
                'dias_atendimento' => json_encode(['segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado']),
                'ativa' => true,
                'public_token' => (string) Str::uuid(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('agenda_configs')->insert($configs);

        $agendaConfigIds = DB::table('agenda_configs')->pluck('id')->all();
        $images = [];
        foreach ($agendaConfigIds as $agendaConfigId) {
            $images[] = [
                'agenda_config_id' => $agendaConfigId,
                'caminho_imagem' => 'agenda/banner-' . random_int(1, 6) . '.jpg',
                'ordem' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $images[] = [
                'agenda_config_id' => $agendaConfigId,
                'caminho_imagem' => 'agenda/ambiente-' . random_int(1, 6) . '.jpg',
                'ordem' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('agenda_imagens')->insert($images);
    }
}
