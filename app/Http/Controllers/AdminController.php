<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Agendamento;
use App\Models\Cliente;
use App\Models\Service;
use App\Notifications\EmployeeInvitationNotification;
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
        // Apply filters
        $query = User::withCount(['agendamentos as agendamentos_count']);

        // Search by name
        if ($search = request('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        // Filter by role/cargo
        if ($cargo = request('cargo')) {
            $query->where('cargo', $cargo);
        }

        // Filter by status (active/inactive based on last 30 days)
        if ($status = request('status')) {
            if ($status === 'active') {
                $query->where(function ($q) {
                    $q->has('agendamentos')
                      ->orWhere('updated_at', '>=', now()->subDays(30));
                });
            } elseif ($status === 'inactive') {
                $query->doesntHave('agendamentos')
                      ->where('updated_at', '<', now()->subDays(30));
            }
        }

        $usuarios = $query->orderBy('created_at', 'desc')->paginate(20);

        // Prepare JS array for modal population
        $usuariosJs = $usuarios->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'cpf' => $user->cpf,
                'professional_name' => $user->professional_name,
                'gender' => $user->gender,
                'salary' => $user->salary,
                'cargo' => $user->cargo,
                'role' => $user->role,
                'avatar' => $user->avatar,
                'date_of_birth' => $user->date_of_birth,
            ];
        })->keyBy('id')->toArray();

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

        // Fetch available services
        $services = Service::where('active', true)->get();

        return view('admin.index', compact('usuarios', 'usuariosJs', 'estatisticas', 'services'));
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
            'cargo' => 'required|string|max:255',
            'professional_name' => 'nullable|string|max:255',
            'gender' => 'required|in:M,F,O',
            'cpf' => 'required|string|unique:users,cpf,' . $user->id,
            'date_of_birth' => 'required|date',
            'phone' => 'required|string|max:25',
            'salary' => 'nullable|numeric|min:0',
            'password' => 'nullable|min:8',
            'avatar' => 'nullable|image|max:2048',
        ]);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = $avatarPath;
        }

        $user->update($data);

        // Update schedule if provided
        if ($request->filled('schedule')) {
            $schedule = $request->input('schedule');
            $user->schedule()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'entry_time' => $schedule['entry_time'] ?? null,
                    'exit_time' => $schedule['exit_time'] ?? null,
                    'break_start' => $schedule['break_start'] ?? null,
                    'break_end' => $schedule['break_end'] ?? null,
                ]
            );
        }

        // Update professional services if provided
        if ($request->has('services')) {
            $user->professionalServices()->delete();
            foreach ($request->input('services', []) as $service) {
                $user->professionalServices()->create([
                    'service_id' => $service['service_id'],
                    'time_minutes' => $service['time_minutes'],
                    'price' => $service['price'],
                    'commission_percentage' => $service['commission_percentage'],
                ]);
            }
        }

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
        $services = Service::where('active', true)->get();
        return view('admin.create', compact('services'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'role' => 'required|in:owner,barber',
            'cargo' => 'required|string|max:255',
            'professional_name' => 'nullable|string|max:255',
            'gender' => 'required|in:M,F,O',
            'cpf' => 'required|string|unique:users,cpf',
            'date_of_birth' => 'required|date',
            'phone' => 'required|string|max:25',
            'password' => 'nullable|min:8',
            'salary' => 'nullable|numeric|min:0',
            'avatar' => 'nullable|image|max:2048',
        ]);

        // Generate provisional password if not provided
        $provisionalPassword = $data['password'] ?? $this->generateProvisionalPassword();
        $data['password'] = Hash::make($provisionalPassword);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public'); 
            $data['avatar'] = $avatarPath;
        }

        $user = User::create($data);

        // Create schedule (empty if not provided)
        $user->schedule()->create([
            'entry_time' => null,
            'exit_time' => null,
            'break_start' => null,
            'break_end' => null,
        ]);

        // Add professional services if provided
        if ($request->has('services')) {
            foreach ($request->input('services', []) as $service) {
                $user->professionalServices()->create([
                    'service_id' => $service['service_id'],
                    'time_minutes' => $service['time_minutes'],
                    'price' => $service['price'],
                    'commission_percentage' => $service['commission_percentage'],
                ]);
            }
        }

        // Send welcome email
        $user->notify(new EmployeeInvitationNotification($provisionalPassword));

        return redirect()->route('admin.index')
            ->with('success', 'Usuário criado com sucesso.')
            ->with('provisional_password', $provisionalPassword);
    }

    /**
     * Generate a random provisional password
     */
    private function generateProvisionalPassword(): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $password = '';
        for ($i = 0; $i < 12; $i++) {
            $password .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $password;
    }
}

