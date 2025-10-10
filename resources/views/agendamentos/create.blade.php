@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4">
    <h1 class="text-2xl font-bold mb-4">Novo Agendamento</h1>

    <form action="{{ route('agendamentos.store') }}" method="POST">
        @csrf
        @include('agendamentos._form')
        <div class="mt-4">
            <button class="bg-green-500 text-white px-4 py-2 rounded">Salvar</button>
            <a href="{{ route('agendamentos.index') }}" class="ml-2 text-gray-600">Cancelar</a>
        </div>
    </form>
</div>
@endsection
