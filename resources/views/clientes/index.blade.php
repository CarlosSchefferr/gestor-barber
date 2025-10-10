@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold">Clientes</h1>
        <a href="{{ route('clientes.create') }}"><x-barber-button class="bg-barber-500">+ Novo</x-barber-button></a>
    </div>

    <div class="grid gap-4">
        @foreach($clientes as $c)
            <x-barber-card>
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-lg font-semibold">{{ $c->nome }}</div>
                        <div class="text-sm text-gray-500">{{ $c->email ?? '—' }} · {{ $c->telefone ?? '—' }}</div>
                    </div>
                    <div class="text-right">
                        <a href="{{ route('clientes.edit', $c) }}" class="text-barber-700">Editar</a>
                        <form action="{{ route('clientes.destroy', $c) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button class="text-red-600 ml-2" onclick="return confirm('Remover?')">Remover</button>
                        </form>
                    </div>
                </div>
            </x-barber-card>
        @endforeach
    </div>

    <div class="mt-4">{{ $clientes->links() }}</div>
</div>
@endsection
