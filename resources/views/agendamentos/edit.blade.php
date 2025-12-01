@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Editar Agendamento</h1>
            <p class="text-gray-600 mt-1">Atualize as informações do agendamento</p>
        </div>
        <a href="{{ route('agendamentos.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition-colors shadow-sm">
            ← Voltar
        </a>
    </div>

    <div class="max-w-4xl mx-auto">
        <form action="{{ route('agendamentos.update', $agendamento) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

            <!-- Informações do Cliente e Barbeiro -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    Informações Básicas
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Cliente <span class="text-red-500">*</span>
                        </label>
                        <select name="cliente_id" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-barber-500 focus:ring-barber-500 @error('cliente_id') border-red-300 @enderror">
                            <option value="">Selecione um cliente</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}" {{ old('cliente_id', $agendamento->cliente_id) == $cliente->id ? 'selected' : '' }}>
                                    {{ $cliente->nome }}
                                </option>
                            @endforeach
                        </select>
                        @error('cliente_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Barbeiro <span class="text-red-500">*</span>
                        </label>
                        <select name="barbeiro_id" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-barber-500 focus:ring-barber-500 @error('barbeiro_id') border-red-300 @enderror">
                            <option value="">Selecione um barbeiro</option>
                            @foreach($barbeiros as $barbeiro)
                                <option value="{{ $barbeiro->id }}" {{ old('barbeiro_id', $agendamento->barbeiro_id) == $barbeiro->id ? 'selected' : '' }}>
                                    {{ $barbeiro->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('barbeiro_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Data e Hora -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    Data e Horário
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Data e Hora de Início <span class="text-red-500">*</span>
                        </label>
                        <input type="datetime-local" name="starts_at" required
                               value="{{ old('starts_at', $agendamento->starts_at->format('Y-m-d\TH:i')) }}"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-barber-500 focus:ring-barber-500 @error('starts_at') border-red-300 @enderror">
                        @error('starts_at')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Data e Hora de Fim
                        </label>
                        <input type="datetime-local" name="ends_at"
                               value="{{ old('ends_at', $agendamento->ends_at ? $agendamento->ends_at->format('Y-m-d\TH:i') : '') }}"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-barber-500 focus:ring-barber-500 @error('ends_at') border-red-300 @enderror">
                        @error('ends_at')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Opcional - deixe em branco se não souber a duração</p>
                    </div>
                </div>
            </div>

            <!-- Serviço e Preço -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    Serviço e Valor
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Serviço <span class="text-red-500">*</span>
                        </label>
                        <select name="servico" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-barber-500 focus:ring-barber-500 @error('servico') border-red-300 @enderror">
                            <option value="">Selecione um serviço</option>
                            @foreach($services as $service)
                                <option value="{{ $service->name }}" {{ (old('servico', $agendamento->servico) == $service->name) ? 'selected' : '' }}>{{ $service->name }}</option>
                            @endforeach
                        </select>
                        @error('servico')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Preço (R$)
                        </label>
                        <input type="number" step="0.01" name="price"
                               value="{{ old('price', $agendamento->price) }}"
                               placeholder="0,00"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-barber-500 focus:ring-barber-500 @error('price') border-red-300 @enderror">
                        @error('price')
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
                        Observações Adicionais
                    </label>
                    <textarea name="observacoes" rows="4"
                              placeholder="Informações adicionais sobre o agendamento..."
                              class="w-full border-gray-300 rounded-md shadow-sm focus:border-barber-500 focus:ring-barber-500 @error('observacoes') border-red-300 @enderror">{{ old('observacoes', $agendamento->observacoes) }}</textarea>
                    @error('observacoes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Botões de Ação -->
            <div class="flex justify-end space-x-4 pt-6">
                <a href="{{ route('agendamentos.index') }}"
                   class="bg-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-400 transition-colors">
                    Cancelar
                </a>
                <button type="submit"
                        class="bg-barber-600 text-white px-6 py-3 rounded-lg hover:bg-barber-700 transition-colors shadow-sm">
                    Atualizar Agendamento
                </button>
            </div>
    </form>
    </div>
</div>
@endsection
