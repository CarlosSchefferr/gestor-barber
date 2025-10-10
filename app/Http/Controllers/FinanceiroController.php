<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FinanceiroController extends Controller
{
    public function index()
    {
        // Placeholder data for initial screen
        $saldo = 0;
        $receitas = [];
        $despesas = [];

        return view('financeiro.index', compact('saldo', 'receitas', 'despesas'));
    }
}
