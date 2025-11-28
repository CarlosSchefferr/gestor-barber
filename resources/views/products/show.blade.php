<!-- products show -->
@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-bold mb-4">{{ $product->name }}</h1>

    <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
        <p><strong>Descrição:</strong></p>
        <div class="prose max-w-none mt-2">{!! nl2br(e($product->description ?? '-')) !!}</div>

        <p class="mt-4"><strong>Preço:</strong> R$ {{ number_format($product->price,2,',','.') }}</p>
        <p><strong>Quantidade:</strong> {{ $product->quantity }}</p>

        <div class="mt-6">
            <a href="{{ route('admin.products.edit', $product) }}" class="px-4 py-2 bg-barber-600 text-white rounded-md">Editar</a>
            <a href="{{ route('admin.products.index') }}" class="ml-2 px-4 py-2 bg-gray-100 rounded-md">Voltar</a>
        </div>
    </div>
</div>
@endsection
