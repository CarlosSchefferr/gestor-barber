<?php

namespace App\Http\Controllers;

use App\Models\AgendaConfig;
use App\Models\Agendamento;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class PublicAgendamentoController extends Controller
{
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

        $imagens = $agendaConfig->imagens->map(fn ($img) => [
            'id' => $img->id,
            'url' => asset('storage/' . $img->caminho_imagem),
        ])->toArray();

        $barbeiros = [$agendaConfig->user];
        $servicos = $agendaConfig->user->agendamentos()
            ->select('servico')
            ->distinct()
            ->whereNotNull('servico')
            ->pluck('servico')
            ->toArray();

        return response()->json([
            'nome_barbearia' => $agendaConfig->nome_barbearia,
            'descricao' => $agendaConfig->descricao,
            'telefone' => $agendaConfig->telefone,
            'endereco' => $agendaConfig->endereco,
            'horario_inicio' => $agendaConfig->horario_inicio,
            'horario_fim' => $agendaConfig->horario_fim,
            'intervalo_slots' => $agendaConfig->intervalo_slots,
            'imagens' => $imagens,
            'barbeiros' => collect($barbeiros)->map(fn ($u) => [
                'id' => $u->id,
                'nome' => $u->name,
            ])->toArray(),
            'servicos' => $servicos,
        ]);
    }

    /**
     * Submit a booking from public page
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
            'barbeiro_id' => 'required|integer|exists:users,id',
            'servico' => 'required|string|max:255',
            'data_agendamento' => 'required|date|after:today',
            'hora_agendamento' => 'required|date_format:H:i',
            'observacoes' => 'nullable|string',
        ]);

        // Find or create cliente
        $cliente = Cliente::where('email', $validated['cliente_email'])->first();
        if (!$cliente) {
            $cliente = Cliente::create([
                'nome' => $validated['cliente_nome'],
                'email' => $validated['cliente_email'],
                'telefone' => $validated['cliente_telefone'],
                'active' => true,
            ]);
        }

        // Create appointment
        $dataHora = \Carbon\Carbon::parse($validated['data_agendamento'] . ' ' . $validated['hora_agendamento']);
        $dataHoraFim = $dataHora->copy()->addMinutes($agendaConfig->intervalo_slots);

        $agendamento = Agendamento::create([
            'cliente_id' => $cliente->id,
            'barbeiro_id' => $validated['barbeiro_id'],
            'user_id' => $agendaConfig->user_id,
            'starts_at' => $dataHora,
            'ends_at' => $dataHoraFim,
            'servico' => $validated['servico'],
            'status' => 'agendado',
            'observacoes' => $validated['observacoes'] ?? null,
            'public_token' => (string) Str::uuid(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Agendamento realizado com sucesso!',
            'agendamento_id' => $agendamento->id,
        ]);
    }
}
