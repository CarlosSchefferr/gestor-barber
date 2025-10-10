<?php

namespace App\Http\Controllers;

use App\Models\Agendamento;
use App\Models\Cliente;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
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

        return view('dashboard', compact('total', 'today', 'clientesInativos', 'agendamentosHoje', 'servicosSemana', 'agendamentosPorDia'));
    }
}
