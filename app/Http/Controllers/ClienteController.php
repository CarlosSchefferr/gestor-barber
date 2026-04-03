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

        $query = Cliente::query()->with('lastAgendamento');

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

        // Preencher dinamicamente last_appointment_at para exibição quando não houver valor salvo
        foreach ($clientes as $cliente) {
            if (empty($cliente->last_appointment_at) && $cliente->lastAgendamento) {
                $cliente->last_appointment_at = $cliente->lastAgendamento->starts_at;
            }
        }

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

        // Dados para os selects de filtro na aba Atendimentos
        $barbeiros = \App\Models\User::orderBy('name')->get();
        $servicos = \App\Models\Agendamento::whereNotNull('servico')
            ->distinct()
            ->pluck('servico')
            ->sort()
            ->values();
        $produtos = \App\Models\Product::orderBy('name')->get();

        return view('clientes.index', compact('clientes', 'clientsWithoutAppointment', 'mostAttended', 'mostProfitable', 'days', 'barbeiros', 'servicos', 'produtos'));
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
            'data_nascimento' => 'required|date',
            'email' => 'nullable|email',
            'telefone' => 'required|string|max:50',
            'cep' => 'nullable|string|max:10',
            'bairro' => 'nullable|string|max:100',
            'observacoes' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        // Handle photo upload
        if ($request->hasFile('foto')) {
            $foto = $request->file('foto');
            $filename = time() . '_' . $foto->getClientOriginalName();
            $data['foto'] = $foto->storeAs('clientes/fotos', $filename, 'public');
        }

        // Add audit trail
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        $cliente = Cliente::create($data);
        return redirect()->route('clientes.index')->with('success', 'Cliente criado.');
    }

    /**
     * Store cliente via AJAX from other screens (inline creation)
     */
    public function storeInline(Request $request)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'data_nascimento' => 'required|date',
            'email' => 'nullable|email',
            'telefone' => 'required|string|max:50',
            'cep' => 'nullable|string|max:10',
            'bairro' => 'nullable|string|max:100',
            'observacoes' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        // Handle photo upload
        if ($request->hasFile('foto')) {
            $foto = $request->file('foto');
            $filename = time() . '_' . $foto->getClientOriginalName();
            $data['foto'] = $foto->storeAs('clientes/fotos', $filename, 'public');
        }

        // Add audit trail
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        $cliente = Cliente::create($data);

        return response()->json([
            'id' => $cliente->id,
            'nome' => $cliente->nome,
            'success' => true
        ]);
    }

    public function edit(Cliente $cliente)
    {
        return view('clientes.edit', compact('cliente'));
    }

    /**
     * Show single cliente (JSON for AJAX)
     */
    public function show(Cliente $cliente)
    {
        // Check permissions for barbers
        if (auth()->user()->isBarber()) {
            $attended = $cliente->agendamentos()->where('barbeiro_id', auth()->id())->exists();
            if (!$attended) {
                abort(403);
            }
        }

        return response()->json($cliente);
    }

    public function update(Request $request, Cliente $cliente)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'data_nascimento' => 'required|date',
            'email' => 'nullable|email',
            'telefone' => 'required|string|max:50',
            'cep' => 'nullable|string|max:10',
            'bairro' => 'nullable|string|max:100',
            'observacoes' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        // Handle photo upload
        if ($request->hasFile('foto')) {
            // Delete old photo if exists
            if ($cliente->foto && \Storage::disk('public')->exists($cliente->foto)) {
                \Storage::disk('public')->delete($cliente->foto);
            }
            $foto = $request->file('foto');
            $filename = time() . '_' . $foto->getClientOriginalName();
            $data['foto'] = $foto->storeAs('clientes/fotos', $filename, 'public');
        }

        // Add audit trail
        $data['updated_by'] = auth()->id();

        $cliente->update($data);
        return redirect()->route('clientes.index')->with('success', 'Cliente atualizado.');
    }

    /**
     * Check if phone number already exists (AJAX validation)
     */
    public function checkDuplicatePhone(Request $request)
    {
        $telefone = $request->input('telefone');
        $clienteId = $request->input('cliente_id'); // For edit mode

        $query = Cliente::where('telefone', $telefone);
        if ($clienteId) {
            $query->where('id', '!=', $clienteId);
        }

        $cliente = $query->first();

        if ($cliente) {
            return response()->json([
                'exists' => true,
                'cliente' => [
                    'id' => $cliente->id,
                    'nome' => $cliente->nome,
                    'email' => $cliente->email,
                    'telefone' => $cliente->telefone,
                    'data_nascimento' => $cliente->data_nascimento ? $cliente->data_nascimento->format('d/m/Y') : null,
                ]
            ]);
        }

        return response()->json(['exists' => false]);
    }

    /**
     * Check if name already exists (AJAX validation)
     */
    public function checkDuplicateName(Request $request)
    {
        $nome = $request->input('nome');
        $clienteId = $request->input('cliente_id'); // For edit mode

        $query = Cliente::where('nome', 'like', $nome);
        if ($clienteId) {
            $query->where('id', '!=', $clienteId);
        }

        $clientes = $query->limit(5)->get(['id', 'nome', 'email', 'telefone', 'data_nascimento']);

        if ($clientes->count() > 0) {
            return response()->json([
                'exists' => true,
                'clientes' => $clientes->map(function ($cliente) {
                    return [
                        'id' => $cliente->id,
                        'nome' => $cliente->nome,
                        'email' => $cliente->email,
                        'telefone' => $cliente->telefone,
                        'data_nascimento' => $cliente->data_nascimento ? $cliente->data_nascimento->format('d/m/Y') : null,
                    ];
                })
            ]);
        }

        return response()->json(['exists' => false]);
    }

    /**
     * Get client statistics (for Atendimentos tab)
     */
    public function getClientStatistics($id)
    {
        $cliente = Cliente::with(['agendamentos' => function ($query) {
            $query->with(['barbeiro', 'produtos']);
        }])->findOrFail($id);

        // Check permissions for barbers
        if (auth()->user()->isBarber()) {
            $attended = $cliente->agendamentos()->where('barbeiro_id', auth()->id())->exists();
            if (!$attended) {
                abort(403);
            }
        }

        // Dias sem atendimento
        $lastAppointment = $cliente->agendamentos()
            ->where('status', 'atendido')
            ->orderBy('starts_at', 'desc')
            ->first();
        $daysSinceLastAppointment = null;
        if ($lastAppointment) {
            // Garantir que o cálculo sempre resulta em número positivo
            // diffInDays retorna valor absoluto quando usamos true como segundo parâmetro
            $daysSinceLastAppointment = (int) $lastAppointment->starts_at->diffInDays(now(), false);
        }

        // Atendimentos executados
        $atendimentosCount = $cliente->agendamentos()
            ->where('status', 'atendido')
            ->count();

        // Serviço mais realizado
        $mostFrequentService = $cliente->agendamentos()
            ->where('status', 'atendido')
            ->whereNotNull('servico')
            ->select('servico', \DB::raw('count(*) as count'))
            ->groupBy('servico')
            ->orderByDesc('count')
            ->first();

        // Produtos comprados (da tabela pivot)
        $produtosCount = \DB::table('agendamento_produto')
            ->join('agendamentos', 'agendamento_produto.agendamento_id', '=', 'agendamentos.id')
            ->where('agendamentos.cliente_id', $cliente->id)
            ->where('agendamentos.status', 'atendido')
            ->sum('agendamento_produto.quantity');

        // Produto mais comprado
        $mostBoughtProduct = \DB::table('agendamento_produto')
            ->join('agendamentos', 'agendamento_produto.agendamento_id', '=', 'agendamentos.id')
            ->join('products', 'agendamento_produto.produto_id', '=', 'products.id')
            ->where('agendamentos.cliente_id', $cliente->id)
            ->where('agendamentos.status', 'atendido')
            ->select('products.name', \DB::raw('sum(agendamento_produto.quantity) as total'))
            ->groupBy('products.name')
            ->orderByDesc('total')
            ->first();

        // Valores gastos
        $valorServicos = $cliente->agendamentos()
            ->where('status', 'atendido')
            ->sum('price');

        $valorProdutos = \DB::table('agendamento_produto')
            ->join('agendamentos', 'agendamento_produto.agendamento_id', '=', 'agendamentos.id')
            ->where('agendamentos.cliente_id', $cliente->id)
            ->where('agendamentos.status', 'atendido')
            ->sum(\DB::raw('agendamento_produto.quantity * agendamento_produto.price'));

        return response()->json([
            'days_since_last_appointment' => $daysSinceLastAppointment,
            'atendimentos_count' => $atendimentosCount,
            'most_frequent_service' => $mostFrequentService ? $mostFrequentService->servico : null,
            'most_frequent_service_count' => $mostFrequentService ? $mostFrequentService->count : 0,
            'produtos_count' => $produtosCount ?? 0,
            'most_bought_product' => $mostBoughtProduct ? $mostBoughtProduct->name : null,
            'most_bought_product_count' => $mostBoughtProduct ? $mostBoughtProduct->total : 0,
            'valor_servicos' => $valorServicos,
            'valor_produtos' => $valorProdutos ?? 0,
            'valor_total' => $valorServicos + ($valorProdutos ?? 0),
        ]);
    }

    /**
     * Get client attendance history with filters
     */
    public function getClientHistory(Request $request, $id)
    {
        $cliente = Cliente::findOrFail($id);

        // Check permissions for barbers
        if (auth()->user()->isBarber()) {
            $attended = $cliente->agendamentos()->where('barbeiro_id', auth()->id())->exists();
            if (!$attended) {
                abort(403);
            }
        }

        $query = $cliente->agendamentos()->with(['barbeiro', 'produtos']);

        // Apply filters
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        if ($request->has('data_inicio') && $request->data_inicio) {
            $query->whereDate('starts_at', '>=', $request->data_inicio);
        }

        if ($request->has('data_fim') && $request->data_fim) {
            $query->whereDate('starts_at', '<=', $request->data_fim);
        }

        if ($request->has('barbeiro_id') && $request->barbeiro_id) {
            $query->where('barbeiro_id', $request->barbeiro_id);
        }

        if ($request->has('servico') && $request->servico) {
            $query->where('servico', 'like', '%' . $request->servico . '%');
        }

        if ($request->has('produto_id') && $request->produto_id) {
            $query->whereHas('produtos', function ($q) use ($request) {
                $q->where('produto_id', $request->produto_id);
            });
        }

        $atendimentos = $query->orderBy('starts_at', 'desc')->paginate(15);

        // Format response with proper data structure
        $formattedData = collect($atendimentos->items())->map(function ($atend) {
            return [
                'id' => $atend->id,
                'status' => $atend->status,
                'data_hora' => $atend->starts_at,
                'barbeiro_nome' => $atend->barbeiro ? $atend->barbeiro->name : null,
                'quantidade_servicos' => $atend->servico ? 1 : 0,
                'quantidade_produtos' => $atend->produtos ? $atend->produtos->sum('pivot.quantity') : 0,
                'valor_total' => ($atend->price ?? 0) + ($atend->produtos ? $atend->produtos->sum(function ($produto) {
                    return ($produto->pivot->price ?? 0) * ($produto->pivot->quantity ?? 0);
                }) : 0),
            ];
        });

        return response()->json([
            'data' => $formattedData,
            'current_page' => $atendimentos->currentPage(),
            'last_page' => $atendimentos->lastPage(),
            'per_page' => $atendimentos->perPage(),
            'total' => $atendimentos->total(),
        ]);
    }

    /**
     * Get barbeiros for select dropdown (AJAX)
     */
    public function getBarbeiros()
    {
        $barbeiros = \App\Models\User::orderBy('name')->get(['id', 'name']);
        return response()->json($barbeiros);
    }

    /**
     * Get unique servicos from agendamentos for select dropdown (AJAX)
     */
    public function getServicos()
    {
        $servicos = \App\Models\Agendamento::whereNotNull('servico')
            ->distinct()
            ->pluck('servico')
            ->sort()
            ->values();

        return response()->json($servicos);
    }

    /**
     * Get produtos for select dropdown (AJAX)
     */
    public function getProdutos()
    {
        $produtos = \App\Models\Product::orderBy('name')->get(['id', 'name']);
        return response()->json($produtos);
    }

    // Removed destroy method - clients can only be activated/deactivated via toggleStatus
}

