<?php

namespace App\Services\Chat\Tools;

use App\Models\Service;
use App\Models\User;

class ListProfessionalsTool implements Tool
{
    public function name(): string
    {
        return 'list_professionals';
    }

    public function definition(): array
    {
        return [
            'type' => 'function',
            'name' => $this->name(),
            'description' => 'Lista os profissionais reais e ativos da barbearia. Se um service_id válido for informado, retorna apenas os profissionais aptos a executar esse serviço.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'service_id' => [
                        'type' => ['integer', 'null'],
                        'description' => 'ID do serviço (vindo de list_services) para filtrar profissionais aptos. Null para todos.',
                    ],
                ],
                'required' => ['service_id'],
                'additionalProperties' => false,
            ],
            'strict' => true,
        ];
    }

    public function handle(array $arguments, ToolContext $context): ToolResult
    {
        $serviceId = $arguments['service_id'] ?? null;
        $service = null;

        if ($serviceId !== null) {
            $service = Service::query()->where('active', true)->find($serviceId);
            if (! $service) {
                return ToolResult::invalid('Serviço não encontrado ou inativo.');
            }
        }

        $professionals = $service
            ? $context->availability->professionalsForService($context->config, $service)
            : $context->availability->professionals($context->config);

        $items = $professionals->map(function (User $u) {
            return [
                'id' => $u->id,
                'nome' => $u->professional_name ?: $u->name,
                'cargo' => $u->cargo ?: ($u->role === 'owner' ? 'Proprietário' : 'Barbeiro'),
            ];
        })->values()->all();

        return ToolResult::ok([
            'profissionais' => $items,
            'permite_qualquer' => count($items) > 1,
        ], ['professionals' => $items]);
    }
}
