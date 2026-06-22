<?php

namespace App\Services\Agenda;

use App\Models\AgendaConfig;
use App\Models\Agendamento;
use App\Models\ProfessionalSchedule;
use App\Models\ProfessionalService;
use App\Models\Service;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Fonte única e centralizada de cálculo de disponibilidade.
 *
 * Reutilizada pelo chat com IA, pelo formulário público tradicional e por
 * qualquer endpoint que informe horários. Considera, quando as estruturas
 * existem no projeto: escopo da barbearia, dias de atendimento, expediente
 * global, jornada e pausa do profissional, duração real do serviço,
 * agendamentos que ocupam o período, timezone oficial, antecedência mínima
 * e horizonte máximo.
 *
 * Decisões documentadas (ver docs/ai-context/06 e 14):
 * - Escopo: o sistema é uma única barbearia; profissionais são os usuários
 *   com papel "barber" ou "owner". Não há vínculo profissional<->agenda no
 *   esquema; se multiunidade for introduzida, esse vínculo deve ser criado.
 * - Aptidão: profissional apto a um serviço é aquele com registro em
 *   professional_services. Se nenhum profissional tiver o serviço configurado,
 *   o fallback é considerar todos os profissionais usando Service.duration.
 * - Duração efetiva: ProfessionalService.time_minutes quando existir; caso
 *   contrário, Service.duration (nunca o intervalo visual de slots).
 * - ends_at nulo em agendamentos legados é tratado como ocupação mínima de
 *   AgendaConfig.intervalo_slots para evitar sobreposição silenciosa.
 */
class AvailabilityService
{
    public function timezone(): string
    {
        return (string) config('chat.scheduling.timezone', 'America/Sao_Paulo');
    }

    /** @return array<int,string> */
    private function blockingStatuses(): array
    {
        return (array) config('chat.scheduling.blocking_statuses', ['agendado', 'atendido']);
    }

    private function minLeadMinutes(): int
    {
        return (int) config('chat.scheduling.min_lead_minutes', 60);
    }

    public function maxHorizonDays(): int
    {
        return (int) config('chat.scheduling.max_horizon_days', 60);
    }

    /**
     * Profissionais (User) no escopo da barbearia.
     */
    public function professionals(AgendaConfig $config): Collection
    {
        return User::query()
            ->whereIn('role', ['barber', 'owner'])
            ->orderBy('name')
            ->get(['id', 'name', 'professional_name', 'cargo', 'role', 'avatar']);
    }

    /**
     * Profissionais aptos a executar um serviço específico.
     */
    public function professionalsForService(AgendaConfig $config, Service $service): Collection
    {
        $professionals = $this->professionals($config);

        $aptIds = ProfessionalService::query()
            ->where('service_id', $service->id)
            ->whereIn('user_id', $professionals->pluck('id'))
            ->pluck('user_id')
            ->all();

        if (empty($aptIds)) {
            // Fallback documentado: nenhum vínculo configurado -> todos aptos.
            return $professionals;
        }

        return $professionals->whereIn('id', $aptIds)->values();
    }

    /**
     * Duração efetiva (minutos) do serviço para um profissional.
     */
    public function durationMinutes(Service $service, User $professional): int
    {
        $ps = ProfessionalService::query()
            ->where('user_id', $professional->id)
            ->where('service_id', $service->id)
            ->first();

        $minutes = (int) ($ps?->time_minutes ?: $service->duration);

        return max(5, $minutes);
    }

    /**
     * Preço efetivo do serviço para um profissional.
     */
    public function price(Service $service, User $professional): ?float
    {
        $ps = ProfessionalService::query()
            ->where('user_id', $professional->id)
            ->where('service_id', $service->id)
            ->first();

        $price = $ps && $ps->price !== null ? (float) $ps->price : (float) $service->price;

        return $price > 0 ? $price : null;
    }

    /**
     * Datas (Y-m-d) com pelo menos um horário disponível.
     *
     * @return array<int,string>
     */
    public function availableDates(AgendaConfig $config, Service $service, ?User $professional, int $maxResults = 14): array
    {
        if (! $config->ativa) {
            return [];
        }

        $tz = $this->timezone();
        $today = CarbonImmutable::now($tz)->startOfDay();
        $horizon = $this->maxHorizonDays();

        $dates = [];
        for ($i = 0; $i <= $horizon && count($dates) < $maxResults; $i++) {
            $date = $today->addDays($i);

            if (! $this->isServedDay($config, $date)) {
                continue;
            }

            if (! empty($this->availableTimes($config, $service, $professional, $date, 1))) {
                $dates[] = $date->toDateString();
            }
        }

        return $dates;
    }

