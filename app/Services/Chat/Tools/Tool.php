<?php

namespace App\Services\Chat\Tools;

/**
 * Contrato de uma ferramenta controlada pelo Laravel exposta ao modelo.
 * Toda tool valida argumentos, respeita o escopo da barbearia e devolve
 * apenas dados mínimos.
 */
interface Tool
{
    public function name(): string;

    /**
     * Definição da function tool no formato da Responses API
     * (type=function, name, description, parameters strict).
     *
     * @return array<string,mixed>
     */
    public function definition(): array;

    /**
     * @param  array<string,mixed>  $arguments
     */
    public function handle(array $arguments, ToolContext $context): ToolResult;
}
