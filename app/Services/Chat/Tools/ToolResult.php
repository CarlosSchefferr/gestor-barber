<?php

namespace App\Services\Chat\Tools;

/**
 * Resultado de uma tool: payload compacto devolvido ao modelo, dados
 * estruturados para a interface e o status para auditoria.
 */
final class ToolResult
{
    /**
     * @param  array<string,mixed>  $output  Devolvido ao modelo (compacto, sem PII).
     * @param  array<string,mixed>  $ui  Estruturas para a interface renderizar.
     */
    private function __construct(
        public readonly array $output,
        public readonly array $ui,
        public readonly string $status,
    ) {}

    public static function ok(array $output, array $ui = []): self
    {
        return new self($output, $ui, 'ok');
    }

    public static function invalid(string $message, array $extra = []): self
    {
        return new self(array_merge(['error' => $message], $extra), [], 'invalid');
    }

    public static function error(string $message): self
    {
        return new self(['error' => $message], [], 'error');
    }
}