    /**
     * Horários disponíveis para o serviço/data, cada um vinculado a um
     * profissional real escolhido pelo backend quando "qualquer profissional".
     *
     * @return array<int,array{time:string,professional_id:int,professional_name:string,starts_at:string,ends_at:string,duration_minutes:int}>
     */
    public function availableTimes(AgendaConfig $config, Service $service, ?User $professional, CarbonImmutable $date, int $limit = 0): array
    {
        if (! $config->ativa || ! $this->isServedDay($config, $date)) {
            return [];
        }

        $professionals = $professional
            ? collect([$professional])
            : $this->professionalsForService($config, $service);

        $byTime = [];

        foreach ($professionals as $prof) {
            foreach ($this->professionalSlots($config, $service, $prof, $date) as $slot) {
                $time = $slot->startsAt->format('H:i');
                // Mantém o primeiro profissional disponível para o horário.
                if (! isset($byTime[$time])) {
                    $byTime[$time] = [
                        'time' => $time,
                        'professional_id' => $prof->id,
                        'professional_name' => $prof->professional_name ?: $prof->name,
                        'starts_at' => $slot->startsAt->toIso8601String(),
                        'ends_at' => $slot->endsAt->toIso8601String(),
                        'duration_minutes' => $slot->durationMinutes,
                    ];
                }
            }
        }

        ksort($byTime);
        $result = array_values($byTime);

        return $limit > 0 ? array_slice($result, 0, $limit) : $result;
    }

    /**
     * Valida e resolve um horário específico, retornando o profissional
     * concreto (resolvendo "qualquer profissional") e a duração efetiva.
     */
    public function resolveSlot(AgendaConfig $config, Service $service, ?User $professional, CarbonImmutable $startsAt): ?ResolvedSlot
    {
        $startsAt = $startsAt->setTimezone($this->timezone());

        $professionals = $professional
            ? collect([$professional])
            : $this->professionalsForService($config, $service);

        foreach ($professionals as $prof) {
            foreach ($this->professionalSlots($config, $service, $prof, $startsAt->startOfDay()) as $slot) {
                if ($slot->startsAt->equalTo($startsAt)) {
                    return $slot;
                }
            }
        }

        return null;
    }

