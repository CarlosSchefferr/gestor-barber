@extends('layouts.app')

@section('content')
@php
    $cardClass = 'rounded-3xl border border-zinc-200 bg-white/95 shadow-sm';
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-8 rounded-3xl border border-zinc-200 bg-white px-6 py-7 shadow-sm sm:px-8">
        <div class="flex flex-col gap-5">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500">Painel</p>
                <h1 class="mt-2 text-3xl font-bold leading-tight text-zinc-900 sm:text-4xl">Visao geral</h1>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('agendamentos.create') }}" class="inline-flex items-center justify-center rounded-2xl bg-barber-500 px-4 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-barber-600">
                    Novo agendamento
                </a>
                <a href="{{ route('clientes.create') }}" class="inline-flex items-center justify-center rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700 transition hover:bg-zinc-100">
                    Novo cliente
                </a>
                <a href="{{ route('financeiro.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-xs font-bold uppercase tracking-[0.08em] text-emerald-700 transition hover:bg-emerald-100">
                    Ver financeiro
                </a>
            </div>
        </div>
    </div>

    <!-- Cards de Estatisticas -->
    <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="{{ $cardClass }} p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Total agendamentos</p>
            <p class="mt-3 text-3xl font-bold text-zinc-900">{{ $total ?? 0 }}</p>
            <p class="mt-1 text-sm text-zinc-500">Agendamentos registrados</p>
        </div>

        <div class="{{ $cardClass }} p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Hoje</p>
            <p class="mt-3 text-3xl font-bold text-emerald-600">{{ $today ?? 0 }}</p>
            <p class="mt-1 text-sm text-zinc-500">Agendamentos do dia</p>
        </div>

        <div class="{{ $cardClass }} p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Clientes ativos</p>
            <p class="mt-3 text-3xl font-bold text-zinc-900">{{ \App\Models\Agendamento::whereBetween('starts_at', [now()->subDays(30)->startOfDay(), now()->endOfDay()])->distinct()->count('cliente_id') }}</p>
            <p class="mt-1 text-sm text-zinc-500">Nos ultimos 30 dias</p>
        </div>

        <div class="{{ $cardClass }} p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Receita hoje</p>
            <p class="mt-3 text-3xl font-bold text-emerald-600">R$ {{ number_format(App\Models\Agendamento::whereDate('starts_at', now()->toDateString())->sum('price') ?? 0, 2, ',', '.') }}</p>
            <p class="mt-1 text-sm text-zinc-500">Faturamento do dia</p>
        </div>
    </div>

    <!-- Graficos -->
    <div class="mb-8 grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="{{ $cardClass }} p-6 xl:col-span-2">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-zinc-900">Agendamentos ultimos 7 dias</h3>
                    <p class="mt-1 text-sm text-zinc-500">Evolucao diaria de agendamentos</p>
                </div>
                <button id="chart-toggle" class="inline-flex items-center justify-center rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2 text-xs font-semibold text-zinc-600 transition hover:bg-zinc-100">
                    Ver grafico
                </button>
            </div>
            <div class="h-64">
                <canvas id="agendamentosChart"></canvas>
            </div>
        </div>

        <div class="{{ $cardClass }} p-6">
            <h3 class="text-base font-bold text-zinc-900">Servicos da semana</h3>
            <p class="mt-1 text-sm text-zinc-500">Distribuicao por tipo</p>
            <div class="mt-4 h-64">
                <canvas id="servicosChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Metas de Crescimento -->
    @if(isset($metas) && $metas->count() > 0)
    <div class="{{ $cardClass }} mb-8 p-6">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-zinc-900">Metas de crescimento</h3>
                <p class="mt-1 text-sm text-zinc-500">Acompanhe o progresso dos objetivos</p>
            </div>
            <a href="{{ route('financeiro.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-zinc-300 bg-white px-4 py-2.5 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700 transition hover:bg-zinc-100">
                Ver todas
            </a>
        </div>

        <div class="space-y-4">
            @forelse($metas as $meta)
                <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4 sm:p-5">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex-1">
                            <h4 class="text-sm font-bold text-zinc-900">{{ $meta->nome }}</h4>
                            <p class="mt-1 text-sm text-zinc-600">{{ $meta->descricao ?: 'Sem descricao informada.' }}</p>
                            <div class="mt-3 flex items-center gap-3">
                                <div class="h-2 w-full rounded-full bg-zinc-200">
                                    <div class="h-2 rounded-full bg-emerald-500" style="width: {{ $meta->valor_meta > 0 ? min(100, ($meta->valor_atual / $meta->valor_meta) * 100) : 0 }}%"></div>
                                </div>
                                <span class="text-xs font-semibold text-zinc-500">{{ number_format($meta->valor_meta > 0 ? (($meta->valor_atual / $meta->valor_meta) * 100) : 0, 1) }}%</span>
                            </div>
                        </div>
                        <div class="min-w-[200px] rounded-xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-600">
                            <p><span class="font-semibold text-zinc-900">Atual:</span> R$ {{ number_format($meta->valor_atual, 2, ',', '.') }}</p>
                            <p><span class="font-semibold text-zinc-900">Meta:</span> R$ {{ number_format($meta->valor_meta, 2, ',', '.') }}</p>
                            @if($meta->data_limite)
                                <p class="mt-1"><span class="font-semibold text-zinc-900">Prazo:</span> {{ \Carbon\Carbon::parse($meta->data_limite)->format('d/m/Y') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-zinc-300 bg-zinc-50 p-10 text-center">
                    <p class="text-sm font-medium text-zinc-500">Nenhuma meta cadastrada</p>
                </div>
            @endforelse
        </div>
    </div>
    @endif

    <!-- Resumo Financeiro -->
    <div class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="{{ $cardClass }} p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Receita hoje</p>
            <p class="mt-3 text-3xl font-bold text-emerald-600">R$ {{ number_format(App\Models\Agendamento::whereDate('starts_at', now()->toDateString())->sum('price') ?? 0, 2, ',', '.') }}</p>
            <p class="mt-1 text-sm text-zinc-500">Entradas do dia</p>
        </div>

        <div class="{{ $cardClass }} p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Receita semana</p>
            <p class="mt-3 text-3xl font-bold text-zinc-900">R$ {{ number_format(App\Models\Agendamento::whereBetween('starts_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()])->sum('price') ?? 0, 2, ',', '.') }}</p>
            <p class="mt-1 text-sm text-zinc-500">Ultimos 7 dias</p>
        </div>

        <div class="{{ $cardClass }} p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Ticket medio</p>
            <p class="mt-3 text-3xl font-bold text-zinc-900">R$ {{ number_format(App\Models\Agendamento::whereNotNull('price')->avg('price') ?? 0, 2, ',', '.') }}</p>
            <p class="mt-1 text-sm text-zinc-500">Valor medio por atendimento</p>
        </div>
    </div>

    <!-- Agendamentos de Hoje -->
    <div class="{{ $cardClass }} overflow-hidden">
        <div class="border-b border-zinc-200 px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-zinc-900">Agendamentos de hoje</h3>
                    <p class="mt-1 text-sm text-zinc-500">Proximos atendimentos do dia</p>
                </div>
                <a href="{{ route('agendamentos.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-zinc-300 bg-white px-4 py-2.5 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700 transition hover:bg-zinc-100">
                    Ver todos
                </a>
            </div>
        </div>

        @if($agendamentosHoje->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-zinc-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Horario</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Cliente</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Servico</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Barbeiro</th>
                            <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wide text-zinc-500">Valor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 bg-white">
                        @foreach($agendamentosHoje as $agendamento)
                            <tr class="transition hover:bg-zinc-50">
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-zinc-900">
                                    {{ $agendamento->starts_at->format('H:i') }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-700">
                                    {{ $agendamento->cliente->nome }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span class="inline-flex rounded-full bg-barber-100 px-2.5 py-1 text-xs font-semibold text-barber-700">
                                        {{ $agendamento->servico }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-700">
                                    {{ $agendamento->barbeiro->name }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-bold text-emerald-600">
                                    R$ {{ number_format($agendamento->price ?? 0, 2, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="px-6 py-12 text-center">
                <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100">
                    <svg class="h-6 w-6 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <p class="text-sm font-medium text-zinc-500">Nenhum agendamento para hoje</p>
                <a href="{{ route('agendamentos.create') }}" class="mt-4 inline-flex items-center justify-center rounded-2xl bg-barber-500 px-4 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-barber-600">
                    Novo agendamento
                </a>
            </div>
        @endif
    </div>

    <!-- Acoes Rapidas -->
    <div class="{{ $cardClass }} mt-8 p-6">
        <h3 class="text-lg font-bold text-zinc-900">Acoes rapidas</h3>
        <p class="mt-1 text-sm text-zinc-500">Atalhos para as principais funcoes</p>

        <div class="mt-6 grid grid-cols-2 gap-4 md:grid-cols-4">
            <a href="{{ route('agendamentos.create') }}" class="group flex flex-col items-center justify-center rounded-2xl border border-zinc-200 bg-zinc-50 p-6 transition hover:border-barber-300 hover:bg-barber-50">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-barber-100 text-barber-600 transition group-hover:bg-barber-200">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <span class="mt-3 text-xs font-bold uppercase tracking-wide text-zinc-700">Agendar</span>
            </a>

            <a href="{{ route('clientes.create') }}" class="group flex flex-col items-center justify-center rounded-2xl border border-zinc-200 bg-zinc-50 p-6 transition hover:border-blue-300 hover:bg-blue-50">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 text-blue-600 transition group-hover:bg-blue-200">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                    </svg>
                </div>
                <span class="mt-3 text-xs font-bold uppercase tracking-wide text-zinc-700">Novo cliente</span>
            </a>

            <a href="{{ route('agendamentos.index') }}" class="group flex flex-col items-center justify-center rounded-2xl border border-zinc-200 bg-zinc-50 p-6 transition hover:border-emerald-300 hover:bg-emerald-50">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 transition group-hover:bg-emerald-200">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <span class="mt-3 text-xs font-bold uppercase tracking-wide text-zinc-700">Agendamentos</span>
            </a>

            <a href="{{ route('clientes.index') }}" class="group flex flex-col items-center justify-center rounded-2xl border border-zinc-200 bg-zinc-50 p-6 transition hover:border-purple-300 hover:bg-purple-50">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-100 text-purple-600 transition group-hover:bg-purple-200">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <span class="mt-3 text-xs font-bold uppercase tracking-wide text-zinc-700">Clientes</span>
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const agendamentosData = @json($agendamentosPorDia);
const servicosData = @json($servicosSemana);

const colors = [
    '#c96f1f', '#db934c', '#e6b97f', '#efd3ab', '#f8ebd9',
    '#6b0f0f', '#0b0b0b', '#4f2a0a', '#7b3b0f', '#a45317'
];

const ctxLine = document.getElementById('agendamentosChart').getContext('2d');
new Chart(ctxLine, {
    type: 'line',
    data: {
        labels: agendamentosData.map(item => {
            const date = new Date(item.date);
            return date.toLocaleDateString('pt-BR', { weekday: 'short', day: '2-digit' });
        }),
        datasets: [{
            label: 'Agendamentos',
            data: agendamentosData.map(item => item.count),
            borderColor: '#c96f1f',
            backgroundColor: 'rgba(201, 111, 31, 0.12)',
            borderWidth: 2.5,
            fill: true,
            tension: 0.35,
            pointBackgroundColor: '#c96f1f',
            pointBorderColor: '#ffffff',
            pointBorderWidth: 2,
            pointRadius: 5
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { stepSize: 1 },
                grid: { color: 'rgba(0, 0, 0, 0.04)' }
            },
            x: {
                grid: { display: false }
            }
        }
    }
});

const ctxPie = document.getElementById('servicosChart').getContext('2d');
new Chart(ctxPie, {
    type: 'doughnut',
    data: {
        labels: servicosData.map(item => item.servico),
        datasets: [{
            data: servicosData.map(item => item.quantidade),
            backgroundColor: colors.slice(0, servicosData.length),
            borderWidth: 2,
            borderColor: '#ffffff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: { padding: 16, usePointStyle: true }
            }
        },
        cutout: '60%'
    }
});

document.getElementById('chart-toggle').addEventListener('click', function() {
    const canvas = document.getElementById('agendamentosChart');
    if (canvas.style.display === 'none') {
        canvas.style.display = 'block';
        this.textContent = 'Ocultar grafico';
    } else {
        canvas.style.display = 'none';
        this.textContent = 'Ver grafico';
    }
});
</script>
@endsection
