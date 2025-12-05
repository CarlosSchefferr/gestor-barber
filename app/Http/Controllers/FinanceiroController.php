<?php

namespace App\Http\Controllers;

use App\Models\Agendamento;
use App\Models\Cliente;
use App\Models\Transacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceiroController extends Controller
{
    public function index(Request $request)
    {
        // Filtros de período
        $period = $request->get('period', 'month');
        $from = $request->get('from');
        $to = $request->get('to');
        $type = $request->get('type');

        // Definir período baseado no filtro
        if ($period === 'today') {
            $from = now()->startOfDay();
            $to = now()->endOfDay();
        } elseif ($period === 'week') {
            $from = now()->startOfWeek();
            $to = now()->endOfWeek();
        } elseif ($period === 'month') {
            $from = now()->startOfMonth();
            $to = now()->endOfMonth();
        } elseif ($period === 'quarter') {
            $from = now()->startOfQuarter();
            $to = now()->endOfQuarter();
        } elseif ($period === 'year') {
            $from = now()->startOfYear();
            $to = now()->endOfYear();
        } elseif ($period === 'custom' && $from && $to) {
            $from = \Carbon\Carbon::parse($from)->startOfDay();
            $to = \Carbon\Carbon::parse($to)->endOfDay();
        } else {
            $from = now()->startOfMonth();
            $to = now()->endOfMonth();
        }

        // Calcular receita total (agendamentos com preço) + transacoes do tipo receita
        $receitaAgendamentos = Agendamento::whereBetween('starts_at', [$from, $to])
            ->whereNotNull('price')
            ->sum('price');

        $receitaTransacoes = Transacao::whereBetween('data', [$from, $to])
            ->where('tipo', 'receita')
            ->sum('valor');

        $receitaTotal = $receitaAgendamentos + $receitaTransacoes;

        // Calcular despesas reais a partir da tabela transacoes
        $despesas = Transacao::whereBetween('data', [$from, $to])
            ->where('tipo', 'despesa')
            ->sum('valor');

        // Lucro líquido
        $lucroLiquido = $receitaTotal - $despesas;

        // Meta do mês: procurar por uma meta do tipo 'meta_mensal' válida para o período
        $metaMensal = \App\Models\Meta::where('tipo', 'meta_mensal')
            ->where(function ($q) use ($from, $to) {
                $q->whereNull('data_inicio')->orWhere('data_inicio', '<=', $to);
            })
            ->where(function ($q) use ($from, $to) {
                $q->whereNull('data_limite')->orWhere('data_limite', '>=', $from);
            })
            ->orderBy('created_at', 'desc')
            ->first();

        if ($metaMensal) {
            $metaMes = $metaMensal->valor_meta;
        } else {
            $metaMes = 0; // fallback
        }

        $progressoMeta = $metaMes > 0 ? min(100, ($receitaTotal / $metaMes) * 100) : 0;

        // Metas de crescimento (persistidas) — recalcular valor_atual dinamicamente
        $metas = \App\Models\Meta::orderBy('created_at', 'desc')->get();
        foreach ($metas as $meta) {
            $valorAtual = 0;
            $mFrom = $meta->data_inicio ? $meta->data_inicio->format('Y-m-d') : null;
            $mTo = $meta->data_limite ? $meta->data_limite->format('Y-m-d') : null;

            if ($meta->tipo === 'reducao_despesas') {
                $q = Transacao::where('tipo', 'despesa');
                if ($mFrom && $mTo) {
                    $q->whereBetween('data', [$mFrom, $mTo]);
                }
                $valorAtual = $q->sum('valor');
            } elseif ($meta->tipo === 'aumentar_receita' || $meta->tipo === 'meta_mensal') {
                $receitas = 0;
                $tq = Transacao::where('tipo', 'receita');
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
                $cq = \App\Models\Cliente::query();
                if ($mFrom && $mTo) {
                    $cq->whereBetween('created_at', [$mFrom, $mTo]);
                }
                $valorAtual = $cq->count();
            } else {
                // fallback para outros tipos — usar valor_atual salvo
                $valorAtual = $meta->valor_atual ?? 0;
            }

            $meta->valor_atual = $valorAtual;
            // calcular percentual (evitar divisão por zero)
            $meta->percent = ($meta->valor_meta > 0) ? min(100, ($valorAtual / $meta->valor_meta) * 100) : 0;
        }

        // Contagem para gráfico de metas: concluído >=100%, em andamento 1-99%, pendente 0
        $concluido = $metas->where('percent', '>=', 100)->count();
        $andamento = $metas->where('percent', '>', 0)->where('percent', '<', 100)->count();
        $pendente = $metas->where('percent', '<=', 0)->count();
        $metaStatusCounts = [$concluido, $andamento, $pendente];

        // Transações recentes: combine transacoes da tabela com agendamentos
        $transacoesModel = Transacao::whereBetween('data', [$from, $to])
            ->orderBy('data', 'desc')
            ->get()
            ->map(function ($t) {
                return (object) [
                    'data' => $t->data,
                    'descricao' => $t->descricao,
                    'tipo' => $t->tipo,
                    'valor' => $t->valor,
                    'status' => $t->status,
                ];
            });

        $transacoesAgend = Agendamento::with(['cliente', 'barbeiro'])
            ->whereBetween('starts_at', [$from, $to])
            ->whereNotNull('price')
            ->orderBy('starts_at', 'desc')
            ->get()
            ->map(function ($agendamento) {
                return (object) [
                    'data' => $agendamento->starts_at,
                    'descricao' => $agendamento->servico ?: 'Serviço - ' . $agendamento->cliente->nome,
                    'tipo' => 'receita',
                    'valor' => $agendamento->price,
                    'status' => 'Confirmado'
                ];
            });

        // Merge and sort by date desc, then limit 10
        $transacoes = $transacoesModel->merge($transacoesAgend)
            ->sortByDesc(function ($t) {
                return strtotime($t->data);
            })->values()->slice(0, 10);

        // Dados para gráficos
        $receitaPorMes = [];
        for ($i = 5; $i >= 0; $i--) {
            $mes = now()->subMonths($i);
            $receitaMes = Agendamento::whereYear('starts_at', $mes->year)
                ->whereMonth('starts_at', $mes->month)
                ->whereNotNull('price')
                ->sum('price');

            $receitaPorMes[] = [
                'mes' => $mes->format('M'),
                'receita' => $receitaMes
            ];
        }

        return view('financeiro.index', compact(
            'receitaTotal',
            'despesas',
            'lucroLiquido',
            'metaMes',
            'progressoMeta',
            'metas',
            'transacoes',
            'receitaPorMes',
            'from',
            'to',
            'period',
            'type'
            ,'metaStatusCounts'
        ));
    }
}
