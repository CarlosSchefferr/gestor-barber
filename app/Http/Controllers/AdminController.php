<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Agendamento;
use App\Models\Cliente;
use App\Models\Service;
use App\Notifications\EmployeeInvitationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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
        $query = User::withCount(['agendamentos as agendamentos_count'])
            ->with([
                'schedule',
                'professionalServices.service:id,name,duration,price',
            ]);

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
            $schedule = $user->schedule;

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
                'schedule' => [
                    'entry_time' => $this->formatTimeForInput($schedule?->entry_time),
                    'exit_time' => $this->formatTimeForInput($schedule?->exit_time),
                    'break_start' => $this->formatTimeForInput($schedule?->break_start),
                    'break_end' => $this->formatTimeForInput($schedule?->break_end),
                ],
                'services' => $user->professionalServices->map(function ($professionalService) {
                    return [
                        'service_id' => $professionalService->service_id,
                        'name' => $professionalService->service?->name,
                        'time_minutes' => $professionalService->time_minutes,
                        'price' => (float) $professionalService->price,
                        'commission_percentage' => (float) $professionalService->commission_percentage,
                    ];
                })->values()->toArray(),
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
        $payload = $this->normalizePayload($request);
        $validator = validator(
            $payload,
            $this->validationRules($user),
            $this->validationMessages(),
            $this->validationAttributes()
        );
        $data = $validator->validate();

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

        DB::transaction(function () use ($request, $user, $data) {
            $user->update($this->extractUserData($data));

            if ($request->has('schedule')) {
                $schedule = $data['schedule'] ?? [];
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

            if ($request->boolean('services_submitted')) {
                $user->professionalServices()->delete();

                foreach (($data['services'] ?? []) as $service) {
                    $user->professionalServices()->create([
                        'service_id' => $service['service_id'],
                        'time_minutes' => $service['time_minutes'],
                        'price' => $service['price'],
                        'commission_percentage' => $service['commission_percentage'],
                    ]);
                }
            }
        });

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
        $payload = $this->normalizePayload($request);
        $validator = validator(
            $payload,
            $this->validationRules(),
            $this->validationMessages(),
            $this->validationAttributes()
        );
        $data = $validator->validate();

        // Generate provisional password if not provided
        $provisionalPassword = $data['password'] ?? $this->generateProvisionalPassword();
        $data['password'] = Hash::make($provisionalPassword);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public'); 
            $data['avatar'] = $avatarPath;
        }

        $user = DB::transaction(function () use ($request, $data) {
            $user = User::create($this->extractUserData($data));

            $schedule = $data['schedule'] ?? [];
            $user->schedule()->create([
                'entry_time' => $schedule['entry_time'] ?? null,
                'exit_time' => $schedule['exit_time'] ?? null,
                'break_start' => $schedule['break_start'] ?? null,
                'break_end' => $schedule['break_end'] ?? null,
            ]);

            if ($request->boolean('services_submitted')) {
                foreach (($data['services'] ?? []) as $service) {
                    $user->professionalServices()->create([
                        'service_id' => $service['service_id'],
                        'time_minutes' => $service['time_minutes'],
                        'price' => $service['price'],
                        'commission_percentage' => $service['commission_percentage'],
                    ]);
                }
            }

            return $user;
        });

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

    private function validationRules(?User $user = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            'role' => ['required', 'in:owner,barber'],
            'cargo' => ['required', 'string', 'max:255'],
            'professional_name' => ['nullable', 'string', 'max:255'],
            'gender' => ['required', 'in:M,F,O'],
            'cpf' => [
                'required',
                'string',
                Rule::unique('users', 'cpf')->ignore($user?->id),
            ],
            'date_of_birth' => ['required', 'date'],
            'phone' => ['required', 'string', 'max:25'],
            'password' => ['nullable', 'string', 'min:8'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'schedule' => ['nullable', 'array'],
            'schedule.entry_time' => ['nullable', 'date_format:H:i'],
            'schedule.exit_time' => ['nullable', 'date_format:H:i'],
            'schedule.break_start' => ['nullable', 'date_format:H:i'],
            'schedule.break_end' => ['nullable', 'date_format:H:i'],
            'services_submitted' => ['nullable', 'boolean'],
            'services' => ['nullable', 'array'],
            'services.*.service_id' => ['required_with:services', 'exists:services,id'],
            'services.*.time_minutes' => ['required_with:services', 'numeric', 'min:1'],
            'services.*.price' => ['required_with:services', 'numeric', 'min:0'],
            'services.*.commission_percentage' => ['required_with:services', 'numeric', 'between:0,100'],
        ];
    }

    private function validationMessages(): array
    {
        return [
            'email.unique' => 'Este e-mail já está cadastrado para outro usuário.',
            'cpf.unique' => 'Este CPF já está cadastrado para outro usuário.',
            'salary.numeric' => 'O salário deve ser informado em formato numérico válido.',
            'services.*.time_minutes.numeric' => 'Os minutos do serviço devem ser numéricos.',
            'services.*.price.numeric' => 'O valor do serviço deve ser numérico.',
            'services.*.commission_percentage.numeric' => 'A comissão do serviço deve ser numérica.',
            'services.*.service_id.exists' => 'Um dos serviços selecionados não existe mais. Atualize a página e tente novamente.',
            'services.*.time_minutes.min' => 'O tempo do serviço deve ser maior que zero.',
            'services.*.price.min' => 'O valor do serviço não pode ser negativo.',
            'services.*.commission_percentage.between' => 'A comissão do serviço deve estar entre 0% e 100%.',
        ];
    }

    private function validationAttributes(): array
    {
        return [
            'name' => 'nome',
            'email' => 'e-mail',
            'role' => 'nível de acesso',
            'cargo' => 'cargo',
            'professional_name' => 'nome profissional',
            'gender' => 'sexo',
            'cpf' => 'CPF',
            'date_of_birth' => 'data de nascimento',
            'phone' => 'telefone',
            'password' => 'senha',
            'salary' => 'salário',
            'avatar' => 'foto de perfil',
            'schedule.entry_time' => 'horário de entrada',
            'schedule.exit_time' => 'horário de saída',
            'schedule.break_start' => 'início do intervalo',
            'schedule.break_end' => 'fim do intervalo',
            'services.*.service_id' => 'serviço',
            'services.*.time_minutes' => 'minutos do serviço',
            'services.*.price' => 'valor do serviço',
            'services.*.commission_percentage' => 'comissão do serviço',
        ];
    }

    private function extractUserData(array $data): array
    {
        return collect($data)->only([
            'name',
            'email',
            'password',
            'role',
            'avatar',
            'date_of_birth',
            'phone',
            'cpf',
            'professional_name',
            'gender',
            'salary',
            'cargo',
        ])->toArray();
    }

    private function normalizePayload(Request $request): array
    {
        $payload = $request->all();
        $payload['salary'] = $this->normalizeNumericValue($request->input('salary'));

        $services = $request->input('services', []);
        $payload['services'] = collect(is_array($services) ? $services : [])
            ->map(function ($service) {
                return [
                    'service_id' => $service['service_id'] ?? null,
                    'time_minutes' => $this->normalizeNumericValue($service['time_minutes'] ?? null),
                    'price' => $this->normalizeNumericValue($service['price'] ?? null),
                    'commission_percentage' => $this->normalizeNumericValue($service['commission_percentage'] ?? null),
                ];
            })
            ->values()
            ->toArray();

        return $payload;
    }

    private function normalizeNumericValue(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return $value + 0;
        }

        if (!is_string($value)) {
            return $value;
        }

        $numeric = preg_replace('/[^\d,.-]/', '', trim($value));
        if ($numeric === '' || $numeric === null) {
            return null;
        }

        if (str_contains($numeric, ',')) {
            $numeric = str_replace('.', '', $numeric);
            $numeric = str_replace(',', '.', $numeric);
        } else {
            $numeric = str_replace(',', '', $numeric);
        }

        return is_numeric($numeric) ? (float) $numeric : $value;
    }

    private function formatTimeForInput(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('H:i');
        }

        if (!is_string($value)) {
            return null;
        }

        if (preg_match('/^\d{2}:\d{2}/', $value, $matches)) {
            return $matches[0];
        }

        return null;
    }
}
