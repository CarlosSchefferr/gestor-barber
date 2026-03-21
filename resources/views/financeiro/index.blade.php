@extends('layouts.app')

@section('content')
@php
    $inputClass = 'mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20';
    $cardClass = 'rounded-3xl border border-zinc-200 bg-white/95 shadow-sm';
    $lucroPositivo = $lucroLiquido >= 0;
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-8 rounded-3xl border border-zinc-200 bg-white px-6 py-7 shadow-sm sm:px-8">
        <div class="flex flex-col gap-5">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500">Financeiro</p>
                <h1 class="mt-2 text-3xl font-bold leading-tight text-zinc-900 sm:text-4xl">Resumo e performance</h1>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('financeiro.presentation.monthly.preview') }}" class="inline-flex items-center justify-center rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700 transition hover:bg-zinc-100">
                    Ver apresentação
                </a>
                <a href="{{ route('financeiro.presentation.monthly.pdf') }}" class="inline-flex items-center justify-center rounded-2xl bg-barber-500 px-4 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-barber-600">
                    Baixar PDF mensal
                </a>
                <button type="button" onclick="openMetaModal()" class="inline-flex items-center justify-center rounded-2xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-xs font-bold uppercase tracking-[0.08em] text-emerald-700 transition hover:bg-emerald-100">
                    Nova meta
                </button>
                <button type="button" onclick="openTransacaoModal()" class="inline-flex items-center justify-center rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700 transition hover:bg-zinc-100">
                    Nova transacao
                </button>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3">
            <p class="text-sm font-medium text-emerald-700">{{ session('success') }}</p>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3">
            <ul class="list-disc pl-5 text-sm text-red-700">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="{{ $cardClass }} mb-8 p-6 sm:p-7">
        <div class="mb-5 flex items-center justify-between">
            <h2 class="text-lg font-bold text-zinc-900">Filtros</h2>
            <span class="rounded-full border border-zinc-200 bg-zinc-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-zinc-500">Periodo de analise</span>
        </div>

        <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <div>
                <label class="text-sm font-semibold text-zinc-700">Periodo</label>
                <x-custom-select
                    name="period"
                    :options="[
                        'today' => 'Hoje',
                        'week' => 'Esta semana',
                        'month' => 'Este mes',
                        'quarter' => 'Este trimestre',
                        'year' => 'Este ano',
                        'custom' => 'Personalizado',
                    ]"
                    :value="request('period', $period ?? 'month')"
                    placeholder="Selecione o periodo"
                />
            </div>

            <div>
                <label for="from" class="text-sm font-semibold text-zinc-700">Data inicio</label>
                <input id="from" type="date" name="from" value="{{ request('from', $from ? \Carbon\Carbon::parse($from)->format('Y-m-d') : '') }}" class="{{ $inputClass }}">
            </div>

            <div>
                <label for="to" class="text-sm font-semibold text-zinc-700">Data fim</label>
                <input id="to" type="date" name="to" value="{{ request('to', $to ? \Carbon\Carbon::parse($to)->format('Y-m-d') : '') }}" class="{{ $inputClass }}">
            </div>

            <div>
                <label class="text-sm font-semibold text-zinc-700">Tipo</label>
                <x-custom-select
                    name="type"
                    :options="[
                        '' => 'Todos',
                        'receita' => 'Receita',
                        'despesa' => 'Despesa',
                    ]"
                    :value="request('type', $type ?? '')"
                    placeholder="Selecione o tipo"
                />
            </div>

            <div class="md:col-span-4 flex flex-wrap items-center justify-center gap-3 pt-1">
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-barber-500 px-5 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-barber-600">
                    Aplicar filtros
                </button>
                <a href="{{ route('financeiro.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-zinc-300 bg-white px-5 py-3 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700 transition hover:bg-zinc-100">
                    Limpar
                </a>
            </div>
        </form>
    </div>

    <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="{{ $cardClass }} p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Receita total</p>
            <p class="mt-3 text-3xl font-bold text-zinc-900">R$ {{ number_format($receitaTotal, 2, ',', '.') }}</p>
            <p class="mt-1 text-sm text-zinc-500">Entradas consolidadas no periodo</p>
        </div>

        <div class="{{ $cardClass }} p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Despesas</p>
            <p class="mt-3 text-3xl font-bold text-zinc-900">R$ {{ number_format($despesas, 2, ',', '.') }}</p>
            <p class="mt-1 text-sm text-zinc-500">Custos e saidas registradas</p>
        </div>

        <div class="{{ $cardClass }} p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Lucro liquido</p>
            <p class="mt-3 text-3xl font-bold {{ $lucroPositivo ? 'text-emerald-600' : 'text-red-600' }}">R$ {{ number_format($lucroLiquido, 2, ',', '.') }}</p>
            <p class="mt-1 text-sm text-zinc-500">Resultado apos despesas</p>
        </div>

        <div class="{{ $cardClass }} p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Meta do mes</p>
            <p class="mt-3 text-3xl font-bold text-zinc-900">R$ {{ number_format($metaMes, 2, ',', '.') }}</p>
            <div class="mt-4 flex items-center gap-3">
                <div class="h-2 w-full rounded-full bg-zinc-200">
                    <div class="h-2 rounded-full bg-barber-500" style="width: {{ min(100, max(0, $progressoMeta)) }}%"></div>
                </div>
                <span class="text-xs font-semibold text-zinc-500">{{ number_format($progressoMeta, 1) }}%</span>
            </div>
        </div>
    </div>

    <div class="mb-8 grid grid-cols-1 gap-6 xl:grid-cols-2">
        <div class="{{ $cardClass }} p-6">
            <h3 class="text-base font-bold text-zinc-900">Receita por mes</h3>
            <p class="mt-1 text-sm text-zinc-500">Evolucao dos ultimos 6 meses</p>
            <div class="mt-4">
                <canvas id="receitaChart" height="120"></canvas>
            </div>
        </div>

        <div class="{{ $cardClass }} p-6">
            <h3 class="text-base font-bold text-zinc-900">Progresso das metas</h3>
            <p class="mt-1 text-sm text-zinc-500">Concluidas, em andamento e pendentes</p>
            <div class="mt-4">
                <canvas id="metasChart" height="120"></canvas>
            </div>
        </div>
    </div>

    <div class="{{ $cardClass }} mb-8 p-6">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-zinc-900">Metas de crescimento</h3>
                <p class="mt-1 text-sm text-zinc-500">Acompanhe o progresso de cada objetivo do time</p>
            </div>
            <button type="button" onclick="openMetaModal()" class="inline-flex items-center justify-center rounded-2xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-xs font-bold uppercase tracking-[0.08em] text-emerald-700 transition hover:bg-emerald-100">
                Nova meta
            </button>
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
                                    <div class="h-2 rounded-full bg-emerald-500" style="width: {{ min(100, max(0, $meta->percent)) }}%"></div>
                                </div>
                                <span class="text-xs font-semibold text-zinc-500">{{ number_format($meta->percent, 1) }}%</span>
                            </div>
                        </div>
                        <div class="min-w-[220px] rounded-xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-600">
                            @if($meta->tipo === 'novos_clientes')
                                <p><span class="font-semibold text-zinc-900">Atual:</span> {{ $meta->valor_atual }} / {{ intval($meta->valor_meta) }} clientes</p>
                            @else
                                <p><span class="font-semibold text-zinc-900">Atual:</span> R$ {{ number_format($meta->valor_atual, 2, ',', '.') }}</p>
                                <p><span class="font-semibold text-zinc-900">Meta:</span> R$ {{ number_format($meta->valor_meta, 2, ',', '.') }}</p>
                            @endif
                            <p class="mt-1"><span class="font-semibold text-zinc-900">Prazo:</span> {{ $meta->data_limite ? \Carbon\Carbon::parse($meta->data_limite)->format('d/m/Y') : '-' }}</p>
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

    <div class="{{ $cardClass }} overflow-hidden">
        <div class="border-b border-zinc-200 px-6 py-4">
            <h3 class="text-lg font-bold text-zinc-900">Transacoes recentes</h3>
            <p class="mt-1 text-sm text-zinc-500">Ultimos lancamentos do periodo filtrado</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-zinc-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Data</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Descricao</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Valor</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 bg-white">
                    @forelse($transacoes as $transacao)
                        <tr class="transition hover:bg-zinc-50">
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-700">{{ \Carbon\Carbon::parse($transacao['data'])->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-zinc-900">{{ $transacao['descricao'] }}</td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $transacao['tipo'] == 'receita' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                    {{ ucfirst($transacao['tipo']) }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-bold {{ $transacao['tipo'] == 'receita' ? 'text-emerald-600' : 'text-red-600' }}">
                                {{ $transacao['tipo'] == 'receita' ? '+' : '-' }}R$ {{ number_format($transacao['valor'], 2, ',', '.') }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <span class="inline-flex rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-semibold text-zinc-700">{{ $transacao['status'] }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-sm font-medium text-zinc-500">Nenhuma transacao encontrada</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="metaModal" class="fixed inset-0 z-50 hidden h-full w-full overflow-y-auto bg-zinc-900/60 backdrop-blur-[2px]">
    <div class="relative top-10 mx-auto w-full max-w-2xl rounded-3xl border border-zinc-200 bg-white p-6 shadow-xl sm:p-8">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-barber-500">Cadastro</p>
                <h3 class="mt-2 text-2xl font-bold text-zinc-900">Nova meta de crescimento</h3>
            </div>
            <button type="button" onclick="closeMetaModal()" class="rounded-xl p-2 text-zinc-400 transition hover:bg-zinc-100 hover:text-zinc-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <form action="{{ route('metas.store') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="text-sm font-semibold text-zinc-700">Nome da meta <span class="text-red-500">*</span></label>
                    <input type="text" name="nome" required class="{{ $inputClass }}" placeholder="Ex: Aumentar receita em 20%">
                </div>

                <div class="sm:col-span-2">
                    <label class="text-sm font-semibold text-zinc-700">Descricao</label>
                    <textarea name="descricao" rows="3" class="{{ $inputClass }}" placeholder="Descreva a meta..."></textarea>
                </div>

                <div>
                    <label id="label_valor_meta" class="text-sm font-semibold text-zinc-700">Valor da meta (R$) <span class="text-red-500">*</span></label>
                    <input id="valor_meta" type="number" name="valor_meta" step="0.01" required class="{{ $inputClass }}" placeholder="0,00">
                </div>

                <div>
                    <label class="text-sm font-semibold text-zinc-700">Data inicial</label>
                    <input type="date" name="data_inicio" class="{{ $inputClass }}">
                </div>

                <div>
                    <label class="text-sm font-semibold text-zinc-700">Data limite <span class="text-red-500">*</span></label>
                    <input type="date" name="data_limite" required class="{{ $inputClass }}">
                </div>

                <div>
                    <label class="text-sm font-semibold text-zinc-700">Quem tem acesso</label>
                    <x-custom-select
                        name="quem_tem_acesso"
                        :options="[
                            'all' => 'Todos usuarios',
                            'current' => 'Usuario atual',
                            'barbers' => 'Barbeiros',
                            'owners' => 'Administradores',
                            'attendants' => 'Atendentes',
                        ]"
                        value="all"
                        placeholder="Selecione quem tem acesso"
                    />
                </div>

                <div class="sm:col-span-2">
                    <label class="text-sm font-semibold text-zinc-700">Tipo de meta</label>
                    <x-custom-select
                        name="tipo"
                        id="tipo_meta_select"
                        :options="[
                            'reducao_despesas' => 'Reducao de despesas',
                            'aumentar_receita' => 'Aumentar receita',
                            'novos_clientes' => 'Novos clientes',
                            'meta_mensal' => 'Meta mensal',
                            'outro' => 'Outro',
                        ]"
                        value="reducao_despesas"
                        placeholder="Selecione o tipo de meta"
                    />
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeMetaModal()" class="inline-flex items-center justify-center rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700 transition hover:bg-zinc-100">Cancelar</button>
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-4 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-emerald-700">Salvar meta</button>
            </div>
        </form>
    </div>
</div>

<div id="transacaoModal" class="fixed inset-0 z-50 hidden h-full w-full overflow-y-auto bg-zinc-900/60 backdrop-blur-[2px]">
    <div class="relative top-10 mx-auto w-full max-w-xl rounded-3xl border border-zinc-200 bg-white p-6 shadow-xl sm:p-8">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-barber-500">Cadastro</p>
                <h3 class="mt-2 text-2xl font-bold text-zinc-900">Nova transacao</h3>
            </div>
            <button type="button" onclick="closeTransacaoModal()" class="rounded-xl p-2 text-zinc-400 transition hover:bg-zinc-100 hover:text-zinc-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <form action="{{ route('transacoes.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="text-sm font-semibold text-zinc-700">Tipo <span class="text-red-500">*</span></label>
                <x-custom-select
                    name="tipo"
                    :options="[
                        '' => 'Selecione o tipo',
                        'receita' => 'Receita',
                        'despesa' => 'Despesa',
                    ]"
                    value=""
                    placeholder="Selecione o tipo"
                    :required="true"
                />
            </div>

            <div>
                <label class="text-sm font-semibold text-zinc-700">Descricao <span class="text-red-500">*</span></label>
                <input type="text" name="descricao" required class="{{ $inputClass }}" placeholder="Ex: Corte de cabelo">
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="text-sm font-semibold text-zinc-700">Valor (R$) <span class="text-red-500">*</span></label>
                    <input type="number" name="valor" step="0.01" required class="{{ $inputClass }}" placeholder="0,00">
                </div>
                <div>
                    <label class="text-sm font-semibold text-zinc-700">Data <span class="text-red-500">*</span></label>
                    <input type="date" name="data" required value="{{ old('data', date('Y-m-d')) }}" class="{{ $inputClass }}">
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeTransacaoModal()" class="inline-flex items-center justify-center rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700 transition hover:bg-zinc-100">Cancelar</button>
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-barber-500 px-4 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-barber-600">Salvar transacao</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const receitaCanvas = document.getElementById('receitaChart');
if (receitaCanvas) {
    const receitaCtx = receitaCanvas.getContext('2d');
    new Chart(receitaCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode(collect($receitaPorMes)->pluck('mes')) !!},
            datasets: [{
                label: 'Receita',
                data: {!! json_encode(collect($receitaPorMes)->pluck('receita')) !!},
                borderColor: '#c96f1f',
                backgroundColor: 'rgba(201, 111, 31, 0.12)',
                borderWidth: 2.5,
                fill: true,
                tension: 0.35
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            }
        }
    });
}

const metasCanvas = document.getElementById('metasChart');
if (metasCanvas) {
    const metasCtx = metasCanvas.getContext('2d');
    const metasData = {!! json_encode($metaStatusCounts ?? [0,0,0]) !!};
    new Chart(metasCtx, {
        type: 'doughnut',
        data: {
            labels: ['Concluido', 'Em andamento', 'Pendente'],
            datasets: [{
                data: metasData,
                backgroundColor: ['#10B981', '#F59E0B', '#EF4444']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

function openMetaModal() {
    document.getElementById('metaModal').classList.remove('hidden');
    setTimeout(setMetaValorField, 50);
}

function setMetaValorField() {
    const tipoInput = document.querySelector('#metaModal input[name="tipo"]');
    const label = document.getElementById('label_valor_meta');
    const input = document.getElementById('valor_meta');
    if (!tipoInput || !label || !input) return;

    if (tipoInput.value === 'novos_clientes') {
        label.textContent = 'Quantidade (clientes) *';
        input.step = 1;
        input.min = 0;
        input.placeholder = '0';
        input.value = '';
    } else {
        label.textContent = 'Valor da meta (R$) *';
        input.step = 0.01;
        input.min = 0;
        input.placeholder = '0,00';
        input.value = '';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const tipoInput = document.querySelector('#metaModal input[name="tipo"]');
    if (tipoInput) {
        tipoInput.addEventListener('change', setMetaValorField);
        setMetaValorField();
    }
});

function closeMetaModal() {
    document.getElementById('metaModal').classList.add('hidden');
}

function openTransacaoModal() {
    document.getElementById('transacaoModal').classList.remove('hidden');
}

function closeTransacaoModal() {
    document.getElementById('transacaoModal').classList.add('hidden');
}

const metaModal = document.getElementById('metaModal');
if (metaModal) {
    metaModal.addEventListener('click', function (e) {
        if (e.target === this) {
            closeMetaModal();
        }
    });
}

const transacaoModal = document.getElementById('transacaoModal');
if (transacaoModal) {
    transacaoModal.addEventListener('click', function (e) {
        if (e.target === this) {
            closeTransacaoModal();
        }
    });
}
</script>
@endsection
