@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4">
    <x-page-title :title="'Agendamentos'">
        <x-slot name="icon">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        </x-slot>
        <x-slot name="actions">
            <a href="{{ route('agendamentos.create') }}"><x-barber-button>+ Novo</x-barber-button></a>
        </x-slot>
    </x-page-title>

    <div class="grid gap-4">
        @foreach($agendamentos as $ag)
            <x-barber-card>
                <div class="flex justify-between items-center">
                    <div>
                        <div class="text-sm text-gray-500">{{ $ag->starts_at->format('d/m/Y H:i') }}</div>
                        <div class="text-lg font-semibold">{{ $ag->cliente->nome }} — <span class="text-sm font-normal">{{ $ag->servico }}</span></div>
                        <div class="text-sm text-gray-400">Barbeiro: {{ $ag->barbeiro->name }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-bold">R$ {{ number_format($ag->price ?? 0, 2, ',', '.') }}</div>
                        <div class="mt-2">
                            <a href="{{ route('agendamentos.edit', $ag) }}" class="text-barber-700">Editar</a>
                            <form action="{{ route('agendamentos.destroy', $ag) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600 ml-2" onclick="return confirm('Remover?')">Remover</button>
                            </form>
                        </div>
                    </div>
                </div>
            </x-barber-card>
        @endforeach
    </div>

    <div class="mt-4">
        {{ $agendamentos->links() }}
    </div>
</div>
@endsection
