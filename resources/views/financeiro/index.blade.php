@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Financeiro</h1>
            <p class="text-gray-600 mt-1">Controle financeiro e metas da barbearia</p>
        </div>
        <div class="flex gap-3">
            <button onclick="openMetaModal()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors shadow-sm">
                + Nova Meta
            </button>
            <button onclick="openTransacaoModal()" class="bg-barber-600 text-white px-4 py-2 rounded-lg hover:bg-barber-700 transition-colors shadow-sm">
                + Nova Transação
            </button>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Filtros</h3>
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Período</label>
                <select name="periodo" class="w-full border-gray-300 rounded-md shadow-sm focus:border-barber-500 focus:ring-barber-500">
                    <option value="hoje" {{ request('periodo') == 'hoje' ? 'selected' : '' }}>Hoje</option>
                    <option value="semana" {{ request('periodo') == 'semana' ? 'selected' : '' }}>Esta Semana</option>
                    <option value="mes" {{ request('periodo') == 'mes' ? 'selected' : '' }}>Este Mês</option>
                    <option value="trimestre" {{ request('periodo') == 'trimestre' ? 'selected' : '' }}>Este Trimestre</option>
                    <option value="ano" {{ request('periodo') == 'ano' ? 'selected' : '' }}>Este Ano</option>
                    <option value="custom" {{ request('periodo') == 'custom' ? 'selected' : '' }}>Personalizado</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Data Início</label>
                <input type="date" name="data_inicio" value="{{ request('data_inicio') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-barber-500 focus:ring-barber-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Data Fim</label>
                <input type="date" name="data_fim" value="{{ request('data_fim') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-barber-500 focus:ring-barber-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
                <select name="tipo" class="w-full border-gray-300 rounded-md shadow-sm focus:border-barber-500 focus:ring-barber-500">
                    <option value="">Todos</option>
                    <option value="receita" {{ request('tipo') == 'receita' ? 'selected' : '' }}>Receita</option>
                    <option value="despesa" {{ request('tipo') == 'despesa' ? 'selected' : '' }}>Despesa</option>
                </select>
            </div>
            <div class="md:col-span-4 flex gap-2">
                <button type="submit" class="bg-barber-600 text-white px-4 py-2 rounded-md hover:bg-barber-700 transition-colors">
                    Filtrar
                </button>
                <a href="{{ route('financeiro.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 transition-colors">
                    Limpar
                </a>
            </div>
        </form>
    </div>

    <!-- Indicadores Principais -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Receita Total</p>
                    <p class="text-2xl font-bold text-gray-900">R$ {{ number_format($receitaTotal, 2, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Despesas</p>
                    <p class="text-2xl font-bold text-gray-900">R$ {{ number_format($despesas, 2, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Lucro Líquido</p>
                    <p class="text-2xl font-bold text-gray-900">R$ {{ number_format($lucroLiquido, 2, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Meta do Mês</p>
                    <p class="text-2xl font-bold text-gray-900">R$ {{ number_format($metaMes, 2, ',', '.') }}</p>
                    <div class="mt-2">
                        <div class="flex items-center">
                            <div class="flex-1 bg-gray-200 rounded-full h-2">
                                <div class="bg-yellow-500 h-2 rounded-full" style="width: {{ $progressoMeta }}%"></div>
                            </div>
                            <span class="ml-2 text-sm text-gray-600">{{ number_format($progressoMeta, 1) }}%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Gráfico de Receita -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Receita por Mês</h3>
            <canvas id="receitaChart" width="400" height="200"></canvas>
        </div>

        <!-- Gráfico de Metas -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Progresso das Metas</h3>
            <canvas id="metasChart" width="400" height="200"></canvas>
        </div>
    </div>

    <!-- Metas de Crescimento -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Metas de Crescimento</h3>
            <button onclick="openMetaModal()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors text-sm">
                + Nova Meta
            </button>
        </div>

        <div class="space-y-4">
            @forelse($metas as $meta)
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-900">{{ $meta->nome }}</h4>
                        <p class="text-sm text-gray-600">{{ $meta->descricao }}</p>
                        <div class="mt-2">
                            <div class="flex items-center">
                                <div class="flex-1 bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-500 h-2 rounded-full" style="width: {{ min(100, ($meta->valor_atual / $meta->valor_meta) * 100) }}%"></div>
                                </div>
                                <span class="ml-2 text-sm text-gray-600">{{ number_format(($meta->valor_atual / $meta->valor_meta) * 100, 1) }}%</span>
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-600">R$ {{ number_format($meta->valor_atual, 2, ',', '.') }} / R$ {{ number_format($meta->valor_meta, 2, ',', '.') }}</p>
                        <p class="text-xs text-gray-500">Prazo: {{ \Carbon\Carbon::parse($meta->data_limite)->format('d/m/Y') }}</p>
                    </div>
                </div>
            @empty
                <div class="text-center py-8">
                    <p class="text-gray-500">Nenhuma meta cadastrada</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Transações Recentes -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Transações Recentes</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($transacoes as $transacao)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ \Carbon\Carbon::parse($transacao->data)->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $transacao->descricao }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $transacao->tipo == 'receita' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($transacao->tipo) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $transacao->tipo == 'receita' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $transacao->tipo == 'receita' ? '+' : '-' }}R$ {{ number_format($transacao->valor, 2, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    {{ $transacao->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                Nenhuma transação encontrada
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para Nova Meta -->
<div id="metaModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-6 border w-full max-w-md shadow-lg rounded-lg bg-white">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-semibold text-gray-900">Nova Meta de Crescimento</h3>
            <button onclick="closeMetaModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <form class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nome da Meta <span class="text-red-500">*</span></label>
                <input type="text" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-barber-500 focus:ring-barber-500" placeholder="Ex: Aumentar receita em 20%">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Descrição</label>
                <textarea class="w-full border-gray-300 rounded-lg shadow-sm focus:border-barber-500 focus:ring-barber-500" rows="3" placeholder="Descreva a meta..."></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Valor da Meta (R$) <span class="text-red-500">*</span></label>
                <input type="number" step="0.01" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-barber-500 focus:ring-barber-500" placeholder="0,00">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Data Limite <span class="text-red-500">*</span></label>
                <input type="date" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-barber-500 focus:ring-barber-500">
            </div>

            <div class="flex justify-end space-x-3 pt-4">
                <button type="button" onclick="closeMetaModal()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition-colors">
                    Cancelar
                </button>
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors shadow-sm">
                    Salvar Meta
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal para Nova Transação -->
<div id="transacaoModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-6 border w-full max-w-md shadow-lg rounded-lg bg-white">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-semibold text-gray-900">Nova Transação</h3>
            <button onclick="closeTransacaoModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <form class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo <span class="text-red-500">*</span></label>
                <select required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-barber-500 focus:ring-barber-500">
                    <option value="">Selecione o tipo</option>
                    <option value="receita">Receita</option>
                    <option value="despesa">Despesa</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Descrição <span class="text-red-500">*</span></label>
                <input type="text" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-barber-500 focus:ring-barber-500" placeholder="Ex: Corte de cabelo">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Valor (R$) <span class="text-red-500">*</span></label>
                <input type="number" step="0.01" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-barber-500 focus:ring-barber-500" placeholder="0,00">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Data <span class="text-red-500">*</span></label>
                <input type="date" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-barber-500 focus:ring-barber-500">
            </div>

            <div class="flex justify-end space-x-3 pt-4">
                <button type="button" onclick="closeTransacaoModal()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition-colors">
                    Cancelar
                </button>
                <button type="submit" class="bg-barber-600 text-white px-4 py-2 rounded-lg hover:bg-barber-700 transition-colors shadow-sm">
                    Salvar Transação
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Gráfico de Receita
const receitaCtx = document.getElementById('receitaChart').getContext('2d');
new Chart(receitaCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode(collect($receitaPorMes)->pluck('mes')) !!},
        datasets: [{
            label: 'Receita',
            data: {!! json_encode(collect($receitaPorMes)->pluck('receita')) !!},
            borderColor: '#c96f1f',
            backgroundColor: 'rgba(201, 111, 31, 0.1)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        }
    }
});

// Gráfico de Metas
const metasCtx = document.getElementById('metasChart').getContext('2d');
new Chart(metasCtx, {
    type: 'doughnut',
    data: {
        labels: ['Concluído', 'Em Andamento', 'Pendente'],
        datasets: [{
            data: [60, 30, 10],
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

// Modais
function openMetaModal() {
    document.getElementById('metaModal').classList.remove('hidden');
}

function closeMetaModal() {
    document.getElementById('metaModal').classList.add('hidden');
}

function openTransacaoModal() {
    document.getElementById('transacaoModal').classList.remove('hidden');
}

function closeTransacaoModal() {
    document.getElementById('transacaoModal').classList.add('hidden');
}

// Fechar modais ao clicar fora
document.getElementById('metaModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeMetaModal();
    }
});

document.getElementById('transacaoModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeTransacaoModal();
    }
});
</script>
@endsection
