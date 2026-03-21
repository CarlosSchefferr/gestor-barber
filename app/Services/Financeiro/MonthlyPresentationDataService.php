<?php

namespace App\Services\Financeiro;

use App\Models\Agendamento;
use App\Models\Cliente;
use App\Models\Meta;
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

        $atendimentosAnterior = Agendamento::query()
            ->whereBetween('starts_at', [$previousStart, $previousEnd])
            ->count();

        $ticketMedio = $atendimentos > 0 ? $faturamentoAtual / $atendimentos : 0.0;
        $ticketMedioAnterior = $atendimentosAnterior > 0 ? $faturamentoAnterior / $atendimentosAnterior : 0.0;

        $servicos = $this->buildServicesBreakdown($monthStart, $monthEnd);
        $servicoMaisVendido = $servicos->first();

        $barbeiroDestaque = $this->findTopBarber($monthStart, $monthEnd);
        $rankingBarbeiros = $this->buildBarbersRanking($monthStart, $monthEnd);

        $evolucaoPercentual = $this->calculateGrowth($faturamentoAtual, $faturamentoAnterior);
        $evolucaoAtendimentos = $this->calculateGrowth($atendimentos, $atendimentosAnterior);

        $despesas = $this->sumExpenses($monthStart, $monthEnd);
        $despesasAnterior = $this->sumExpenses($previousStart, $previousEnd);
        $lucroLiquido = $faturamentoAtual - $despesas;

        $metas = $this->buildMetasData($monthStart, $monthEnd);
        $novosClientes = $this->countNewClients($monthStart, $monthEnd);
        $novosClientesAnterior = $this->countNewClients($previousStart, $previousEnd);
        $clientesAtivos = $this->countActiveClients($monthStart, $monthEnd);

        $diaPico = $this->findPeakDay($monthStart, $monthEnd);
        $horaPico = $this->findPeakHour($monthStart, $monthEnd);

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
            'quantidade_atendimentos_anterior' => $atendimentosAnterior,
            'ticket_medio' => round($ticketMedio, 2),
            'ticket_medio_anterior' => round($ticketMedioAnterior, 2),
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
            'ranking_barbeiros' => $rankingBarbeiros,
            'evolucao_percentual' => round($evolucaoPercentual, 2),
            'evolucao_atendimentos' => round($evolucaoAtendimentos, 2),
            'lista_servicos' => $servicos->values()->all(),
            'despesas' => round($despesas, 2),
            'despesas_anterior' => round($despesasAnterior, 2),
            'lucro_liquido' => round($lucroLiquido, 2),
            'metas' => $metas,
            'novos_clientes' => $novosClientes,
            'novos_clientes_anterior' => $novosClientesAnterior,
            'clientes_ativos' => $clientesAtivos,
            'dia_pico' => $diaPico,
            'hora_pico' => $horaPico,
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

    private function sumExpenses(Carbon $from, Carbon $to): float
    {
        return (float) Transacao::query()
            ->whereBetween('data', [$from->toDateString(), $to->toDateString()])
            ->where('tipo', 'despesa')
            ->sum('valor');
    }

    private function buildBarbersRanking(Carbon $from, Carbon $to): array
    {
        return Agendamento::query()
            ->select(
                'barbeiro_id',
                DB::raw('COUNT(*) as atendimentos'),
                DB::raw('SUM(COALESCE(price, 0)) as faturamento')
            )
            ->whereBetween('starts_at', [$from, $to])
            ->whereNotNull('barbeiro_id')
            ->groupBy('barbeiro_id')
            ->orderByDesc('faturamento')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                $barberName = User::query()->where('id', $item->barbeiro_id)->value('name') ?? 'Barbeiro';
                return [
                    'id' => (int) $item->barbeiro_id,
                    'nome' => $barberName,
                    'atendimentos' => (int) $item->atendimentos,
                    'faturamento' => round((float) $item->faturamento, 2),
                ];
            })
            ->all();
    }

    private function buildMetasData(Carbon $from, Carbon $to): array
    {
        $metas = Meta::query()
            ->where(function ($query) use ($from, $to) {
                $query->whereBetween('data_limite', [$from, $to])
                    ->orWhere(function ($q) use ($from, $to) {
                        $q->where('data_inicio', '<=', $to)
                            ->where('data_limite', '>=', $from);
                    });
            })
            ->get();

        $concluidas = 0;
        $emAndamento = 0;
        $pendentes = 0;
        $totalMetas = $metas->count();
        $metasList = [];

        foreach ($metas as $meta) {
            $percent = $meta->valor_meta > 0
                ? min(100, ($meta->valor_atual / $meta->valor_meta) * 100)
                : 0;

            if ($percent >= 100) {
                $concluidas++;
                $status = 'concluida';
            } elseif ($percent > 0) {
                $emAndamento++;
                $status = 'em_andamento';
            } else {
                $pendentes++;
                $status = 'pendente';
            }

            $metasList[] = [
                'nome' => $meta->nome,
                'tipo' => $meta->tipo,
                'valor_meta' => (float) $meta->valor_meta,
                'valor_atual' => (float) $meta->valor_atual,
                'percent' => round($percent, 1),
                'status' => $status,
            ];
        }

        return [
            'total' => $totalMetas,
            'concluidas' => $concluidas,
            'em_andamento' => $emAndamento,
            'pendentes' => $pendentes,
            'taxa_conclusao' => $totalMetas > 0 ? round(($concluidas / $totalMetas) * 100, 1) : 0,
            'lista' => $metasList,
        ];
    }

    private function countNewClients(Carbon $from, Carbon $to): int
    {
        return Cliente::query()
            ->whereBetween('created_at', [$from, $to])
            ->count();
    }

    private function countActiveClients(Carbon $from, Carbon $to): int
    {
        return Cliente::query()
            ->whereHas('agendamentos', function ($query) use ($from, $to) {
                $query->whereBetween('starts_at', [$from, $to]);
            })
            ->count();
    }

    private function findPeakDay(Carbon $from, Carbon $to): array
    {
        $dias = ['Domingo', 'Segunda', 'Terca', 'Quarta', 'Quinta', 'Sexta', 'Sabado'];

        $result = Agendamento::query()
            ->selectRaw('DAYOFWEEK(starts_at) as dia, COUNT(*) as total')
            ->whereBetween('starts_at', [$from, $to])
            ->groupBy('dia')
            ->orderByDesc('total')
            ->first();

        if (!$result) {
            return ['nome' => 'Sem dados', 'total' => 0];
        }

        $diaIndex = ((int) $result->dia) - 1;
        return [
            'nome' => $dias[$diaIndex] ?? 'Desconhecido',
            'total' => (int) $result->total,
        ];
    }

    private function findPeakHour(Carbon $from, Carbon $to): array
    {
        $result = Agendamento::query()
            ->selectRaw('HOUR(starts_at) as hora, COUNT(*) as total')
            ->whereBetween('starts_at', [$from, $to])
            ->groupBy('hora')
            ->orderByDesc('total')
            ->first();

        if (!$result) {
            return ['hora' => 'Sem dados', 'total' => 0];
        }

        $hora = (int) $result->hora;
        return [
            'hora' => sprintf('%02d:00', $hora),
            'total' => (int) $result->total,
        ];
    }
}
