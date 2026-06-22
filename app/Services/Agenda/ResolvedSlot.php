<?php

namespace App\Services\Agenda;

use App\Models\Service;
use App\Models\User;
use Carbon\CarbonImmutable;

/**
 * Resultado determinístico, calculado pelo backend, de um horário disponível.
 * É a única base factual para propor/confirmar um agendamento.
 */
final class ResolvedSlot
{
    public function __construct(
        public readonly Service $service,
        public readonly User $professional,
        public readonly CarbonImmutable $startsAt,
        public readonly CarbonImmutable $endsAt,
        public readonly int $durationMinutes,
        public readonly ?float $price,
    ) {}

    public function toArray(): array
    {
        return [
            'service_id' => $this->service->id,
            'professional_id' => $this->professional->id,
            'starts_at' => $this->startsAt->toIso8601String(),
            'ends_at' => $this->endsAt->toIso8601String(),
            'duration_minutes' => $this->durationMinutes,
            'price' => $this->price,
        ];
    }
}
