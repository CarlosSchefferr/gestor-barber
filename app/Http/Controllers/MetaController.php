<?php

namespace App\Http\Controllers;

use App\Models\Meta;
use Illuminate\Http\Request;

class MetaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'valor_meta' => 'required|numeric|min:0',
            'data_inicio' => 'nullable|date',
            'data_limite' => 'nullable|date',
            'quem_tem_acesso' => 'required|string|in:all,current,barbers,owners,attendants',
            'tipo' => 'required|string|in:reducao_despesas,aumentar_receita,novos_clientes,meta_mensal,outro',
        ]);

        $data['created_by'] = auth()->id();

        // Calcular valor_atual inicial baseado no tipo de meta
        $valorAtual = 0;
        $from = $data['data_inicio'] ?? null;
        $to = $data['data_limite'] ?? null;

        if ($data['tipo'] === 'reducao_despesas') {
            $query = \App\Models\Transacao::query()->where('tipo', 'despesa');
            if ($from && $to) {
                $query->whereBetween('data', [$from, $to]);
            }
            $valorAtual = $query->sum('valor');
        } elseif ($data['tipo'] === 'aumentar_receita' || $data['tipo'] === 'meta_mensal') {
            // Somar receitas vindas da tabela transacoes + agendamentos
            $receitas = 0;
            $tQuery = \App\Models\Transacao::where('tipo', 'receita');
            if ($from && $to) {
                $tQuery->whereBetween('data', [$from, $to]);
            }
            $receitas += $tQuery->sum('valor');

            $aQuery = \App\Models\Agendamento::whereNotNull('price');
            if ($from && $to) {
                $aQuery->whereBetween('starts_at', [$from, $to]);
            }
            $receitas += $aQuery->sum('price');

            $valorAtual = $receitas;
        } elseif ($data['tipo'] === 'novos_clientes') {
            $cQuery = \App\Models\Cliente::query();
            if ($from && $to) {
                $cQuery->whereBetween('created_at', [$from, $to]);
            }
            $valorAtual = $cQuery->count();
        }

        $data['valor_atual'] = $valorAtual;

        Meta::create($data);

        return redirect()->route('financeiro.index')->with('success', 'Meta criada.');
    }
}
