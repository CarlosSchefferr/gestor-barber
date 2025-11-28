<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Agendamento;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (Auth::user()->role !== 'owner') {
                abort(403, 'Acesso negado. Apenas proprietários podem acessar esta área.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $usuarios = User::withCount(['agendamentos as agendamentos_count'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $estatisticas = [
            'total_usuarios' => User::count(),
            'total_barbeiros' => User::where('role', 'barber')->count(),
            'total_owners' => User::where('role', 'owner')->count(),
            'usuarios_ativos' => User::whereHas('agendamentos', function($query) {
                $query->where('starts_at', '>=', now()->subDays(30));
            })->count(),
            'total_agendamentos' => Agendamento::count(),
            'total_clientes' => Cliente::count(),
        ];

        return view('admin.index', compact('usuarios', 'estatisticas'));
    }

    public function show(User $user)
    {
        $agendamentos = Agendamento::where('barbeiro_id', $user->id)
            ->with(['cliente'])
            ->orderBy('starts_at', 'desc')
            ->paginate(10);

        $estatisticasUsuario = [
            'total_agendamentos' => Agendamento::where('barbeiro_id', $user->id)->count(),
            'agendamentos_hoje' => Agendamento::where('barbeiro_id', $user->id)
                ->whereDate('starts_at', now()->toDateString())
                ->count(),
            'receita_total' => Agendamento::where('barbeiro_id', $user->id)
                ->whereNotNull('price')
                ->sum('price'),
            'ultimo_agendamento' => Agendamento::where('barbeiro_id', $user->id)
                ->orderBy('starts_at', 'desc')
                ->first(),
        ];

        // indicadores dinâmicos para filtros (hoje, semanal, mensal)
        $now = \Carbon\Carbon::now();

        // helper para calcular indicadores em um intervalo
        $calcIndicators = function ($start, $end) use ($user) {
            // slots: assumimos expediente de 08:00-18:00 e intervalos de 30 minutos
            $slotsPerDay = ((18 - 8) * 60) / 30; // 20

            $days = \Carbon\CarbonPeriod::create($start->startOfDay(), $end->endOfDay());
            $workDays = 0;
            foreach ($days as $d) {
                // contar apenas seg-sex (1..5)
                if (in_array($d->dayOfWeekIso, [1,2,3,4,5])) {
                    $workDays++;
                }
            }

            $totalSlots = max(0, $workDays * $slotsPerDay);

            $scheduledQuery = Agendamento::where('barbeiro_id', $user->id)
                ->whereBetween('starts_at', [$start->startOfDay(), $end->endOfDay()]);

            $scheduled = (int) $scheduledQuery->count();
            $completed = (int) $scheduledQuery->where('status', 'concluido')->count();
            $totalRevenue = (float) $scheduledQuery->whereNotNull('price')->sum('price');

            // comissão: assumimos 30% por padrão
            $commission = $totalRevenue * 0.30;

            $vacant = max(0, $totalSlots - $scheduled);

            return [
                'vacant_slots' => $vacant,
                'scheduled' => $scheduled,
                'completed' => $completed,
                'commission' => $commission,
            ];
        };

        $startToday = $now->copy();
        $endToday = $now->copy();

        // semana atual: segunda a sexta
        $startWeek = $now->copy()->startOfWeek(1);
        $endWeek = $now->copy()->startOfWeek(1)->addDays(4);

        // mês atual
        $startMonth = $now->copy()->startOfMonth();
        $endMonth = $now->copy()->endOfMonth();

        $indicators = [
            'today' => $calcIndicators($startToday, $endToday),
            'week' => $calcIndicators($startWeek, $endWeek),
            'month' => $calcIndicators($startMonth, $endMonth),
        ];

        return view('admin.show', compact('user', 'agendamentos', 'estatisticasUsuario', 'indicators'));
    }

    public function edit(User $user)
    {
        return view('admin.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:owner,barber',
            'password' => 'nullable|min:8|confirmed',
            'date_of_birth' => 'nullable|date',
            'phone' => 'nullable|string|max:25',
        ]);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('admin.index')->with('success', 'Usuário atualizado com sucesso.');
    }

    public function destroy(User $user)
    {
        // Não permitir deletar o próprio usuário
        if ($user->id === Auth::id()) {
            return redirect()->route('admin.index')->with('error', 'Você não pode deletar sua própria conta.');
        }

        // Não permitir deletar o último owner
        if ($user->role === 'owner' && User::where('role', 'owner')->count() <= 1) {
            return redirect()->route('admin.index')->with('error', 'Não é possível deletar o último proprietário.');
        }

        $user->delete();

        return redirect()->route('admin.index')->with('success', 'Usuário removido com sucesso.');
    }

    public function create()
    {
        return view('admin.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'role' => 'required|in:owner,barber',
            'password' => 'required|min:8|confirmed',
            'date_of_birth' => 'required|date',
            'phone' => 'required|string|max:25',
        ]);

        $data['password'] = Hash::make($data['password']);

        User::create($data);

        return redirect()->route('admin.index')->with('success', 'Usuário criado com sucesso.');
    }
}
