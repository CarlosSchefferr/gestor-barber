@extends('layouts.app')

@section('content')
@php
    $inputClass = 'mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20';
    $cardClass = 'rounded-3xl border border-zinc-200 bg-white/95 shadow-sm';
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-6 {{ $cardClass }} px-6 py-6 sm:px-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500">Agenda</p>
                <h1 class="mt-1 text-2xl font-bold leading-tight text-zinc-900 sm:text-3xl">Editar Agendamento</h1>
                <p class="mt-1 text-sm text-zinc-500">Atualize as informacoes do agendamento</p>
            </div>
            <a href="{{ route('agendamentos.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-zinc-200 bg-white px-4 py-2 text-sm font-medium text-zinc-600 transition hover:bg-zinc-50 hover:border-zinc-300">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Voltar
            </a>
        </div>
    </div>

    <div class="max-w-4xl mx-auto">
        <form action="{{ route('agendamentos.update', $agendamento) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Cliente e Barbeiro -->
            <div class="{{ $cardClass }} overflow-hidden">
                <div class="border-b border-zinc-100 bg-zinc-50/60 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-barber-100">
                            <svg class="h-5 w-5 text-barber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-zinc-900">Cliente e Barbeiro</h3>
                            <p class="text-sm text-zinc-500">Selecione o cliente e o profissional</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label class="text-sm font-semibold text-zinc-700">
                                Cliente <span class="text-red-500">*</span>
                            </label>
                            <x-custom-select
                                name="cliente_id"
                                :options="$clientes->pluck('nome', 'id')->toArray()"
                                :value="old('cliente_id', $agendamento->cliente_id)"
                                placeholder="Selecione um cliente"
                                required
                            />
                            @error('cliente_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="text-sm font-semibold text-zinc-700">
                                Barbeiro <span class="text-red-500">*</span>
                            </label>
                            @if(auth()->check() && auth()->user()->isBarber())
                                <x-custom-select
                                    name="barbeiro_id"
                                    :options="[auth()->id() => auth()->user()->name]"
                                    :value="auth()->id()"
                                    placeholder="Selecione o barbeiro"
                                    required
                                />
                            @else
                                <x-custom-select
                                    name="barbeiro_id"
                                    :options="$barbeiros->pluck('name', 'id')->toArray()"
                                    :value="old('barbeiro_id', $agendamento->barbeiro_id)"
                                    placeholder="Selecione o barbeiro"
                                    required
                                />
                            @endif
                            @error('barbeiro_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data e Horario -->
            <div class="{{ $cardClass }} overflow-hidden">
                <div class="border-b border-zinc-100 bg-zinc-50/60 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100">
                            <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-zinc-900">Data e Horario</h3>
                            <p class="text-sm text-zinc-500">Defina quando sera o atendimento</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label class="text-sm font-semibold text-zinc-700">
                                Data e Hora de Inicio <span class="text-red-500">*</span>
                            </label>
                            <input type="datetime-local" name="starts_at" required
                                   value="{{ old('starts_at', $agendamento->starts_at->format('Y-m-d\TH:i')) }}"
                                   class="{{ $inputClass }} @error('starts_at') !border-red-400 !ring-2 !ring-red-200 @enderror">
                            @error('starts_at')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="text-sm font-semibold text-zinc-700">
                                Data e Hora de Fim
                            </label>
                            <input type="datetime-local" name="ends_at"
                                   value="{{ old('ends_at', $agendamento->ends_at ? $agendamento->ends_at->format('Y-m-d\TH:i') : '') }}"
                                   class="{{ $inputClass }} @error('ends_at') !border-red-400 !ring-2 !ring-red-200 @enderror">
                            <p class="mt-2 text-xs text-zinc-500">Opcional - deixe em branco se nao souber a duracao</p>
                            @error('ends_at')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Servico e Valor -->
            <div class="{{ $cardClass }} overflow-hidden">
                <div class="border-b border-zinc-100 bg-zinc-50/60 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100">
                            <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 10-4.243 4.243 3 3 0 004.243-4.243zm0-5.758a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-zinc-900">Servico e Valor</h3>
                            <p class="text-sm text-zinc-500">Escolha o servico e defina o preco</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label class="text-sm font-semibold text-zinc-700">
                                Servico <span class="text-red-500">*</span>
                            </label>
                            <select name="servico" id="servicoSelect" required class="{{ $inputClass }} @error('servico') !border-red-400 !ring-2 !ring-red-200 @enderror">
                                <option value="">Selecione um servico</option>
                                @foreach($services as $service)
                                    <option value="{{ $service->name }}" data-price="{{ $service->price ?? '0.00' }}" {{ (old('servico', $agendamento->servico) == $service->name) ? 'selected' : '' }}>
                                        {{ $service->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('servico')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="text-sm font-semibold text-zinc-700">
                                Preco (R$)
                            </label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-medium text-zinc-400">R$</span>
                                <input type="number" step="0.01" name="price" id="priceInput"
                                       value="{{ old('price', $agendamento->price) }}"
                                       placeholder="0,00"
                                       class="{{ $inputClass }} !pl-10 @error('price') !border-red-400 !ring-2 !ring-red-200 @enderror">
                            </div>
                            @error('price')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Observacoes -->
            <div class="{{ $cardClass }} overflow-hidden">
                <div class="border-b border-zinc-100 bg-zinc-50/60 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-100">
                            <svg class="h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-zinc-900">Observacoes</h3>
                            <p class="text-sm text-zinc-500">Informacoes adicionais sobre o atendimento</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <label class="text-sm font-semibold text-zinc-700">
                        Observacoes Adicionais
                    </label>
                    <textarea name="observacoes" rows="4"
                              placeholder="Digite aqui observacoes importantes sobre o agendamento..."
                              class="{{ $inputClass }} resize-none @error('observacoes') !border-red-400 !ring-2 !ring-red-200 @enderror">{{ old('observacoes', $agendamento->observacoes) }}</textarea>
                    @error('observacoes')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Botoes de Acao -->
            <div class="flex flex-col gap-3 pt-2 sm:flex-row sm:justify-end">
                <a href="{{ route('agendamentos.index') }}"
                   class="inline-flex items-center justify-center rounded-2xl border border-zinc-300 bg-white px-6 py-3 text-sm font-bold uppercase tracking-wide text-zinc-700 transition hover:bg-zinc-50">
                    Cancelar
                </a>
                <button type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-barber-500 px-6 py-3 text-sm font-bold uppercase tracking-wide text-white shadow-sm transition hover:bg-barber-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Atualizar Agendamento
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
