<?php

namespace App\Services\Chat\Exceptions;

use RuntimeException;

class ChatBookingException extends RuntimeException
{
    public function __construct(string $message, public readonly string $reason = 'error')
    {
        parent::__construct($message);
    }
}
