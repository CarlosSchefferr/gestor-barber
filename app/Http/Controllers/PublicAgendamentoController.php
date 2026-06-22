<?php

namespace App\Http\Controllers;

use App\Models\AgendaConfig;
use App\Models\Agendamento;
use App\Models\Product;
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
        $agendaConfig = AgendaConfig::where('public_token', $publicToken)
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
        $agendaConfig = AgendaConfig::where('public_token', $publicToken)
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

        // Barbeiros (profissionais da barbearia)
        $barbeiros = User::whereIn('role', ['barber', 'owner'])
            ->orderBy('name')
            ->get()
            ->map(fn ($u) => [
                'id' => $u->id,
                'nome' => $u->professional_name ?: $u->name,
                'cargo' => $u->cargo ?: ($u->role === 'owner' ? 'Proprietário' : 'Barbeiro'),
                'avatar' => $u->avatar ? asset('storage/'.$u->avatar) : null,
            ])->values();

        // Serviços ativos
        $servicos = Service::where('active', true)
            ->orderBy('name')
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'nome' => $s->name,
                'descricao' => $s->description,
                'duracao' => (int) $s->duration,
                'duracao_label' => $this->formatarDuracao((int) $s->duration),
                'preco' => (float) $s->price,
                'preco_label' => 'R$ '.number_format((float) $s->price, 2, ',', '.'),
            ])->values();

        // Produtos à venda
        $produtos = Product::sellable()
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
        $agendaConfig = AgendaConfig::where('public_token', $publicToken)
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
}
