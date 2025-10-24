@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Configurações</h1>
            <p class="text-gray-600 mt-1">Gerencie suas preferências de conta e sistema</p>
        </div>
        <a href="{{ route('profile.edit') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition-colors shadow-sm">
            ← Voltar ao Perfil
        </a>
    </div>

    <div class="space-y-8">
        <!-- Segurança -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-gray-900">Segurança</h2>
            </div>
            @include('profile.partials.update-password-form')
        </div>


        <!-- Zona de Perigo -->
        <div class="bg-white rounded-lg shadow-sm border border-red-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-red-900">Zona de Perigo</h2>
            </div>
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</div>
@endsection
