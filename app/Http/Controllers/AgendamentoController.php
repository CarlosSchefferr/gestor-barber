<?php

namespace App\Http\Controllers;

use App\Models\Agendamento;
use App\Models\Cliente;
use App\Models\User;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgendamentoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = Agendamento::query()->with(['cliente', 'barbeiro']);

        // If the logged user is a barber, show only their agendamentos
        if (Auth::check() && Auth::user()->isBarber()) {
            $query->where('barbeiro_id', Auth::id());
        }

        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }

        if ($request->filled('barbeiro_id')) {
            $query->where('barbeiro_id', $request->barbeiro_id);
        }

        if ($request->filled('from')) {
            $query->where('starts_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->where('starts_at', '<=', $request->to);
        }

        $agendamentos = $query->orderBy('starts_at')->paginate(20)->withQueryString();

        // Para o calendário, usar a mesma query (aplicando os filtros) e gerar eventos
        $calendarAgendamentos = (clone $query)->orderBy('starts_at')->get();
        $calendarEvents = $calendarAgendamentos->map(function($a) {
            return [
                'id' => $a->id,
                'title' => ($a->cliente? $a->cliente->nome : '') . ' - ' . ($a->servico ?? ''),
                'start' => $a->starts_at ? $a->starts_at->toIso8601String() : null,
                'end' => $a->ends_at ? $a->ends_at->toIso8601String() : null,
                'backgroundColor' => $a->color ?? '#3b82f6',
                'borderColor' => $a->color ?? '#3b82f6',
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'barbeiro' => $a->barbeiro? $a->barbeiro->name : '',
                    'barbeiro_name' => $a->barbeiro? $a->barbeiro->name : '',
                    'cliente_name' => $a->cliente? $a->cliente->nome : '',
                    'cliente_phone' => $a->cliente? ($a->cliente->telefone ?? '') : '',
                    'servico' => $a->servico ?? '',
                    'price' => $a->price ?? null,
                    'observacoes' => $a->observacoes ?? '',
                    'color' => $a->color ?? null,
                    'starts_at' => $a->starts_at ? $a->starts_at->toIso8601String() : null,
                    'ends_at' => $a->ends_at ? $a->ends_at->toIso8601String() : null,
                ],
            ];
        })->toArray();

        $clientes = Cliente::orderBy('nome')->get();
        $barbeiros = User::orderBy('name')->get();
        $services = Service::orderBy('name')->get();

        return view('agendamentos.index', compact('agendamentos', 'clientes', 'barbeiros', 'calendarAgendamentos', 'calendarEvents', 'services'));
    }

    public function create()
    {
        $clientes = Cliente::orderBy('nome')->get();
        $barbeiros = User::orderBy('name')->get();
        $services = Service::orderBy('name')->get();
        return view('agendamentos.create', compact('clientes', 'barbeiros', 'services'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'barbeiro_id' => 'required|exists:users,id',
            'starts_at' => 'required|date',
            'ends_at' => 'nullable|date',
            'servico' => 'nullable|string|max:255',
            'color' => ['nullable','regex:/^#[0-9A-Fa-f]{6}$/'],
            'price' => 'nullable|numeric',
            'observacoes' => 'nullable|string',
        ]);

    $data['user_id'] = Auth::id();

        Agendamento::create($data);

        return redirect()->route('agendamentos.index')->with('success', 'Agendamento criado.');
    }

    public function edit(Agendamento $agendamento)
    {
        // Barbeiros só podem editar seus próprios agendamentos
        if (Auth::user()->isBarber() && $agendamento->barbeiro_id !== Auth::id()) {
            abort(403, 'Você só pode editar seus próprios agendamentos.');
        }

        $clientes = Cliente::orderBy('nome')->get();
        $barbeiros = User::orderBy('name')->get();
        $services = Service::orderBy('name')->get();
        return view('agendamentos.edit', compact('agendamento', 'clientes', 'barbeiros', 'services'));
    }

    public function update(Request $request, Agendamento $agendamento)
    {
        // Barbeiros só podem atualizar seus próprios agendamentos
        if (Auth::user()->isBarber() && $agendamento->barbeiro_id !== Auth::id()) {
            abort(403, 'Você só pode atualizar seus próprios agendamentos.');
        }

        $data = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'barbeiro_id' => 'required|exists:users,id',
            'starts_at' => 'required|date',
            'ends_at' => 'nullable|date',
            'servico' => 'nullable|string|max:255',
            'color' => ['nullable','regex:/^#[0-9A-Fa-f]{6}$/'],
            'price' => 'nullable|numeric',
            'observacoes' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        $agendamento->update($data);

        return redirect()->route('agendamentos.index')->with('success', 'Agendamento atualizado.');
    }

    public function destroy(Agendamento $agendamento)
    {
        // Barbeiros só podem deletar seus próprios agendamentos
        if (Auth::user()->isBarber() && $agendamento->barbeiro_id !== Auth::id()) {
            abort(403, 'Você só pode deletar seus próprios agendamentos.');
        }

        $agendamento->delete();
        return redirect()->route('agendamentos.index')->with('success', 'Agendamento removido.');
    }
}
