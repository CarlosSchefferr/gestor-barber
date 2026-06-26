<?php

namespace App\Services\Chat\Tools;

use App\Models\Service;
use App\Models\User;
use Carbon\CarbonImmutable;

/**
 * Ferramenta: retorna as próximas datas (até 14) com ao menos um horário livre
 * para o serviço (e profissional, se informado). Sempre consultada antes de
 * sugerir datas — a disponibilidade nunca vem do catálogo, só das ferramentas.
 */
class GetAvailableDatesTool implements Tool
{
    public function name(): string
    {
        return 'get_available_dates';
    }

    public function definition(): array
    {
        return [
            'type' => 'function',
            'name' => $this->name(),
            'description' => 'Retorna as próximas datas que possuem pelo menos um horário realmente disponível para o serviço (e profissional, se informado). Sempre consulte antes de sugerir datas.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'service_id' => [
                        'type' => 'integer',
                        'description' => 'ID do serviço (de list_services).',
                    ],
                    'professional_id' => [
                        'type' => ['integer', 'null'],
                        'description' => 'ID do profissional escolhido, ou null para qualquer profissional disponível.',
                    ],
                ],
                'required' => ['service_id', 'professional_id'],
                'additionalProperties' => false,
            ],
            'strict' => true,
        ];
    }

    public function handle(array $arguments, ToolContext $context): ToolResult
    {
        // 1) Valida o serviço.
        $service = Service::query()->where('active', true)->find($arguments['service_id'] ?? null);
        if (! $service) {
            return ToolResult::invalid('Serviço não encontrado ou inativo.');
        }

        // 2) Resolve o profissional (null = qualquer; false = inválido).
        $professional = $this->resolveProfessional($arguments['professional_id'] ?? null, $context, $service);
        if ($professional === false) {
            return ToolResult::invalid('Profissional não encontrado ou indisponível para este serviço.');
        }

        // 3) Consulta as datas livres e devolve com rótulo legível (ex.: "Sexta, 27/06").
        $dates = $context->availability->availableDates($context->config, $service, $professional, 14);

        $items = array_map(function (string $date) use ($context) {
            $d = CarbonImmutable::parse($date, $context->timezone());

            return [
                'data' => $date,
                'label' => $this->dateLabel($d),
            ];
        }, $dates);

        return ToolResult::ok([
            'datas' => $items,
            'tem_disponibilidade' => count($items) > 0,
        ], ['dates' => $items, 'service_id' => $service->id, 'professional_id' => $professional?->id]);
    }

    /**
     * @return User|null|false false quando inválido; null quando "qualquer".
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

    private function dateLabel(CarbonImmutable $d): string
    {
        $dias = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];

        return $dias[$d->dayOfWeek].', '.$d->format('d/m');
    }
}
