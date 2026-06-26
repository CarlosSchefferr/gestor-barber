<?php

namespace App\Services\Chat\Tools;

use App\Models\Service;

/**
 * Ferramenta: lista os serviços ativos (preço/duração oficiais), com filtro
 * opcional por nome. Fonte da verdade para o modelo falar de serviços e preços.
 */
class ListServicesTool implements Tool
{
    public function name(): string
    {
        return 'list_services';
    }

    public function definition(): array
    {
        return [
            'type' => 'function',
            'name' => $this->name(),
            'description' => 'Lista os serviços reais e ativos da barbearia, com preço e duração oficiais. Use sempre que o cliente perguntar sobre serviços, preços ou duração.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'busca' => [
                        'type' => ['string', 'null'],
                        'description' => 'Texto opcional para filtrar serviços pelo nome.',
                    ],
                ],
                'required' => ['busca'],
                'additionalProperties' => false,
            ],
            'strict' => true,
        ];
    }

    public function handle(array $arguments, ToolContext $context): ToolResult
    {
        // 1) Filtro opcional por nome.
        $busca = is_string($arguments['busca'] ?? null) ? trim($arguments['busca']) : '';

        $query = Service::query()->where('active', true);
        if ($busca !== '') {
            $query->where('name', 'like', '%'.$busca.'%');
        }

        // 2) Busca limitada (teto de 20) só com os campos necessários.
        $services = $query->orderBy('name')->limit(20)->get(['id', 'name', 'description', 'duration', 'price']);

        // 3) Normaliza cada serviço com rótulos prontos (duração/preço) para modelo e UI.
        $items = $services->map(function (Service $s) {
            return [
                'id' => $s->id,
                'nome' => $s->name,
                'descricao' => $s->description ? mb_substr($s->description, 0, 160) : null,
                'duracao_min' => (int) $s->duration,
                'duracao_label' => $this->durationLabel((int) $s->duration),
                'preco' => (float) $s->price,
                'preco_label' => 'R$ '.number_format((float) $s->price, 2, ',', '.'),
            ];
        })->values()->all();

        return ToolResult::ok(['servicos' => $items], ['services' => $items]);
    }

    private function durationLabel(int $minutos): string
    {
        if ($minutos <= 0) {
            return '';
        }
        $h = intdiv($minutos, 60);
        $m = $minutos % 60;
        $parts = [];
        if ($h > 0) {
            $parts[] = $h.($h === 1 ? ' hora' : ' horas');
        }
        if ($m > 0) {
            $parts[] = $m.' min';
        }

        return implode(' e ', $parts);
    }
}
