@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $user->name }}</h1>
            <p class="text-gray-600 mt-1">Detalhes do usuário</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.edit', $user) }}" class="bg-barber-600 text-white px-4 py-2 rounded-lg hover:bg-barber-700 transition-colors shadow-sm">
                Editar
            </a>
            <a href="{{ route('admin.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition-colors shadow-sm">
                Voltar
            </a>
        </div>
    </div>

    <!-- Informações do Usuário -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Perfil -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0 h-16 w-16">
                    <div class="h-16 w-16 rounded-full bg-barber-100 flex items-center justify-center">
                        <span class="text-xl font-medium text-barber-600">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </span>
                    </div>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-medium text-gray-900">{{ $user->name }}</h3>
                    <p class="text-sm text-gray-500">{{ $user->email }}</p>
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $user->role === 'owner' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                        {{ $user->role === 'owner' ? 'Proprietário' : 'Barbeiro' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Estatísticas -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Estatísticas</h3>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Total de Agendamentos</span>
                    <span class="text-sm font-medium text-gray-900">{{ $estatisticasUsuario['total_agendamentos'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Agendamentos Hoje</span>
                    <span class="text-sm font-medium text-gray-900">{{ $estatisticasUsuario['agendamentos_hoje'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Receita Total</span>
                    <span class="text-sm font-medium text-gray-900">R$ {{ number_format($estatisticasUsuario['receita_total'], 2, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Informações de Cadastro -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Informações</h3>
            <div class="space-y-3">
                <div>
                    <span class="text-sm text-gray-500">Cadastrado em</span>
                    <p class="text-sm font-medium text-gray-900">{{ $user->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <div>
                    <span class="text-sm text-gray-500">Última atualização</span>
                    <p class="text-sm font-medium text-gray-900">{{ $user->updated_at->format('d/m/Y H:i') }}</p>
                </div>
                @if($estatisticasUsuario['ultimo_agendamento'])
                    <div>
                        <span class="text-sm text-gray-500">Último agendamento</span>
                        <p class="text-sm font-medium text-gray-900">{{ $estatisticasUsuario['ultimo_agendamento']->starts_at->format('d/m/Y H:i') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Agendamentos Recentes -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Agendamentos Recentes</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data/Hora</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Serviço</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($agendamentos as $agendamento)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $agendamento->starts_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $agendamento->cliente->nome }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $agendamento->servico ?: 'Não especificado' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $agendamento->price ? 'R$ ' . number_format($agendamento->price, 2, ',', '.') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $agendamento->status === 'agendado' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                    {{ ucfirst($agendamento->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                Nenhum agendamento encontrado
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($agendamentos->hasPages())
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $agendamentos->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
