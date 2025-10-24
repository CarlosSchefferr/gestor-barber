@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Editar Cliente</h1>
            <p class="text-gray-600 mt-1">Atualize as informações do cliente</p>
        </div>
        <a href="{{ route('clientes.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition-colors shadow-sm">
            ← Voltar
        </a>
    </div>

    <div class="max-w-3xl mx-auto">
        <form action="{{ route('clientes.update', $cliente) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Informações Pessoais -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    Informações Pessoais
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Nome Completo <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nome" required
                               value="{{ old('nome', $cliente->nome) }}"
                               placeholder="Digite o nome completo do cliente"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-barber-500 focus:ring-barber-500 @error('nome') border-red-300 @enderror">
                        @error('nome')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Informações de Contato -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    Informações de Contato
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Email
                        </label>
                        <input type="email" name="email"
                               value="{{ old('email', $cliente->email) }}"
                               placeholder="cliente@exemplo.com"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-barber-500 focus:ring-barber-500 @error('email') border-red-300 @enderror">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Telefone
                        </label>
                        <input type="text" name="telefone"
                               value="{{ old('telefone', $cliente->telefone) }}"
                               placeholder="(11) 99999-9999"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-barber-500 focus:ring-barber-500 @error('telefone') border-red-300 @enderror">
                        @error('telefone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Observações -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    Observações
                </h3>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Observações sobre o Cliente
                    </label>
                    <textarea name="observacoes" rows="4"
                              placeholder="Informações adicionais sobre o cliente (preferências, alergias, etc.)..."
                              class="w-full border-gray-300 rounded-md shadow-sm focus:border-barber-500 focus:ring-barber-500 @error('observacoes') border-red-300 @enderror">{{ old('observacoes', $cliente->observacoes) }}</textarea>
                    @error('observacoes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">Ex: Preferências de corte, alergias, observações importantes</p>
                </div>
            </div>

            <!-- Botões de Ação -->
            <div class="flex justify-end space-x-4 pt-6">
                <a href="{{ route('clientes.index') }}"
                   class="bg-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-400 transition-colors">
                    Cancelar
                </a>
                <button type="submit"
                        class="bg-barber-600 text-white px-6 py-3 rounded-lg hover:bg-barber-700 transition-colors shadow-sm">
                    Atualizar Cliente
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
