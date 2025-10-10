@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4">
    <h1 class="text-2xl font-bold mb-4">Editar Agendamento</h1>

    <form action="{{ route('agendamentos.update', $agendamento) }}" method="POST">
        @csrf
        @method('PUT')
        @include('agendamentos._form')
        <div class="mt-4">
            <button class="bg-blue-500 text-white px-4 py-2 rounded">Atualizar</button>
            <a href="{{ route('agendamentos.index') }}" class="ml-2 text-gray-600">Cancelar</a>
        </div>
    </form>
</div>
@endsection
