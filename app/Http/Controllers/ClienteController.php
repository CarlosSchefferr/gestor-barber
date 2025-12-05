<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $request = request();

        $search = $request->get('search');
        $sort = $request->get('sort', 'nome');
        $statusFilter = $request->get('status');
        $days = $request->get('days', 30);

        $query = Cliente::query();

        // Only active clients by default
        $query->when($statusFilter == 'active', function ($q) { $q->where('active', true); });
        $query->when($statusFilter == 'inactive', function ($q) { $q->where('active', false); });

        if ($search) {
            $query->where('nome', 'like', "%{$search}%");
        }

        // If user is a barber, only show clients they have attended
        if (auth()->check() && auth()->user()->isBarber()) {
            $query->whereHas('agendamentos', function ($q) {
                $q->where('barbeiro_id', auth()->id());
            });
        }

        // Sorting
        if ($sort === 'created_at') {
            $query->orderBy('created_at', 'desc');
        } elseif ($sort === 'last_appointment_at') {
            $query->orderBy('last_appointment_at', 'desc');
        } else {
            $query->orderBy('nome');
        }

        $clientes = $query->paginate(20)->withQueryString();

        // Indicators (apply same barber filter where relevant)
        $clientQueryForIndicators = Cliente::query();
        if (auth()->check() && auth()->user()->isBarber()) {
            $clientQueryForIndicators->whereHas('agendamentos', function ($q) {
                $q->where('barbeiro_id', auth()->id());
            });
        }

        // 1) clients with more than $days without appointment (only active)
        $cutoff = now()->subDays((int)$days);
        $clientsWithoutAppointment = (clone $clientQueryForIndicators)
            ->where('active', true)
            ->where(function ($q) use ($cutoff) {
                $q->whereNull('last_appointment_at')->orWhere('last_appointment_at', '<', $cutoff);
            })->count();

        // 2) client with most attendances (count)
        $mostAttended = null;
        $attQuery = \App\Models\Agendamento::query();
        if (auth()->check() && auth()->user()->isBarber()) {
            $attQuery->where('barbeiro_id', auth()->id());
        }
        $att = $attQuery->select('cliente_id', \DB::raw('count(*) as cnt'))
            ->groupBy('cliente_id')
            ->orderByDesc('cnt')
            ->first();
        if ($att) {
            $clienteMost = Cliente::find($att->cliente_id);
            if ($clienteMost) {
                $mostAttended = (object)['cliente' => $clienteMost->nome, 'count' => $att->cnt];
            }
        }

        // 3) most profitable client (sum of agendamentos.price)
        $profitQuery = \App\Models\Agendamento::whereNotNull('price');
        if (auth()->check() && auth()->user()->isBarber()) {
            $profitQuery->where('barbeiro_id', auth()->id());
        }
        $prof = $profitQuery->select('cliente_id', \DB::raw('sum(price) as total'))
            ->groupBy('cliente_id')
            ->orderByDesc('total')
            ->first();
        $mostProfitable = null;
        if ($prof) {
            $c = Cliente::find($prof->cliente_id);
            if ($c) {
                $mostProfitable = (object)['cliente' => $c->nome, 'valor' => $prof->total];
            }
        }

        return view('clientes.index', compact('clientes', 'clientsWithoutAppointment', 'mostAttended', 'mostProfitable', 'days'));
    }

    /**
     * Toggle active/inactive status for a cliente
     */
    public function toggleStatus(Cliente $cliente)
    {
        // Allow owners to toggle any client; barbers can toggle only clients they attended
        if (auth()->user()->isBarber()) {
            $attended = $cliente->agendamentos()->where('barbeiro_id', auth()->id())->exists();
            if (!$attended) {
                abort(403);
            }
        }

        $cliente->active = !$cliente->active;
        $cliente->save();

        return redirect()->route('clientes.index')->with('success', 'Status do cliente atualizado.');
    }

    public function create()
    {
        return view('clientes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'nullable|email',
            'telefone' => 'nullable|string|max:50',
            'observacoes' => 'nullable|string',
        ]);

        Cliente::create($data);
        return redirect()->route('clientes.index')->with('success', 'Cliente criado.');
    }

    /**
     * Store cliente via AJAX from other screens (inline creation)
     */
    public function storeInline(Request $request)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'nullable|email',
            'telefone' => 'nullable|string|max:50',
            'observacoes' => 'nullable|string',
        ]);

        $cliente = Cliente::create($data);

        return response()->json(['id' => $cliente->id, 'nome' => $cliente->nome]);
    }

    public function edit(Cliente $cliente)
    {
        return view('clientes.edit', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'nullable|email',
            'telefone' => 'nullable|string|max:50',
            'observacoes' => 'nullable|string',
        ]);

        $cliente->update($data);
        return redirect()->route('clientes.index')->with('success', 'Cliente atualizado.');
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->delete();
        return redirect()->route('clientes.index')->with('success', 'Cliente removido.');
    }
}
