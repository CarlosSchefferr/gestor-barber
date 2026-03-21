<?php

namespace App\Services\Financeiro;

use App\Models\Agendamento;
use App\Models\Transacao;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MonthlyPresentationDataService
{
    public function build(?Carbon $referenceDate = null): array
    {
        $referenceDate = ($referenceDate ?? now())->copy()->startOfMonth();

        $monthStart = $referenceDate->copy()->startOfMonth();
        $monthEnd = $referenceDate->copy()->endOfMonth();

        $previousStart = $referenceDate->copy()->subMonth()->startOfMonth();
        $previousEnd = $referenceDate->copy()->subMonth()->endOfMonth();

        $faturamentoAtual = $this->sumRevenue($monthStart, $monthEnd);
        $faturamentoAnterior = $this->sumRevenue($previousStart, $previousEnd);

        $atendimentos = Agendamento::query()
            ->whereBetween('starts_at', [$monthStart, $monthEnd])
            ->count();

        $ticketMedio = $atendimentos > 0 ? $faturamentoAtual / $atendimentos : 0.0;

        $servicos = $this->buildServicesBreakdown($monthStart, $monthEnd);
        $servicoMaisVendido = $servicos->first();

        $barbeiroDestaque = $this->findTopBarber($monthStart, $monthEnd);

        $evolucaoPercentual = $this->calculateGrowth($faturamentoAtual, $faturamentoAnterior);

        $periodo = [
            'mes' => (int) $referenceDate->month,
            'ano' => (int) $referenceDate->year,
            'mes_ano' => ucfirst($referenceDate->locale('pt_BR')->translatedFormat('F / Y')),
            'mes_ano_anterior' => ucfirst($referenceDate->copy()->subMonth()->locale('pt_BR')->translatedFormat('F / Y')),
            'inicio' => $monthStart->toDateString(),
            'fim' => $monthEnd->toDateString(),
        ];

        return [
            'periodo' => $periodo,
            'faturamento_total' => round($faturamentoAtual, 2),
            'faturamento_mes_anterior' => round($faturamentoAnterior, 2),
            'quantidade_atendimentos' => $atendimentos,
            'ticket_medio' => round($ticketMedio, 2),
            'servico_mais_vendido' => [
                'nome' => $servicoMaisVendido['nome'] ?? 'Sem dados no periodo',
                'quantidade' => $servicoMaisVendido['quantidade'] ?? 0,
                'valor_total' => $servicoMaisVendido['valor_total'] ?? 0,
            ],
            'barbeiro_destaque' => [
                'id' => $barbeiroDestaque['id'] ?? null,
                'nome' => $barbeiroDestaque['nome'] ?? 'Sem dados no periodo',
                'faturamento' => $barbeiroDestaque['faturamento'] ?? 0,
            ],
            'evolucao_percentual' => round($evolucaoPercentual, 2),
            'lista_servicos' => $servicos->values()->all(),
        ];
    }

    private function sumRevenue(Carbon $from, Carbon $to): float
    {
        $agendamentos = (float) Agendamento::query()
            ->whereBetween('starts_at', [$from, $to])
            ->whereNotNull('price')
            ->sum('price');

        $transacoes = (float) Transacao::query()
            ->whereBetween('data', [$from->toDateString(), $to->toDateString()])
            ->where('tipo', 'receita')
            ->sum('valor');

        return $agendamentos + $transacoes;
    }

    private function buildServicesBreakdown(Carbon $from, Carbon $to): Collection
    {
        return Agendamento::query()
            ->select(
                'servico',
                DB::raw('COUNT(*) as quantidade'),
                DB::raw('SUM(COALESCE(price, 0)) as valor_total')
            )
            ->whereBetween('starts_at', [$from, $to])
            ->whereNotNull('servico')
            ->where('servico', '!=', '')
            ->groupBy('servico')
            ->orderByDesc('quantidade')
            ->orderByDesc('valor_total')
            ->get()
            ->map(function ($item) {
                return [
                    'nome' => $item->servico,
                    'quantidade' => (int) $item->quantidade,
                    'valor_total' => round((float) $item->valor_total, 2),
                ];
            });
    }

    private function findTopBarber(Carbon $from, Carbon $to): ?array
    {
        $row = Agendamento::query()
            ->select('barbeiro_id', DB::raw('SUM(COALESCE(price, 0)) as faturamento'))
            ->whereBetween('starts_at', [$from, $to])
            ->whereNotNull('barbeiro_id')
            ->groupBy('barbeiro_id')
            ->orderByDesc('faturamento')
            ->first();

        if (!$row) {
            return null;
        }

        $barberName = User::query()->where('id', $row->barbeiro_id)->value('name') ?? 'Barbeiro';

        return [
            'id' => (int) $row->barbeiro_id,
            'nome' => $barberName,
            'faturamento' => round((float) $row->faturamento, 2),
        ];
    }

    private function calculateGrowth(float $current, float $previous): float
    {
        if ($previous <= 0.0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return (($current - $previous) / $previous) * 100;
    }
}
