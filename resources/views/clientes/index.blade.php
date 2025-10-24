@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Clientes</h1>
            <p class="text-gray-600 mt-1">Gerencie todos os clientes</p>
        </div>
        <a href="{{ route('clientes.create') }}" class="bg-barber-600 text-white px-4 py-2 rounded-lg hover:bg-barber-700 transition-colors shadow-sm">
            + Novo Cliente
        </a>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Buscar por nome</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Digite o nome do cliente..."
                       class="w-full border-gray-300 rounded-md shadow-sm focus:border-barber-500 focus:ring-barber-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Ordenar por</label>
                <select name="sort" class="w-full border-gray-300 rounded-md shadow-sm focus:border-barber-500 focus:ring-barber-500">
                    <option value="nome" {{ request('sort') == 'nome' ? 'selected' : '' }}>Nome</option>
                    <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Data de cadastro</option>
                    <option value="last_appointment_at" {{ request('sort') == 'last_appointment_at' ? 'selected' : '' }}>Último atendimento</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" class="w-full border-gray-300 rounded-md shadow-sm focus:border-barber-500 focus:ring-barber-500">
                    <option value="">Todos</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Ativos</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inativos</option>
                </select>
            </div>

            <div class="col-span-full flex justify-end space-x-3">
                <button type="submit" class="bg-barber-600 text-white px-4 py-2 rounded-md hover:bg-barber-700 transition-colors">
                    Filtrar
                </button>
                <a href="{{ route('clientes.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition-colors">
                    Limpar
                </a>
            </div>
        </form>
    </div>

    <!-- Tabela de Clientes -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Cliente
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Contato
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Último Atendimento
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Ações
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($clientes as $cliente)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-barber-100 flex items-center justify-center">
                                            <span class="text-sm font-medium text-barber-800">
                                                {{ strtoupper(substr($cliente->nome, 0, 2)) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $cliente->nome }}</div>
                                        <div class="text-sm text-gray-500">ID: {{ $cliente->id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($cliente->email)
                                    <div class="text-sm text-gray-900">{{ $cliente->email }}</div>
                                @endif
                                @if($cliente->telefone)
                                    <div class="text-sm text-gray-500">{{ $cliente->telefone }}</div>
                                @endif
                                @if(!$cliente->email && !$cliente->telefone)
                                    <span class="text-sm text-gray-400">Sem contato</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($cliente->last_appointment_at)
                                    <div class="text-sm text-gray-900">
                                        {{ \Carbon\Carbon::parse($cliente->last_appointment_at)->format('d/m/Y') }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ \Carbon\Carbon::parse($cliente->last_appointment_at)->diffForHumans() }}
                                    </div>
                                @else
                                    <span class="text-sm text-gray-400">Nunca atendido</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($cliente->last_appointment_at && \Carbon\Carbon::parse($cliente->last_appointment_at)->diffInDays(now()) <= 30)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Ativo
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Inativo
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="{{ route('clientes.edit', $cliente) }}"
                                       class="text-barber-600 hover:text-barber-900 transition-colors text-sm">
                                        Editar
                                    </a>
                                    <a href="{{ route('agendamentos.create', ['cliente_id' => $cliente->id]) }}"
                                       class="text-green-600 hover:text-green-900 transition-colors text-sm">
                                        Agendar
                                    </a>
                                    <button onclick="confirmDelete({{ $cliente->id }})"
                                            class="text-red-600 hover:text-red-900 transition-colors text-sm">
                                        Remover
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum cliente encontrado</h3>
                                    <p class="text-gray-500 mb-4">Comece cadastrando um novo cliente.</p>
                                    <a href="{{ route('clientes.create') }}" class="bg-barber-600 text-white px-4 py-2 rounded-md hover:bg-barber-700 transition-colors">
                                        + Novo Cliente
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        @if($clientes->hasPages())
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $clientes->links() }}
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
                    Tem certeza que deseja excluir este cliente? Esta ação não pode ser desfeita.
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
let clienteIdToDelete = null;

function confirmDelete(id) {
    clienteIdToDelete = id;
    document.getElementById('confirmModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('confirmModal').classList.add('hidden');
    clienteIdToDelete = null;
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (clienteIdToDelete) {
        // Criar form para enviar DELETE
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/clientes/${clienteIdToDelete}`;

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
