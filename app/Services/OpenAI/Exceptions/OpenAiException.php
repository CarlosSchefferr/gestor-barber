<?php

namespace App\Services\OpenAI\Exceptions;

use RuntimeException;

/**
 * Falha ao comunicar com a OpenAI (timeout, 4xx/5xx, resposta inválida).
 * Não deve expor detalhes técnicos ao usuário final.
 */
class OpenAiException extends RuntimeException
{
    public function __construct(string $message, public readonly string $reason = 'error', public readonly ?int $status = null)
    {
        parent::__construct($message);
    }
}
