@extends('layouts.app')

@section('content')
@php
    $inputClass = 'mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20';
    $cardClass = 'rounded-3xl border border-zinc-200 bg-white/95 shadow-sm';
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-8 rounded-3xl border border-zinc-200 bg-white px-6 py-7 shadow-sm sm:px-8">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500">Conta</p>
                <h1 class="mt-2 text-3xl font-bold leading-tight text-zinc-900 sm:text-4xl">Configuracoes</h1>
            </div>
            <a href="{{ route('profile.edit') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700 transition hover:bg-zinc-100">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Voltar ao perfil
            </a>
        </div>
    </div>

    <div class="space-y-8">
        <!-- Aparencia -->
        <div class="{{ $cardClass }} overflow-hidden">
            <div class="border-b border-zinc-200 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-zinc-100">
                        <svg class="h-5 w-5 text-zinc-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-zinc-900">Aparencia e Navegacao</h3>
                        <p class="text-sm text-zinc-500">Escolha o estilo do menu do sistema</p>
                    </div>
                </div>
            </div>
            <div class="p-6 sm:p-7">
                <form method="POST" action="{{ route('profile.preferences.update') }}" class="space-y-6">
                    @csrf
                    @method('patch')

                    <div>
                        <p class="text-sm font-semibold text-zinc-700">Posicao do menu</p>
                        <div class="mt-3 grid gap-3 md:grid-cols-2">
                            <label class="cursor-pointer rounded-2xl border border-zinc-200 p-4 transition hover:border-barber-400 hover:bg-zinc-50">
                                <div class="flex items-start gap-3">
                                    <input type="radio" name="navigation_layout" value="top" class="mt-1 text-barber-500 focus:ring-barber-500" {{ old('navigation_layout', $user->navigation_layout ?? 'top') === 'top' ? 'checked' : '' }}>
                                    <div>
                                        <p class="text-sm font-semibold text-zinc-900">Menu superior (Navbar)</p>
                                        <p class="mt-1 text-xs text-zinc-500">Visual tradicional no topo da tela</p>
                                    </div>
                                </div>
                            </label>
                            <label class="cursor-pointer rounded-2xl border border-zinc-200 p-4 transition hover:border-barber-400 hover:bg-zinc-50">
                                <div class="flex items-start gap-3">
                                    <input type="radio" name="navigation_layout" value="sidebar" class="mt-1 text-barber-500 focus:ring-barber-500" {{ old('navigation_layout', $user->navigation_layout ?? 'top') === 'sidebar' ? 'checked' : '' }}>
                                    <div>
                                        <p class="text-sm font-semibold text-zinc-900">Menu lateral (Sidebar)</p>
                                        <p class="mt-1 text-xs text-zinc-500">Layout moderno com opcao de minimizar</p>
                                    </div>
                                </div>
                            </label>
                        </div>
                        @error('navigation_layout')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="flex items-center gap-3 rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3">
                        <input type="hidden" name="sidebar_collapsed" value="0">
                        <input type="checkbox" name="sidebar_collapsed" value="1" class="rounded border-zinc-300 text-barber-500 focus:ring-barber-500" {{ old('sidebar_collapsed', (int) ($user->sidebar_collapsed ?? 0)) ? 'checked' : '' }}>
                        <div>
                            <p class="text-sm font-semibold text-zinc-800">Iniciar com menu lateral minimizado</p>
                            <p class="text-xs text-zinc-500">So funciona quando o layout lateral estiver selecionado</p>
                        </div>
                    </label>

                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-zinc-900 px-5 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-zinc-800">
                            Salvar preferencia
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Seguranca -->
        <div class="{{ $cardClass }} overflow-hidden">
            <div class="border-b border-zinc-200 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-barber-100">
                        <svg class="h-5 w-5 text-barber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-zinc-900">Seguranca</h3>
                        <p class="text-sm text-zinc-500">Altere sua senha de acesso</p>
                    </div>
                </div>
            </div>
            <div class="p-6 sm:p-7">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        @if(auth()->user()->isOwner())
        <!-- Configurações da Agenda -->
        <div class="{{ $cardClass }} overflow-hidden">
            <div class="border-b border-zinc-200 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-barber-100">
                        <svg class="h-5 w-5 text-barber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-zinc-900">Configuracoes da Agenda</h3>
                        <p class="text-sm text-zinc-500">Personalize sua página de agendamento pública</p>
                    </div>
                </div>
            </div>
            <div class="p-6 sm:p-7">
                <div class="space-y-4">
                    <p class="text-sm text-zinc-600">
                        Gerencie as informações da sua barbearia, horários de atendimento e imagens que serão exibidas na página pública de agendamento.
                    </p>
                    <a href="{{ route('agenda.config.index') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-barber-500 px-6 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-barber-600">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Acessar Configurações
                    </a>
                </div>
            </div>
        </div>
        @endif

        <!-- Zona de Perigo -->
        <div class="rounded-3xl border border-red-200 bg-white/95 shadow-sm overflow-hidden">
            <div class="border-b border-red-200 bg-red-50 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-100">
                        <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-red-900">Zona de perigo</h3>
                        <p class="text-sm text-red-700">Acoes irreversiveis da conta</p>
                    </div>
                </div>
            </div>
            <div class="p-6 sm:p-7">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</div>

@if (session('status') === 'preferences-updated')
    <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)" class="fixed bottom-6 right-6 z-50 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 shadow-lg">
        <div class="flex items-center gap-3">
            <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-100">
                <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <span class="text-sm font-semibold text-emerald-800">Preferencias salvas com sucesso!</span>
        </div>
    </div>
@endif
@endsection
