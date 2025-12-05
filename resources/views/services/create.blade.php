@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Novo Serviço</h1>
            <p class="text-gray-600 mt-1">Cadastre um novo serviço</p>
        </div>
        <a href="{{ route('admin.services.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition-colors shadow-sm">
            ← Voltar
        </a>
    </div>

    <div class="max-w-4xl mx-auto">
        <form action="{{ route('admin.services.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informações</h3>

                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nome <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-barber-500 focus:ring-barber-500 @error('name') border-red-300 @enderror">
                        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Descrição</label>
                        <textarea name="description" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-barber-500 focus:ring-barber-500 @error('description') border-red-300 @enderror">{{ old('description') }}</textarea>
                        @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Preço (R$)</label>
                        <input type="number" step="0.01" name="price" value="{{ old('price', '0.00') }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-barber-500 focus:ring-barber-500 @error('price') border-red-300 @enderror">
                        @error('price')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Comissão (R$)</label>
                        <input type="number" step="0.01" name="commission" value="{{ old('commission', '0.00') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-barber-500 focus:ring-barber-500 @error('commission') border-red-300 @enderror">
                        @error('commission')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-4 pt-6">
                <a href="{{ route('admin.services.index') }}" class="bg-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-400 transition-colors">Cancelar</a>
                <button type="submit" class="bg-barber-600 text-white px-6 py-3 rounded-lg hover:bg-barber-700 transition-colors shadow-sm">Criar Serviço</button>
            </div>
        </form>
    </div>
</div>
@push('scripts')
@endpush
@endsection
