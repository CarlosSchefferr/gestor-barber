<?php

namespace App\Services\Chat\Exceptions;

use RuntimeException;

/**
 * Falha de regra de negócio ao preparar/confirmar um agendamento do chat
 * (proposta expirada, dados faltando, agenda inativa, etc.). O $reason é um
 * código estável usado pelo controller para responder ao frontend.
 */
class ChatBookingException extends RuntimeException
{
    public function __construct(string $message, public readonly string $reason = 'error')
    {
        parent::__construct($message);
    }
}
