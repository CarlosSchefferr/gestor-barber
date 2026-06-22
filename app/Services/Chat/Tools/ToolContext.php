<?php

namespace App\Services\Chat\Tools;

use App\Models\AgendaConfig;
use App\Models\ChatSession;
use App\Services\Agenda\AvailabilityService;
use Carbon\CarbonImmutable;

/**
 * Contexto resolvido no servidor e injetado em toda tool. O escopo da
 * barbearia vem SEMPRE daqui (da AgendaConfig resolvida pelo token público),
 * nunca de argumentos enviados pelo modelo.
 */
final class ToolContext
{
    public function __construct(
        public readonly AgendaConfig $config,
        public readonly ChatSession $session,
        public readonly AvailabilityService $availability,
    ) {}

    public function timezone(): string
    {
        return $this->availability->timezone();
    }

    public function now(): CarbonImmutable
    {
        return CarbonImmutable::now($this->timezone());
    }
}
