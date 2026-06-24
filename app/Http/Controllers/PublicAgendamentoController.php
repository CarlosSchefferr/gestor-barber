<?php

namespace App\Http\Controllers;

use App\Models\AgendaConfig;
use App\Models\Agendamento;
use App\Models\Product;
use App\Models\ProfessionalService;
use App\Models\Service;
use App\Models\User;
use App\Services\Agenda\AvailabilityService;
use App\Services\Agenda\BookingService;
use App\Services\Agenda\Exceptions\SlotUnavailableException;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicAgendamentoController extends Controller
{
    public function __construct(
        private readonly AvailabilityService $availability,
        private readonly BookingService $booking,
    ) {}

    /**
     * Display the public booking page
     */
    public function show(string $publicToken): View
    {
        $agendaConfig = AgendaConfig::forPublicIdentifier($publicToken)
            ->where('ativa', true)
            ->firstOrFail();

        return view('public.agendamento', [
            'agendaConfig' => $agendaConfig,
        ]);
    }

    /**
     * Get agenda configuration via API (for Alpine.js)
     */
    public function getAgendaConfig(string $publicToken): JsonResponse
    {
        $agendaConfig = AgendaConfig::forPublicIdentifier($publicToken)
            ->where('ativa', true)
            ->with(['imagens' => function ($query) {
                $query->orderBy('ordem');
            }, 'user.agendamentos' => function ($query) {
                $query->whereDate('starts_at', '>=', now());
            }])
            ->firstOrFail();

        $ownerId = $agendaConfig->user_id;

        $imagens = $agendaConfig->imagens->map(fn ($img) => [
            'id' => $img->id,
            'url' => asset('storage/'.$img->caminho_imagem),
        ])->values();

        // Serviços ativos
        $servicesAtivos = Service::where('active', true)->orderBy('name')->get();
        $servicos = $servicesAtivos->map(fn ($s) => [
            'id' => $s->id,
            'nome' => $s->name,
            'descricao' => $s->description,
            'duracao' => (int) $s->duration,
            'duracao_label' => $this->formatarDuracao((int) $s->duration),
            'preco' => (float) $s->price,
            'preco_label' => 'R$ '.number_format((float) $s->price, 2, ',', '.'),
        ])->values();

        // Vínculos serviço<->profissional (duração/preço efetivos por profissional).
        $psByUser = ProfessionalService::whereIn('service_id', $servicesAtivos->pluck('id'))
            ->get()
            ->groupBy('user_id');

        // Barbeiros (profissionais) com seus serviços (Figma: navegar e ver serviços).
        $barbeiros = User::whereIn('role', ['barber', 'owner'])
            ->orderBy('name')
            ->get()
            ->map(function ($u) use ($psByUser, $servicesAtivos) {
                $vinculos = $psByUser->get($u->id);

                if ($vinculos && $vinculos->isNotEmpty()) {
                    $servicosProf = $vinculos->map(function ($ps) use ($servicesAtivos) {
                        $svc = $servicesAtivos->firstWhere('id', $ps->service_id);
                        if (! $svc) {
                            return null;
                        }
                        $dur = (int) ($ps->time_minutes ?: $svc->duration);
                        $preco = $ps->price !== null ? (float) $ps->price : (float) $svc->price;

                        return [
                            'id' => $svc->id,
                            'nome' => $svc->name,
                            'duracao' => $dur,
                            'duracao_label' => $this->formatarDuracao($dur),
                            'preco' => $preco,
                            'preco_label' => 'R$ '.number_format($preco, 2, ',', '.'),
                        ];
                    })->filter()->values();
                } else {
                    // Fallback: sem vínculos configurados, considera todos os ativos.
                    $servicosProf = $servicesAtivos->map(fn ($s) => [
                        'id' => $s->id,
                        'nome' => $s->name,
                        'duracao' => (int) $s->duration,
                        'duracao_label' => $this->formatarDuracao((int) $s->duration),
                        'preco' => (float) $s->price,
                        'preco_label' => 'R$ '.number_format((float) $s->price, 2, ',', '.'),
                    ])->values();
                }

                return [
                    'id' => $u->id,
                    'nome' => $u->professional_name ?: $u->name,
                    'cargo' => $u->cargo ?: ($u->role === 'owner' ? 'Proprietário' : 'Barbeiro'),
                    'avatar' => $u->avatar ? asset('storage/'.$u->avatar) : null,
                    'servicos' => $servicosProf,
                ];
            })->values();

        // Produtos do showcase público (todos os ativos)
        $produtos = Product::where('active', true)
            ->orderBy('name')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'nome' => $p->name,
                'descricao' => $p->description,
                'marca' => $p->brand,
                'preco' => (float) $p->price,
                'preco_label' => 'R$ '.number_format((float) $p->price, 2, ',', '.'),
                'imagem' => $p->image_path ? asset('storage/'.$p->image_path) : null,
            ])->values();

        // Indicadores
        $atendidos = Agendamento::where('user_id', $ownerId)->where('status', 'atendido');
        $clientesAtendidos = (clone $atendidos)->distinct('cliente_id')->count('cliente_id');
        $servicosExecutados = (clone $atendidos)->count();

        return response()->json([
            'nome_barbearia' => $agendaConfig->nome_barbearia,
            'logo' => $agendaConfig->getLogoUrl(),
            'descricao' => $agendaConfig->descricao,
            'telefone' => $agendaConfig->telefone,
            'endereco' => $agendaConfig->endereco,
            'horario_inicio' => $agendaConfig->horario_inicio,
            'horario_fim' => $agendaConfig->horario_fim,
            'intervalo_slots' => $agendaConfig->intervalo_slots,
            'imagens' => $imagens,
            'barbeiros' => $barbeiros,
            'servicos' => $servicos,
            'produtos' => $produtos,
            'indicadores' => [
                'clientes_atendidos' => $clientesAtendidos,
                'servicos_executados' => $servicosExecutados,
                'media_avaliacoes' => $servicosExecutados > 0 ? 4.8 : null,
            ],
        ]);
    }

    /**
     * Formata duração em minutos para texto legível (ex.: "1 hora e 20 minutos")
     */
    private function formatarDuracao(int $minutos): string
    {
        if ($minutos <= 0) {
            return '';
        }

        $horas = intdiv($minutos, 60);
        $restante = $minutos % 60;

        $partes = [];
        if ($horas > 0) {
            $partes[] = $horas.($horas === 1 ? ' hora' : ' horas');
        }
        if ($restante > 0) {
            $partes[] = $restante.($restante === 1 ? ' minuto' : ' minutos');
        }

        return implode(' e ', $partes);
    }

    /**
     * Submit a booking from public page.
     *
     * Fluxo tradicional (fallback do chat com IA). Usa exatamente o mesmo
     * backend de disponibilidade e criação transacional, garantindo regras
     * idênticas: duração real do serviço, ocupação, expediente e concorrência.
     */
    public function submitAgendamento(Request $request, string $publicToken): JsonResponse
    {
        $agendaConfig = AgendaConfig::forPublicIdentifier($publicToken)
            ->where('ativa', true)
            ->firstOrFail();

        $validated = $request->validate([
            'cliente_nome' => 'required|string|max:255',
            'cliente_email' => 'required|email',
            'cliente_telefone' => 'required|string|max:20',
            'barbeiro_id' => 'required|integer',
            'service_id' => 'nullable|integer',
            'servico' => 'nullable|string|max:255',
            'data_agendamento' => 'required|date',
            'hora_agendamento' => 'required|date_format:H:i',
            'observacoes' => 'nullable|string|max:500',
        ]);

        // Resolve serviço (por ID, preferencialmente; ou por nome como fallback).
        $service = null;
        if (! empty($validated['service_id'])) {
            $service = Service::where('active', true)->find($validated['service_id']);
        } elseif (! empty($validated['servico'])) {
            $service = Service::where('active', true)->where('name', $validated['servico'])->first();
        }
        if (! $service) {
            return response()->json([
                'success' => false,
                'message' => 'Serviço inválido ou indisponível.',
            ], 422);
        }

        // Profissional precisa estar no escopo (barbeiro/owner).
        $professional = User::whereIn('role', ['barber', 'owner'])->find($validated['barbeiro_id']);
        if (! $professional) {
            return response()->json([
                'success' => false,
                'message' => 'Profissional inválido.',
            ], 422);
        }

        // Monta o início no fuso oficial e valida disponibilidade real.
        try {
            $start = CarbonImmutable::createFromFormat(
                'Y-m-d H:i',
                $validated['data_agendamento'].' '.$validated['hora_agendamento'],
                $this->availability->timezone()
            );
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Data ou hora inválida.'], 422);
        }

        $slot = $this->availability->resolveSlot($agendaConfig, $service, $professional, $start);
        if (! $slot) {
            return response()->json([
                'success' => false,
                'message' => 'Esse horário não está disponível. Escolha outro horário.',
            ], 409);
        }

        try {
            $agendamento = $this->booking->create(
                $agendaConfig,
                $slot,
                [
                    'nome' => $validated['cliente_nome'],
                    'email' => $validated['cliente_email'],
                    'telefone' => $validated['cliente_telefone'],
                ],
                $validated['observacoes'] ?? null,
                'publico_tradicional',
            );
        } catch (SlotUnavailableException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Esse horário acabou de ser ocupado. Escolha outro horário.',
            ], 409);
        }

        return response()->json([
            'success' => true,
            'message' => 'Agendamento realizado com sucesso!',
            'agendamento_id' => $agendamento->id,
        ]);
    }

    /**
     * Profissionais aptos a um serviço (ou todos), para o montador do site.
     */
    public function professionals(Request $request, string $publicToken): JsonResponse
    {
        $config = AgendaConfig::forPublicIdentifier($publicToken)->where('ativa', true)->firstOrFail();

        $serviceId = $request->integer('service_id');
        $service = $serviceId ? Service::where('active', true)->find($serviceId) : null;

        $profs = $service
            ? $this->availability->professionalsForService($config, $service)
            : $this->availability->professionals($config);

        return response()->json([
            'profissionais' => $profs->map(fn ($u) => [
                'id' => $u->id,
                'nome' => $u->professional_name ?: $u->name,
            ])->values(),
        ]);
    }

    /**
     * Datas com disponibilidade real para serviço/profissional.
     */
    public function availableDates(Request $request, string $publicToken): JsonResponse
    {
        $config = AgendaConfig::forPublicIdentifier($publicToken)->where('ativa', true)->firstOrFail();

        $data = $request->validate([
            'service_id' => 'required|integer',
            'professional_id' => 'nullable|integer',
        ]);

        $service = Service::where('active', true)->find($data['service_id']);
        if (! $service) {
            return response()->json(['datas' => []]);
        }

        $professional = $this->resolvePublicProfessional($config, $service, $data['professional_id'] ?? null);
        if ($professional === false) {
            return response()->json(['datas' => []]);
        }

        $dates = $this->availability->availableDates($config, $service, $professional, 21);

        return response()->json([
            'datas' => array_map(fn (string $d) => [
                'data' => $d,
                'label' => $this->dateLabel($d),
            ], $dates),
        ]);
    }

    /**
     * Horários realmente disponíveis (ocupados já excluídos), cada um vinculado
     * a um profissional concreto quando "qualquer profissional".
     */
    public function availableTimes(Request $request, string $publicToken): JsonResponse
    {
        $config = AgendaConfig::forPublicIdentifier($publicToken)->where('ativa', true)->firstOrFail();

        $data = $request->validate([
            'service_id' => 'required|integer',
            'data' => 'required|date_format:Y-m-d',
            'professional_id' => 'nullable|integer',
        ]);

        $service = Service::where('active', true)->find($data['service_id']);
        if (! $service) {
            return response()->json(['horarios' => []]);
        }

        $professional = $this->resolvePublicProfessional($config, $service, $data['professional_id'] ?? null);
        if ($professional === false) {
            return response()->json(['horarios' => []]);
        }

        $date = \Carbon\CarbonImmutable::createFromFormat('Y-m-d', $data['data'], $this->availability->timezone())->startOfDay();
        // Grade completa (com ocupados) para exibir os horários cheios apenas
        // como referência, sem permitir seleção.
        $times = $this->availability->gridTimes($config, $service, $professional, $date);

        return response()->json([
            'horarios' => array_map(fn ($t) => [
                'time' => $t['time'],
                'disponivel' => $t['available'],
                'professional_id' => $t['professional_id'],
                'professional_nome' => $t['professional_name'],
            ], $times),
        ]);
    }

    /**
     * @return User|null|false false = inválido; null = qualquer profissional.
     */
    private function resolvePublicProfessional(AgendaConfig $config, Service $service, $professionalId)
    {
        if (empty($professionalId)) {
            return null;
        }

        $apt = $this->availability->professionalsForService($config, $service);
        $found = $apt->firstWhere('id', (int) $professionalId);

        return $found ?: false;
    }

    private function dateLabel(string $date): string
    {
        $d = \Carbon\CarbonImmutable::parse($date, $this->availability->timezone());
        $dias = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];

        return $dias[$d->dayOfWeek].', '.$d->format('d/m');
    }
}
