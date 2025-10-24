@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Novo Usuário</h1>
            <p class="text-gray-600 mt-1">Adicione um novo usuário ao sistema</p>
        </div>
        <a href="{{ route('admin.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition-colors shadow-sm">
            Voltar
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form action="{{ route('admin.store') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Informações Básicas -->
                <div class="space-y-4">
                    <h3 class="text-lg font-medium text-gray-900">Informações Básicas</h3>
                    
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nome Completo</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-barber-500 focus:ring-barber-500 @error('name') border-red-300 @enderror" 
                               required>
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">E-mail</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-barber-500 focus:ring-barber-500 @error('email') border-red-300 @enderror" 
                               required>
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700">Cargo</label>
                        <select id="role" name="role" 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-barber-500 focus:ring-barber-500 @error('role') border-red-300 @enderror" 
                                required>
                            <option value="">Selecione um cargo</option>
                            <option value="barber" {{ old('role') == 'barber' ? 'selected' : '' }}>Barbeiro</option>
                            <option value="owner" {{ old('role') == 'owner' ? 'selected' : '' }}>Proprietário</option>
                        </select>
                        @error('role')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Senha -->
                <div class="space-y-4">
                    <h3 class="text-lg font-medium text-gray-900">Segurança</h3>
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Senha</label>
                        <input type="password" id="password" name="password" 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-barber-500 focus:ring-barber-500 @error('password') border-red-300 @enderror" 
                               required>
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar Senha</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-barber-500 focus:ring-barber-500" 
                               required>
                    </div>

                    <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">Atenção</h3>
                                <div class="mt-2 text-sm text-yellow-700">
                                    <p>• Proprietários têm acesso total ao sistema</p>
                                    <p>• Barbeiros podem apenas gerenciar seus próprios agendamentos</p>
                                    <p>• A senha deve ter pelo menos 8 caracteres</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-8">
                <a href="{{ route('admin.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition-colors">
                    Cancelar
                </a>
                <button type="submit" class="bg-barber-600 text-white px-4 py-2 rounded-lg hover:bg-barber-700 transition-colors">
                    Criar Usuário
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
