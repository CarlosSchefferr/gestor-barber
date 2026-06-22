<?php

namespace App\Services\Chat\Tools;

use App\Models\Service;
use App\Models\User;
use Carbon\CarbonImmutable;

class GetAvailableTimesTool implements Tool
{
    public function name(): string
    {
        return 'get_available_times';
    }

    public function definition(): array
    {
        return [
            'type' => 'function',
            'name' => $this->name(),
            'description' => 'Retorna os horários realmente disponíveis para o serviço na data informada. Quando profissional for null, cada horário já vem vinculado a um profissional real escolhido pelo sistema.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'service_id' => [
                        'type' => 'integer',
                        'description' => 'ID do serviço (de list_services).',
                    ],
                    'data' => [
                        'type' => 'string',
                        'description' => 'Data no formato AAAA-MM-DD.',
                    ],
                    'professional_id' => [
                        'type' => ['integer', 'null'],
                        'description' => 'ID do profissional, ou null para qualquer profissional disponível.',
                    ],
                ],
                'required' => ['service_id', 'data', 'professional_id'],
                'additionalProperties' => false,
            ],
            'strict' => true,
        ];
    }

    public function handle(array $arguments, ToolContext $context): ToolResult
    {
        $service = Service::query()->where('active', true)->find($arguments['service_id'] ?? null);
        if (! $service) {
            return ToolResult::invalid('Serviço não encontrado ou inativo.');
        }

        $rawDate = is_string($arguments['data'] ?? null) ? $arguments['data'] : '';
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawDate)) {
            return ToolResult::invalid('Data inválida. Use o formato AAAA-MM-DD.');
        }

        try {
            $date = CarbonImmutable::createFromFormat('Y-m-d', $rawDate, $context->timezone())->startOfDay();
        } catch (\Throwable $e) {
            return ToolResult::invalid('Data inválida.');
        }

        $professional = $this->resolveProfessional($arguments['professional_id'] ?? null, $context, $service);
        if ($professional === false) {
            return ToolResult::invalid('Profissional não encontrado ou indisponível para este serviço.');
        }

        $times = $context->availability->availableTimes($context->config, $service, $professional, $date);

        // Para o modelo: lista compacta (sem expor todos os campos).
        $modelTimes = array_map(fn ($t) => $t['time'], $times);

        return ToolResult::ok([
            'data' => $rawDate,
            'horarios' => array_slice($modelTimes, 0, 30),
            'total' => count($times),
            'tem_disponibilidade' => count($times) > 0,
        ], [
            'times' => $times,
            'service_id' => $service->id,
            'date' => $rawDate,
            'professional_id' => $professional?->id,
        ]);
    }

    /**
     * @return User|null|false
     */
    private function resolveProfessional($professionalId, ToolContext $context, Service $service)
    {
        if ($professionalId === null) {
            return null;
        }

        $apt = $context->availability->professionalsForService($context->config, $service);
        $found = $apt->firstWhere('id', (int) $professionalId);

        return $found ?: false;
    }
}
