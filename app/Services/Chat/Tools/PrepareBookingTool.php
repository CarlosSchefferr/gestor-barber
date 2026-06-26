<?php

namespace App\Services\Chat\Tools;

use App\Models\Service;
use App\Models\User;
use Carbon\CarbonImmutable;

/**
 * Ferramenta: valida serviço + profissional + data + hora e gera uma proposta
 * ainda NÃO confirmada (resumo + token para a UI). Não cria o agendamento — a
 * confirmação definitiva é feita pelo frontend via ChatBookingService.
 */
class PrepareBookingTool implements Tool
{
    public function name(): string
    {
        return 'prepare_booking';
    }

    public function definition(): array
    {
        return [
            'type' => 'function',
            'name' => $this->name(),
            'description' => 'Valida serviço, profissional, data e horário e gera um resumo de agendamento ainda NÃO confirmado. NÃO cria o agendamento. A interface mostrará o resumo e o botão de confirmação. Use quando serviço, data e horário estiverem definidos.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'service_id' => ['type' => 'integer', 'description' => 'ID do serviço (de list_services).'],
                    'data' => ['type' => 'string', 'description' => 'Data AAAA-MM-DD.'],
                    'hora' => ['type' => 'string', 'description' => 'Hora HH:MM (de get_available_times).'],
                    'professional_id' => [
                        'type' => ['integer', 'null'],
                        'description' => 'ID do profissional, ou null para qualquer profissional disponível.',
                    ],
                ],
                'required' => ['service_id', 'data', 'hora', 'professional_id'],
                'additionalProperties' => false,
            ],
            'strict' => true,
        ];
    }

    public function handle(array $arguments, ToolContext $context): ToolResult
    {
        // 1) Valida serviço, data/hora (formato) e profissional.
        $service = Service::query()->where('active', true)->find($arguments['service_id'] ?? null);
        if (! $service) {
            return ToolResult::invalid('Serviço não encontrado ou inativo.');
        }

        $rawDate = is_string($arguments['data'] ?? null) ? $arguments['data'] : '';
        $rawTime = is_string($arguments['hora'] ?? null) ? $arguments['hora'] : '';
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawDate) || ! preg_match('/^\d{2}:\d{2}$/', $rawTime)) {
            return ToolResult::invalid('Data ou hora inválida.');
        }

        $professional = $this->resolveProfessional($arguments['professional_id'] ?? null, $context, $service);
        if ($professional === false) {
            return ToolResult::invalid('Profissional não encontrado ou indisponível para este serviço.');
        }

        // 2) Monta o instante de início no fuso oficial.
        try {
            $start = CarbonImmutable::createFromFormat('Y-m-d H:i', "{$rawDate} {$rawTime}", $context->timezone());
        } catch (\Throwable $e) {
            return ToolResult::invalid('Data ou hora inválida.');
        }

        // 3) Delega ao ProposalBuilder (mesmas regras do montador do site); nulo = indisponível.
        $built = (new \App\Services\Chat\ProposalBuilder($context->availability))
            ->build($context->config, $context->session, $service, $professional, $start);

        if (! $built) {
            return ToolResult::invalid('Esse horário não está mais disponível. Posso sugerir outro?', [
                'tem_disponibilidade' => false,
            ]);
        }

        $proposal = $built['proposal'];
        $summary = $built['summary'];

        // Ao modelo: apenas a confirmação de que o resumo está pronto (sem token).
        $modelOutput = [
            'resumo_pronto' => true,
            'resumo' => $summary,
            'campos_pendentes' => ['nome', 'email', 'telefone'],
            'instrucao' => 'A interface exibirá o resumo e o botão de confirmação. Não confirme você mesmo; peça que o cliente revise e confirme.',
        ];

        // À interface: inclui o token opaco da proposta (não exposto ao modelo).
        $ui = [
            'proposal' => array_merge($summary, [
                'token' => $proposal->token,
                'expires_at' => $proposal->expires_at->toIso8601String(),
                'missing_fields' => ['nome', 'email', 'telefone'],
            ]),
        ];

        return ToolResult::ok($modelOutput, $ui);
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
