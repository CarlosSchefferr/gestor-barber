<x-app-layout>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Página: título estilizado, afastado da navbar -->
            <div class="mt-6 mb-6">
                <x-page-title :title="'Painel'">
                    <x-slot name="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h7v7H3zM14 3h7v7h-7zM14 14h7v7h-7zM3 14h7v7H3z" />
                        </svg>
                    </x-slot>
                </x-page-title>
            </div>
            <div class="grid grid-cols-4 gap-6">
                <div class="col-span-3">
                    <div class="grid grid-cols-3 gap-6">
                        <x-barber-card>
                            <h3 class="text-sm font-medium text-gray-500">Total de Agendamentos</h3>
                            <p class="text-3xl font-bold mt-2">{{ $total ?? 0 }}</p>
                            <p class="text-sm text-gray-400 mt-1">Desde o início</p>
                        </x-barber-card>

                        <x-barber-card>
                            <h3 class="text-sm font-medium text-gray-500">Agendamentos Hoje</h3>
                            <p class="text-3xl font-bold mt-2">{{ $today ?? 0 }}</p>
                            <p class="text-sm text-gray-400 mt-1">No dia de hoje</p>
                        </x-barber-card>

                        <x-barber-card>
                            <h3 class="text-sm font-medium text-gray-500">Clientes Inativos</h3>
                            <p class="text-3xl font-bold mt-2">{{ $clientesInativos ?? 0 }}</p>
                            <p class="text-sm text-gray-400 mt-1">Sem atendimento há &gt;30 dias</p>
                        </x-barber-card>
                    </div>

                    <div class="mt-6">
                        <x-barber-card>
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold">Últimos Agendamentos</h3>
                                    <p class="text-sm text-gray-400">Visão rápida dos agendamentos recentes</p>
                                </div>
                                <a href="{{ route('agendamentos.index') }}" class="text-sm text-barber-500 hover:underline">Ver todos</a>
                            </div>

                            <div class="mt-4">
                                <table class="min-w-full text-sm">
                                    <thead>
                                        <tr>
                                            <th class="text-left text-gray-500">Data</th>
                                            <th class="text-left text-gray-500">Cliente</th>
                                            <th class="text-left text-gray-500">Barbeiro</th>
                                            <th class="text-right text-gray-500">Preço</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach(App\Models\Agendamento::latest()->take(6)->get() as $a)
                                        <tr class="border-t">
                                            <td class="py-2">{{ $a->starts_at->format('d/m H:i') }}</td>
                                            <td class="py-2">{{ $a->cliente->nome }}</td>
                                            <td class="py-2">{{ $a->barbeiro->name }}</td>
                                            <td class="py-2 text-right">R$ {{ number_format($a->price ?? 0, 2, ',', '.') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </x-barber-card>
                    </div>
                </div>

                <aside class="col-span-1">
                    @php
                        // cálculo simples da receita do dia — pode ser movido para o controller se preferir
                        $receitaHoje = App\Models\Agendamento::whereDate('starts_at', now()->toDateString())->sum('price');
                    @endphp

                    <x-barber-card>
                        <h3 class="text-lg font-semibold">Receita Hoje</h3>
                        <div class="mt-3">
                            <div class="text-2xl font-bold">R$ {{ number_format($receitaHoje ?? 0, 2, ',', '.') }}</div>
                            <div class="text-sm text-gray-400 mt-1">Soma dos atendimentos do dia</div>
                        </div>
                    </x-barber-card>

                    <x-barber-card class="mt-6">
                        <h3 class="text-lg font-semibold">Resumo Financeiro</h3>
                        <p class="mt-2 text-sm text-gray-400">Sem dados ainda — implemente o financeiro para ver o resumo completo.</p>
                    </x-barber-card>
                </aside>
            </div>
        </div>
    </div>
</x-app-layout>
