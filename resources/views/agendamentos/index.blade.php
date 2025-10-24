@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Agendamentos</h1>
            <p class="text-gray-600 mt-1">Gerencie todos os agendamentos</p>
        </div>
        <a href="{{ route('agendamentos.create') }}" class="bg-barber-600 text-white px-4 py-2 rounded-lg hover:bg-barber-700 transition-colors shadow-sm">
            + Novo Agendamento
        </a>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Cliente</label>
                <select name="cliente_id" class="w-full border-gray-300 rounded-md shadow-sm focus:border-barber-500 focus:ring-barber-500">
                    <option value="">Todos os clientes</option>
                    @foreach($clientes as $cliente)
                        <option value="{{ $cliente->id }}" {{ request('cliente_id') == $cliente->id ? 'selected' : '' }}>
                            {{ $cliente->nome }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Barbeiro</label>
                <select name="barbeiro_id" class="w-full border-gray-300 rounded-md shadow-sm focus:border-barber-500 focus:ring-barber-500">
                    <option value="">Todos os barbeiros</option>
                    @foreach($barbeiros as $barbeiro)
                        <option value="{{ $barbeiro->id }}" {{ request('barbeiro_id') == $barbeiro->id ? 'selected' : '' }}>
                            {{ $barbeiro->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Data Início</label>
                <input type="date" name="from" value="{{ request('from') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-barber-500 focus:ring-barber-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Data Fim</label>
                <input type="date" name="to" value="{{ request('to') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-barber-500 focus:ring-barber-500">
            </div>

            <div class="col-span-full flex justify-end space-x-3">
                <button type="submit" class="bg-barber-600 text-white px-4 py-2 rounded-md hover:bg-barber-700 transition-colors">
                    Filtrar
                </button>
                <a href="{{ route('agendamentos.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition-colors">
                    Limpar
                </a>
            </div>
        </form>
    </div>

    <!-- Tabela de Agendamentos -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Data/Hora
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Cliente
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Serviço
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Barbeiro
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Valor
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Ações
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($agendamentos as $agendamento)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $agendamento->starts_at->format('d/m/Y') }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ $agendamento->starts_at->format('H:i') }}
                                    @if($agendamento->ends_at)
                                        - {{ $agendamento->ends_at->format('H:i') }}
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $agendamento->cliente->nome }}</div>
                                @if($agendamento->cliente->telefone)
                                    <div class="text-sm text-gray-500">{{ $agendamento->cliente->telefone }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-barber-100 text-barber-800">
                                    {{ $agendamento->servico }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $agendamento->barbeiro->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">
                                    R$ {{ number_format($agendamento->price ?? 0, 2, ',', '.') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="{{ route('agendamentos.edit', $agendamento) }}"
                                       class="text-barber-600 hover:text-barber-900 transition-colors text-sm">
                                        Editar
                                    </a>
                                    <button onclick="confirmDelete({{ $agendamento->id }})"
                                            class="text-red-600 hover:text-red-900 transition-colors text-sm">
                                        Remover
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum agendamento encontrado</h3>
                                    <p class="text-gray-500 mb-4">Comece criando um novo agendamento.</p>
                                    <a href="{{ route('agendamentos.create') }}" class="bg-barber-600 text-white px-4 py-2 rounded-md hover:bg-barber-700 transition-colors">
                                        + Novo Agendamento
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        @if($agendamentos->hasPages())
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $agendamentos->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal de Confirmação -->
<div id="confirmModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mt-4">Confirmar Exclusão</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">
                    Tem certeza que deseja excluir este agendamento? Esta ação não pode ser desfeita.
                </p>
            </div>
            <div class="items-center px-4 py-3">
                <button id="confirmDeleteBtn" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition-colors mr-2">
                    Sim, Excluir
                </button>
                <button onclick="closeModal()" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 transition-colors">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let agendamentoIdToDelete = null;

function confirmDelete(id) {
    agendamentoIdToDelete = id;
    document.getElementById('confirmModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('confirmModal').classList.add('hidden');
    agendamentoIdToDelete = null;
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (agendamentoIdToDelete) {
        // Criar form para enviar DELETE
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/agendamentos/${agendamentoIdToDelete}`;

        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';

        const tokenField = document.createElement('input');
        tokenField.type = 'hidden';
        tokenField.name = '_token';
        tokenField.value = '{{ csrf_token() }}';

        form.appendChild(methodField);
        form.appendChild(tokenField);
        document.body.appendChild(form);
        form.submit();
    }
});

// Fechar modal ao clicar fora
document.getElementById('confirmModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>
@endsection
