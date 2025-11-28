@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Editar Usuário</h1>
            <p class="text-gray-600 mt-1">Atualize as informações do usuário</p>
        </div>
        <a href="{{ route('admin.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition-colors shadow-sm">
            Voltar
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form action="{{ route('admin.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Informações Básicas -->
                <div class="space-y-4">
                    <h3 class="text-lg font-medium text-gray-900">Informações Básicas</h3>

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nome Completo</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-barber-500 focus:ring-barber-500 @error('name') border-red-300 @enderror"
                               required>
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">E-mail</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
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
                            <option value="barber" {{ old('role', $user->role) == 'barber' ? 'selected' : '' }}>Barbeiro</option>
                            <option value="owner" {{ old('role', $user->role) == 'owner' ? 'selected' : '' }}>Proprietário</option>
                        </select>
                        @error('role')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="date_of_birth" class="block text-sm font-medium text-gray-700">Data de Nascimento</label>
                        <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', $user->date_of_birth ? \Carbon\Carbon::parse($user->date_of_birth)->format('Y-m-d') : '') }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-barber-500 focus:ring-barber-500 @error('date_of_birth') border-red-300 @enderror">
                        @error('date_of_birth')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">Telefone</label>
                            <input type="tel" name="phone" id="phone" value="{{ old('phone', $user->phone) }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-barber-500 focus:ring-barber-500 @error('phone') border-red-300 @enderror" placeholder="(99) 99999-9999">
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Senha -->
                <div class="space-y-4">
                    <h3 class="text-lg font-medium text-gray-900">Segurança</h3>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Nova Senha</label>
                        <input type="password" id="password" name="password"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-barber-500 focus:ring-barber-500 @error('password') border-red-300 @enderror">
                        <p class="mt-1 text-sm text-gray-500">Deixe em branco para manter a senha atual</p>
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar Nova Senha</label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-barber-500 focus:ring-barber-500">
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800">Informação</h3>
                                <div class="mt-2 text-sm text-blue-700">
                                    <p>• Cadastrado em: {{ $user->created_at->format('d/m/Y H:i') }}</p>
                                    <p>• Última atualização: {{ $user->updated_at->format('d/m/Y H:i') }}</p>
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
                    Atualizar Usuário
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
