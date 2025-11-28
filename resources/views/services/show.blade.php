@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-bold mb-4">{{ $service->name }}</h1>

    <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
        <p><strong>Descrição:</strong></p>
        <div class="prose max-w-none mt-2">{!! nl2br(e($service->description ?? '-')) !!}</div>

        <div class="mt-6">
            <a href="{{ route('admin.services.edit', $service) }}" class="px-4 py-2 bg-barber-600 text-white rounded-md">Editar</a>
            <a href="{{ route('admin.services.index') }}" class="ml-2 px-4 py-2 bg-gray-100 rounded-md">Voltar</a>
        </div>
    </div>
</div>
@endsection