    /**
     * Verifica conflito de ocupação para um profissional num período.
     * Usado na revalidação transacional (idealmente sob lock).
     */
    public function hasConflict(int $professionalId, CarbonImmutable $startsAt, CarbonImmutable $endsAt, ?int $ignoreAgendamentoId = null): bool
    {
        $intervalFallback = 30;

        // Avaliação de sobreposição em PHP para ser portável entre MySQL e
        // SQLite (testes) e tratar corretamente ends_at nulo. A serialização
        // contra concorrência é feita por lock no profissional no BookingService.
        $appts = Agendamento::query()
            ->where('barbeiro_id', $professionalId)
            ->whereIn('status', $this->blockingStatuses())
            ->when($ignoreAgendamentoId, fn ($q) => $q->where('id', '!=', $ignoreAgendamentoId))
            ->where('starts_at', '<', $endsAt)
            ->where('starts_at', '>=', $startsAt->subHours(12))
            ->get(['id', 'starts_at', 'ends_at']);

        foreach ($appts as $appt) {
            $aStart = $this->wall($appt->starts_at);
            $aEnd = $appt->ends_at
                ? $this->wall($appt->ends_at)
                : $aStart->addMinutes($intervalFallback);

            if ($aStart->lessThan($endsAt) && $aEnd->greaterThan($startsAt)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Reinterpreta um datetime gravado (wall-clock) no fuso oficial, evitando
     * deslocamento causado pelo timezone UTC da aplicação.
     */
    private function wall($value): CarbonImmutable
    {
        $str = $value instanceof \DateTimeInterface
            ? $value->format('Y-m-d H:i:s')
            : (string) $value;

        return CarbonImmutable::parse($str, $this->timezone());
    }

    /**
     * Slots disponíveis de um profissional específico numa data.
     *
     * @return array<int,ResolvedSlot>
     */
    private function professionalSlots(AgendaConfig $config, Service $service, User $professional, CarbonImmutable $date): array
    {
        $tz = $this->timezone();
        $date = $date->setTimezone($tz)->startOfDay();
        $duration = $this->durationMinutes($service, $professional);
        $step = max(5, (int) ($config->intervalo_slots ?: 30));

        [$windowStart, $windowEnd] = $this->workingWindow($config, $professional, $date);
        if ($windowStart === null || $windowEnd === null || $windowStart->greaterThanOrEqualTo($windowEnd)) {
            return [];
        }

        [$breakStart, $breakEnd] = $this->breakWindow($professional, $date);

        $now = CarbonImmutable::now($tz);
        $minStart = $now->addMinutes($this->minLeadMinutes());

        $occupied = $this->occupiedIntervals($professional->id, $date);

        $slots = [];
        $cursor = $windowStart;
        while ($cursor->lessThanOrEqualTo($windowEnd->subMinutes($duration))) {
            $candidateStart = $cursor;
            $candidateEnd = $cursor->addMinutes($duration);

            $cursor = $cursor->addMinutes($step);

            if ($candidateStart->lessThan($minStart)) {
                continue;
            }

            // Pausa do profissional.
            if ($breakStart && $breakEnd && $this->overlaps($candidateStart, $candidateEnd, $breakStart, $breakEnd)) {
                continue;
            }

            // Ocupação por agendamentos.
            $conflict = false;
            foreach ($occupied as [$oStart, $oEnd]) {
                if ($this->overlaps($candidateStart, $candidateEnd, $oStart, $oEnd)) {
                    $conflict = true;
                    break;
                }
            }
            if ($conflict) {
                continue;
            }

            $slots[] = new ResolvedSlot(
                service: $service,
                professional: $professional,
                startsAt: $candidateStart,
                endsAt: $candidateEnd,
                durationMinutes: $duration,
                price: $this->price($service, $professional),
            );
        }

        return $slots;
    }

    /**
     * @return array{0:?CarbonImmutable,1:?CarbonImmutable}
     */
    private function workingWindow(AgendaConfig $config, User $professional, CarbonImmutable $date): array
    {
        $start = $this->applyTime($date, $config->horario_inicio, '08:00');
        $end = $this->applyTime($date, $config->horario_fim, '18:00');

        $schedule = ProfessionalSchedule::where('user_id', $professional->id)->first();
        if ($schedule) {
            if ($schedule->entry_time) {
                $entry = $this->applyTime($date, $schedule->entry_time->format('H:i'), null);
                if ($entry) {
                    $start = $start->greaterThan($entry) ? $start : $entry;
                }
            }
            if ($schedule->exit_time) {
                $exit = $this->applyTime($date, $schedule->exit_time->format('H:i'), null);
                if ($exit) {
                    $end = $end->lessThan($exit) ? $end : $exit;
                }
            }
        }

        return [$start, $end];
    }

    /**
     * @return array{0:?CarbonImmutable,1:?CarbonImmutable}
     */
    private function breakWindow(User $professional, CarbonImmutable $date): array
    {
        $schedule = ProfessionalSchedule::where('user_id', $professional->id)->first();
        if (! $schedule || ! $schedule->break_start || ! $schedule->break_end) {
            return [null, null];
        }

        return [
            $this->applyTime($date, $schedule->break_start->format('H:i'), null),
            $this->applyTime($date, $schedule->break_end->format('H:i'), null),
        ];
    }

    /**
     * Intervalos ocupados (start/end) do profissional na data.
     *
     * @return array<int,array{0:CarbonImmutable,1:CarbonImmutable}>
     */
    private function occupiedIntervals(int $professionalId, CarbonImmutable $date): array
    {
        $tz = $this->timezone();
        $dayStart = $date->startOfDay();
        $dayEnd = $date->endOfDay();
        $intervalFallback = 30;

        $appts = Agendamento::query()
            ->where('barbeiro_id', $professionalId)
            ->whereIn('status', $this->blockingStatuses())
            ->whereBetween('starts_at', [$dayStart->subHours(12), $dayEnd])
            ->get(['starts_at', 'ends_at']);

        $intervals = [];
        foreach ($appts as $appt) {
            $start = $this->wall($appt->starts_at);
            $end = $appt->ends_at
                ? $this->wall($appt->ends_at)
                : $start->addMinutes($intervalFallback);

            if ($end->lessThanOrEqualTo($dayStart) || $start->greaterThanOrEqualTo($dayEnd)) {
                continue;
            }

            $intervals[] = [$start, $end];
        }

        return $intervals;
    }

    private function isServedDay(AgendaConfig $config, CarbonImmutable $date): bool
    {
        $dias = $config->dias_atendimento;
        if (empty($dias) || ! is_array($dias)) {
            // Sem configuração de dias, assume todos os dias atendidos.
            return true;
        }

        $map = [0 => 'domingo', 1 => 'segunda', 2 => 'terca', 3 => 'quarta', 4 => 'quinta', 5 => 'sexta', 6 => 'sabado'];

        return in_array($map[$date->dayOfWeek] ?? null, $dias, true);
    }

    private function applyTime(CarbonImmutable $date, ?string $time, ?string $default): ?CarbonImmutable
    {
        $value = $time ?: $default;
        if (! $value) {
            return null;
        }

        $parts = explode(':', $value);
        $hour = (int) ($parts[0] ?? 0);
        $minute = (int) ($parts[1] ?? 0);

        return $date->setTime($hour, $minute, 0);
    }

    private function overlaps(CarbonImmutable $aStart, CarbonImmutable $aEnd, CarbonImmutable $bStart, CarbonImmutable $bEnd): bool
    {
        return $aStart->lessThan($bEnd) && $aEnd->greaterThan($bStart);
    }
}
