<?php

namespace App\Http\Controllers;

use App\Models\Agendamento;
use App\Models\Cliente;
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

        // Calcular receita total (agendamentos com preço)
        $receitaTotal = Agendamento::whereBetween('starts_at', [$from, $to])
            ->whereNotNull('price')
            ->sum('price');

        // Calcular despesas (simulado - você pode criar uma tabela de despesas)
        $despesas = 0; // Por enquanto sem despesas reais

        // Lucro líquido
        $lucroLiquido = $receitaTotal - $despesas;

        // Meta do mês (simulada)
        $metaMes = 30000; // Meta de R$ 30.000
        $progressoMeta = $metaMes > 0 ? min(100, ($receitaTotal / $metaMes) * 100) : 0;

        // Metas de crescimento (dados simulados - você pode criar uma tabela de metas)
        $metas = [
            (object) [
                'nome' => 'Aumentar Receita em 20%',
                'descricao' => 'Meta de crescimento para este trimestre',
                'valor_atual' => $receitaTotal,
                'valor_meta' => 30000,
                'data_limite' => now()->addMonths(3)->format('Y-m-d')
            ],
            (object) [
                'nome' => 'Reduzir Despesas',
                'descricao' => 'Diminuir custos operacionais em 15%',
                'valor_atual' => 3200,
                'valor_meta' => 5000,
                'data_limite' => now()->addMonth()->format('Y-m-d')
            ],
            (object) [
                'nome' => 'Novos Clientes',
                'descricao' => 'Atingir 50 novos clientes este mês',
                'valor_atual' => Cliente::whereBetween('created_at', [$from, $to])->count(),
                'valor_meta' => 50,
                'data_limite' => now()->endOfMonth()->format('Y-m-d')
            ]
        ];

        // Transações recentes (agendamentos como receitas)
        $transacoes = Agendamento::with(['cliente', 'barbeiro'])
            ->whereBetween('starts_at', [$from, $to])
            ->whereNotNull('price')
            ->orderBy('starts_at', 'desc')
            ->limit(10)
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
        ));
    }
}
