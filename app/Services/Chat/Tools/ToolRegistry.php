<?php

namespace App\Services\Chat\Tools;

/**
 * Allowlist de ferramentas disponíveis ao modelo. Qualquer tool fora desta
 * lista é rejeitada. Nomes genéricos perigosos (execute_query, run_sql, etc.)
 * nunca existem aqui por construção.
 */
class ToolRegistry
{
    /** @var array<string,Tool> */
    private array $tools = [];

    public function __construct()
    {
        foreach ([
            new GetBusinessInformationTool,
            new ListServicesTool,
            new ListProfessionalsTool,
            new GetAvailableDatesTool,
            new GetAvailableTimesTool,
            new PrepareBookingTool,
        ] as $tool) {
            $this->tools[$tool->name()] = $tool;
        }
    }

    public function has(string $name): bool
    {
        return isset($this->tools[$name]);
    }

    public function get(string $name): ?Tool
    {
        return $this->tools[$name] ?? null;
    }

    /**
     * Definições no formato da Responses API.
     *
     * @return array<int,array<string,mixed>>
     */
    public function definitions(): array
    {
        return array_values(array_map(fn (Tool $t) => $t->definition(), $this->tools));
    }
}
