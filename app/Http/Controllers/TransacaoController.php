<?php

namespace App\Http\Controllers;

use App\Models\Transacao;
use Illuminate\Http\Request;

class TransacaoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tipo' => 'required|in:receita,despesa',
            'descricao' => 'nullable|string|max:255',
            'valor' => 'required|numeric',
            'data' => 'required|date',
            'status' => 'nullable|string',
        ]);

        $transacao = Transacao::create($data + ['status' => $data['status'] ?? 'Confirmado']);

        return redirect()->route('financeiro.index')->with('success', 'Transação registrada.');
    }
}
