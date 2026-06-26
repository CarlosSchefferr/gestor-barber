<?php

namespace App\Services\Chat\Tools;

use Illuminate\Support\Str;

/**
 * Ferramenta: devolve dados institucionais confirmados (nome, endereço,
 * telefone, horário de funcionamento) para responder dúvidas sobre a barbearia.
 */
class GetBusinessInformationTool implements Tool
{
    public function name(): string
    {
        return 'get_business_information';
    }

    public function definition(): array
    {
        return [
            'type' => 'function',
            'name' => $this->name(),
            'description' => 'Retorna informações institucionais confirmadas da barbearia: nome, endereço, telefone e horário de funcionamento. Use para responder dúvidas sobre a barbearia.',
            'parameters' => [
                'type' => 'object',
                'properties' => (object) [],
                'required' => [],
                'additionalProperties' => false,
            ],
            'strict' => true,
        ];
    }

    public function handle(array $arguments, ToolContext $context): ToolResult
    {
        $config = $context->config;

        // 1) Converte os dias de atendimento para rótulos curtos (Seg, Ter, ...).
        $diasMap = [
            'segunda' => 'Seg', 'terca' => 'Ter', 'quarta' => 'Qua', 'quinta' => 'Qui',
            'sexta' => 'Sex', 'sabado' => 'Sáb', 'domingo' => 'Dom',
        ];
        $dias = collect((array) $config->dias_atendimento)
            ->map(fn ($d) => $diasMap[$d] ?? null)
            ->filter()
            ->values()
            ->all();

        $output = [
            'nome' => $config->nome_barbearia,
            'descricao' => $config->descricao ? Str::limit($config->descricao, 280) : null,
            'endereco' => $config->endereco,
            'telefone' => $config->telefone,
            'horario_funcionamento' => [
                'inicio' => Str::substr((string) $config->horario_inicio, 0, 5),
                'fim' => Str::substr((string) $config->horario_fim, 0, 5),
                'dias' => $dias,
            ],
            'formas_atendimento' => ['agendamento_online'],
        ];

        return ToolResult::ok($output, ['business' => $output]);
    }
}
