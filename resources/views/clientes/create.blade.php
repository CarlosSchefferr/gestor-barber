@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4">
    <h1 class="text-2xl font-semibold mb-4">Novo Cliente</h1>

    <form action="{{ route('clientes.store') }}" method="POST">
        @csrf
        @include('clientes._form')
        <div class="mt-4">
            <button class="bg-barber-500 text-white px-4 py-2 rounded">Salvar</button>
            <a href="{{ route('clientes.index') }}" class="ml-2 text-gray-600">Cancelar</a>
        </div>
    </form>
</div>
@endsection
