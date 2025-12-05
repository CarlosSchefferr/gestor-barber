<?php

namespace App\Http\Controllers;

use App\Models\Agendamento;
use App\Models\Cliente;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isOwner()) {
            // Dados completos para proprietários
            $total = Agendamento::count();
            $today = Agendamento::whereDate('starts_at', now()->toDateString())->count();
            $agendamentosHoje = Agendamento::with(['cliente', 'barbeiro'])->whereDate('starts_at', now()->toDateString())->orderBy('starts_at')->get();
            $clientesInativos = Cliente::where('last_appointment_at', '<', now()->subDays(30))->count();

            // Serviços realizados na última semana (nome do serviço, quantidade e receita)
            $oneWeekAgo = now()->subDays(6)->startOfDay();
            $servicosSemana = Agendamento::selectRaw("servico, COUNT(*) as quantidade, SUM(IFNULL(price,0)) as receita")
                ->whereBetween('starts_at', [$oneWeekAgo, now()->endOfDay()])
                ->groupBy('servico')
                ->orderByDesc('quantidade')
                ->get();

            // Agendamentos por dia para os últimos 7 dias
            $agendamentosPorDia = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i)->toDateString();
                $agendamentosPorDayCount = Agendamento::whereDate('starts_at', $date)->count();
                $agendamentosPorDia[] = ['date' => $date, 'count' => $agendamentosPorDayCount];
            }
        } else {
            // Dados limitados para barbeiros (apenas seus agendamentos)
            $total = Agendamento::where('barbeiro_id', $user->id)->count();
            $today = Agendamento::where('barbeiro_id', $user->id)->whereDate('starts_at', now()->toDateString())->count();
            $agendamentosHoje = Agendamento::with(['cliente', 'barbeiro'])->where('barbeiro_id', $user->id)->whereDate('starts_at', now()->toDateString())->orderBy('starts_at')->get();
            $clientesInativos = 0; // Barbeiros não veem clientes inativos

            // Serviços realizados na última semana (apenas do barbeiro)
            $oneWeekAgo = now()->subDays(6)->startOfDay();
            $servicosSemana = Agendamento::selectRaw("servico, COUNT(*) as quantidade, SUM(IFNULL(price,0)) as receita")
                ->where('barbeiro_id', $user->id)
                ->whereBetween('starts_at', [$oneWeekAgo, now()->endOfDay()])
                ->groupBy('servico')
                ->orderByDesc('quantidade')
                ->get();

            // Agendamentos por dia para os últimos 7 dias (apenas do barbeiro)
            $agendamentosPorDia = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i)->toDateString();
                $agendamentosPorDayCount = Agendamento::where('barbeiro_id', $user->id)->whereDate('starts_at', $date)->count();
                $agendamentosPorDia[] = ['date' => $date, 'count' => $agendamentosPorDayCount];
            }
        }

        // carregar metas visíveis para o usuário
        $user = auth()->user();
        $allMetas = \App\Models\Meta::orderBy('created_at', 'desc')->get();
        $metas = $allMetas->filter(function($meta) use ($user) {
            if ($meta->quem_tem_acesso === 'all') return true;
            if ($meta->quem_tem_acesso === 'current') return $meta->created_by == $user->id;
            if ($meta->quem_tem_acesso === 'barbers') return $user->isBarber();
            if ($meta->quem_tem_acesso === 'owners') return $user->isOwner();
            if ($meta->quem_tem_acesso === 'attendants') return $user->role === 'attendant';
            return false;
        })->values();

        // Recalcular valor_atual e percent para cada meta (mesma lógica do financeiro)
        foreach ($metas as $meta) {
            $valorAtual = 0;
            $mFrom = $meta->data_inicio ? $meta->data_inicio->format('Y-m-d') : null;
            $mTo = $meta->data_limite ? $meta->data_limite->format('Y-m-d') : null;

            if ($meta->tipo === 'reducao_despesas') {
                $q = \App\Models\Transacao::where('tipo', 'despesa');
                if ($mFrom && $mTo) {
                    $q->whereBetween('data', [$mFrom, $mTo]);
                }
                $valorAtual = $q->sum('valor');
            } elseif ($meta->tipo === 'aumentar_receita' || $meta->tipo === 'meta_mensal') {
                $receitas = 0;
                $tq = \App\Models\Transacao::where('tipo', 'receita');
                if ($mFrom && $mTo) {
                    $tq->whereBetween('data', [$mFrom, $mTo]);
                }
                $receitas += $tq->sum('valor');

                $aq = Agendamento::whereNotNull('price');
                if ($mFrom && $mTo) {
                    $aq->whereBetween('starts_at', [$mFrom, $mTo]);
                }
                $receitas += $aq->sum('price');

                $valorAtual = $receitas;
            } elseif ($meta->tipo === 'novos_clientes') {
                $cq = Cliente::query();
                if ($mFrom && $mTo) {
                    $cq->whereBetween('created_at', [$mFrom, $mTo]);
                }
                $valorAtual = $cq->count();
            } else {
                $valorAtual = $meta->valor_atual ?? 0;
            }

            $meta->valor_atual = $valorAtual;
            $meta->percent = ($meta->valor_meta > 0) ? min(100, ($valorAtual / $meta->valor_meta) * 100) : 0;
        }

        return view('dashboard', compact('total', 'today', 'clientesInativos', 'agendamentosHoje', 'servicosSemana', 'agendamentosPorDia', 'metas'));
    }
}
