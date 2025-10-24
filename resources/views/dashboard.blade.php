<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header do Dashboard -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Painel de Controle</h1>
                <p class="text-gray-600 mt-2">Visão geral do seu negócio</p>
            </div>

            <!-- Cards de Estatísticas -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total de Agendamentos -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-barber-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-barber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Agendamentos</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $total ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                <!-- Agendamentos Hoje -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Hoje</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $today ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                <!-- Clientes Ativos -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Clientes Ativos</p>
                            <p class="text-2xl font-bold text-gray-900">{{ App\Models\Cliente::where('last_appointment_at', '>=', now()->subDays(30))->count() }}</p>
                        </div>
                    </div>
                </div>

                <!-- Receita Hoje -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Receita Hoje</p>
                            <p class="text-2xl font-bold text-gray-900">R$ {{ number_format(App\Models\Agendamento::whereDate('starts_at', now()->toDateString())->sum('price') ?? 0, 2, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráficos e Estatísticas -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <!-- Gráfico de Agendamentos por Dia -->
                <div class="lg:col-span-2 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Agendamentos dos Últimos 7 Dias</h3>
                        <div class="flex space-x-2">
                            <button id="chart-toggle" class="text-sm text-barber-600 hover:text-barber-800 bg-barber-50 px-3 py-1 rounded-md">Ver Gráfico</button>
                        </div>
                    </div>
                    <div class="h-64">
                        <canvas id="agendamentosChart"></canvas>
                    </div>
                </div>

                <!-- Gráfico de Serviços -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Serviços Esta Semana</h3>
                    <div class="h-64">
                        <canvas id="servicosChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Cards de Serviços Populares -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                @forelse($servicosSemana->take(6) as $servico)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <h4 class="text-lg font-semibold text-gray-900">{{ $servico->servico }}</h4>
                                <p class="text-sm text-gray-500 mt-1">{{ $servico->quantidade }} atendimentos</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xl font-bold text-barber-600">R$ {{ number_format($servico->receita, 2, ',', '.') }}</p>
                                <p class="text-xs text-gray-500">receita</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-barber-500 h-2 rounded-full" style="width: {{ ($servico->quantidade / $servicosSemana->max('quantidade')) * 100 }}%"></div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                        <p class="text-gray-500">Nenhum serviço registrado esta semana</p>
                        <a href="{{ route('agendamentos.create') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-barber-600 text-white rounded-md hover:bg-barber-700 transition-colors">
                            + Criar Primeiro Agendamento
                        </a>
                    </div>
                @endforelse
            </div>

            <!-- Resumo Financeiro -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
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
                            <p class="text-sm font-medium text-gray-500">Receita Hoje</p>
                            <p class="text-2xl font-bold text-gray-900">R$ {{ number_format(App\Models\Agendamento::whereDate('starts_at', now()->toDateString())->sum('price') ?? 0, 2, ',', '.') }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Receita Esta Semana</p>
                            <p class="text-2xl font-bold text-gray-900">R$ {{ number_format(App\Models\Agendamento::whereBetween('starts_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()])->sum('price') ?? 0, 2, ',', '.') }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Ticket Médio</p>
                            <p class="text-2xl font-bold text-gray-900">R$ {{ number_format(App\Models\Agendamento::whereNotNull('price')->avg('price') ?? 0, 2, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Agendamentos de Hoje -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Agendamentos de Hoje</h3>
                        <a href="{{ route('agendamentos.index') }}" class="text-sm text-barber-600 hover:text-barber-800">
                            Ver todos →
                        </a>
                    </div>
                </div>

                @if($agendamentosHoje->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Horário</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Serviço</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Barbeiro</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($agendamentosHoje as $agendamento)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $agendamento->starts_at->format('H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $agendamento->cliente->nome }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-barber-100 text-barber-800">
                                                {{ $agendamento->servico }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $agendamento->barbeiro->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-gray-900">
                                            R$ {{ number_format($agendamento->price ?? 0, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-6 py-12 text-center">
                        <p class="text-gray-500">Nenhum agendamento para hoje</p>
                        <a href="{{ route('agendamentos.create') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-barber-600 text-white rounded-md hover:bg-barber-700 transition-colors">
                            + Novo Agendamento
                        </a>
                    </div>
                @endif
            </div>

            <!-- Ações Rápidas -->
            <div class="mt-8 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Ações Rápidas</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <a href="{{ route('agendamentos.create') }}" class="flex items-center p-4 bg-barber-50 rounded-lg hover:bg-barber-100 transition-colors">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-barber-100 rounded-lg flex items-center justify-center">
                                <span class="text-barber-600 font-semibold">📅</span>
                            </div>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">Novo Agendamento</p>
                            <p class="text-xs text-gray-500">Criar agendamento</p>
                        </div>
                    </a>

                    <a href="{{ route('clientes.create') }}" class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                <span class="text-blue-600 font-semibold">👤</span>
                            </div>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">Novo Cliente</p>
                            <p class="text-xs text-gray-500">Cadastrar cliente</p>
                        </div>
                    </a>

                    <a href="{{ route('agendamentos.index') }}" class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                <span class="text-green-600 font-semibold">📋</span>
                            </div>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">Ver Agendamentos</p>
                            <p class="text-xs text-gray-500">Lista completa</p>
                        </div>
                    </a>

                    <a href="{{ route('clientes.index') }}" class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                <span class="text-purple-600 font-semibold">👥</span>
                            </div>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">Ver Clientes</p>
                            <p class="text-xs text-gray-500">Lista completa</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts para os gráficos -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Dados para os gráficos
        const agendamentosData = @json($agendamentosPorDia);
        const servicosData = @json($servicosSemana);

        // Cores para os gráficos
        const colors = [
            '#c96f1f', '#db934c', '#e6b97f', '#efd3ab', '#f8ebd9',
            '#6b0f0f', '#0b0b0b', '#4f2a0a', '#7b3b0f', '#a45317'
        ];

        // Gráfico de linha - Agendamentos por dia
        const ctxLine = document.getElementById('agendamentosChart').getContext('2d');
        const lineChart = new Chart(ctxLine, {
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
                    backgroundColor: 'rgba(201, 111, 31, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#c96f1f',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                elements: {
                    point: {
                        hoverRadius: 8
                    }
                }
            }
        });

        // Gráfico de pizza - Serviços
        const ctxPie = document.getElementById('servicosChart').getContext('2d');
        const pieChart = new Chart(ctxPie, {
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
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                },
                cutout: '60%'
            }
        });

        // Toggle para mostrar/ocultar gráfico
        document.getElementById('chart-toggle').addEventListener('click', function() {
            const canvas = document.getElementById('agendamentosChart');
            const button = this;

            if (canvas.style.display === 'none') {
                canvas.style.display = 'block';
                button.textContent = 'Ocultar Gráfico';
            } else {
                canvas.style.display = 'none';
                button.textContent = 'Ver Gráfico';
            }
        });
    </script>
</x-app-layout>
