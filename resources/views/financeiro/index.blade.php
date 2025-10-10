@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-bold">Financeiro</h1>
        <a href="#"><x-barber-button>Adicionar</x-barber-button></a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-barber-card>
            <div>
                <div class="text-sm text-gray-500">Saldo atual</div>
                <div class="text-2xl font-bold">R$ {{ number_format($saldo ?? 0, 2, ',', '.') }}</div>
            </div>
        </x-barber-card>

        <x-barber-card>
            <div>
                <div class="text-sm text-gray-500">Receitas (últimos 30 dias)</div>
                <div class="text-lg font-semibold">{{ count($receitas) }}</div>
            </div>
        </x-barber-card>

        <x-barber-card>
            <div>
                <div class="text-sm text-gray-500">Despesas (últimos 30 dias)</div>
                <div class="text-lg font-semibold">{{ count($despesas) }}</div>
            </div>
        </x-barber-card>
    </div>

    <div class="mt-6">
        <x-barber-card>
            <div class="text-gray-600">Relatórios e movimentações serão exibidos aqui.</div>
        </x-barber-card>
    </div>
</div>
@endsection
