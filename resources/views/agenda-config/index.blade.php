@extends('layouts.app')

@section('content')
@php
    $inputClass = 'mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20';
    $cardClass = 'rounded-3xl border border-zinc-200 bg-white/95 shadow-sm';
    $editarBtnClass = 'inline-flex items-center gap-1.5 rounded-xl bg-barber-500 px-3 py-2 text-xs font-bold uppercase tracking-[0.08em] text-white transition hover:bg-barber-600';
    $editarBtnInner = '<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg> Editar';
    $hm = fn ($t) => \Illuminate\Support\Str::substr((string) $t, 0, 5);
    $estado = [
        'nome_barbearia' => $agendaConfig->nome_barbearia,
        'slug' => $agendaConfig->slug,
        'descricao' => $agendaConfig->descricao,
        'telefone' => $agendaConfig->telefone,
        'endereco' => $agendaConfig->endereco,
        'horario_inicio' => $hm($agendaConfig->horario_inicio),
        'horario_fim' => $hm($agendaConfig->horario_fim),
        'intervalo_slots' => (int) $agendaConfig->intervalo_slots,
        'dias' => array_values($agendaConfig->dias_atendimento ?? []),
        'ativa' => (bool) $agendaConfig->ativa,
        'logoUrl' => $agendaConfig->getLogoUrl(),
    ];
    $imagensIniciais = $agendaConfig->imagens->map(fn ($img) => [
        'id' => $img->id,
        'url' => asset('storage/' . $img->caminho_imagem),
    ])->values();
@endphp

<div x-data="agendaConfigApp({
        updateUrl: @js(route('agenda.config.update')),
        uploadUrl: @js(route('agenda.imagens.upload')),
        deleteUrlBase: @js(url('agenda/imagens')),
        checkSlugUrl: @js(route('agenda.config.check-slug')),
        publicBase: @js(url('/t') . '/'),
        csrf: @js(csrf_token()),
        imagens: @js($imagensIniciais),
        saved: @js($estado),
        diasSemana: @js($diasSemana),
     })"
     class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- ===== Toast ===== --}}
    <div x-cloak x-show="toast.show"
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0"
         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 translate-x-4"
         class="fixed right-4 top-4 z-[120] max-w-sm">
        <div class="flex items-start gap-3 rounded-2xl px-4 py-3 shadow-lg border" :class="toast.type === 'error' ? 'border-red-200 bg-red-50' : 'border-emerald-200 bg-emerald-50'">
            <svg x-show="toast.type !== 'error'" class="h-5 w-5 flex-shrink-0 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            <svg x-show="toast.type === 'error'" class="h-5 w-5 flex-shrink-0 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
            <p class="text-sm font-medium" :class="toast.type === 'error' ? 'text-red-700' : 'text-emerald-700'" x-text="toast.message"></p>
        </div>
    </div>

    {{-- ===== Header ===== --}}
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500">Barbearia</p>
            <h1 class="mt-2 text-3xl font-bold leading-tight text-zinc-900 sm:text-4xl">Configurações da Agenda</h1>
        </div>
        <a href="{{ route('profile.settings') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700 transition hover:bg-zinc-100">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Voltar
        </a>
    </div>

    <div class="space-y-6">

        {{-- ============ CARD: Informações Básicas ============ --}}
        <div class="{{ $cardClass }} overflow-hidden">
            <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-barber-100">
                        <svg class="h-5 w-5 text-barber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-zinc-900">Informações Básicas</h3>
                </div>
                <button @click="abrirModal('basico')" class="{{ $editarBtnClass }}">{!! $editarBtnInner !!}</button>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-zinc-100 overflow-hidden flex items-center justify-center border border-zinc-200 flex-shrink-0">
                        <template x-if="saved.logoUrl"><img :src="saved.logoUrl" alt="" class="w-full h-full object-cover"></template>
                        <template x-if="!saved.logoUrl"><span class="text-xl font-bold text-zinc-300" x-text="(saved.nome_barbearia || 'B').charAt(0).toUpperCase()"></span></template>
                    </div>
                    <div class="min-w-0">
                        <p class="text-lg font-bold text-zinc-900 truncate" x-text="saved.nome_barbearia || '—'"></p>
                        <p class="text-sm text-zinc-500 line-clamp-2" x-text="saved.descricao || 'Sem descrição'"></p>
                    </div>
                </div>
                <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Telefone</dt>
                        <dd class="mt-0.5 text-sm text-zinc-800" x-text="saved.telefone || '—'"></dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Endereço</dt>
                        <dd class="mt-0.5 text-sm text-zinc-800" x-text="saved.endereco || '—'"></dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- ============ CARD: Horário de Atendimento ============ --}}
        <div class="{{ $cardClass }} overflow-hidden">
            <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-barber-100">
                        <svg class="h-5 w-5 text-barber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-zinc-900">Horário de Atendimento</h3>
                </div>
                <button @click="abrirModal('horarios')" class="{{ $editarBtnClass }}">{!! $editarBtnInner !!}</button>
            </div>
            <div class="p-6 space-y-4">
                <dl class="grid grid-cols-2 gap-3">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Funcionamento</dt>
                        <dd class="mt-0.5 text-sm font-medium text-zinc-800"><span x-text="saved.horario_inicio"></span> às <span x-text="saved.horario_fim"></span></dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Intervalo</dt>
                        <dd class="mt-0.5 text-sm font-medium text-zinc-800"><span x-text="saved.intervalo_slots"></span> minutos</dd>
                    </div>
                </dl>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-zinc-400 mb-2">Dias de atendimento</p>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="(label, key) in diasSemana" :key="key">
                            <span class="rounded-lg border px-2.5 py-1 text-xs font-medium" :class="saved.dias.includes(key) ? 'border-barber-300 bg-barber-50 text-barber-700' : 'border-zinc-200 bg-zinc-50 text-zinc-400'" x-text="label.substring(0,3)"></span>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============ CARD: Acesso da Agenda ============ --}}
        <div class="{{ $cardClass }} overflow-hidden">
            <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-barber-100">
                        <svg class="h-5 w-5 text-barber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-zinc-900">Acesso da Agenda</h3>
                </div>
                <button @click="abrirModal('acesso')" class="{{ $editarBtnClass }}">{!! $editarBtnInner !!}</button>
            </div>
            <div class="p-6">
                <span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-sm font-semibold" :class="saved.ativa ? 'bg-emerald-100 text-emerald-700' : 'bg-zinc-100 text-zinc-600'">
                    <span class="w-2 h-2 rounded-full" :class="saved.ativa ? 'bg-emerald-500' : 'bg-zinc-400'"></span>
                    <span x-text="saved.ativa ? 'Ativa' : 'Inativa'"></span>
                </span>
                <p class="mt-3 text-sm text-zinc-500" x-text="saved.ativa ? 'Seus clientes podem agendar pelo link público.' : 'O link público está desativado no momento.'"></p>
            </div>
        </div>

        {{-- ============ CARD: Link Compartilhável ============ --}}
        <div class="{{ $cardClass }} overflow-hidden">
            <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-barber-100">
                        <svg class="h-5 w-5 text-barber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.658 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-zinc-900">Link Compartilhável</h3>
                </div>
                <button @click="abrirModal('link')" class="{{ $editarBtnClass }}">{!! $editarBtnInner !!}</button>
            </div>
            <div class="p-6 space-y-3">
                <p class="text-sm font-medium text-zinc-800 break-all" x-text="publicUrl()"></p>
                <div class="flex gap-2">
                    <input type="text" :value="publicUrl()" readonly class="sr-only" x-ref="linkInput">
                    <button type="button" @click="copiarLink()" class="inline-flex items-center gap-2 rounded-xl bg-barber-500 px-4 py-2 text-xs font-bold uppercase tracking-[0.08em] text-white hover:bg-barber-600">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        Copiar
                    </button>
                    <a :href="publicUrl()" target="_blank" class="inline-flex items-center gap-2 rounded-xl border border-zinc-300 bg-white px-4 py-2 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700 hover:bg-zinc-100">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        Abrir
                    </a>
                </div>
            </div>
        </div>

        {{-- ============ CARD: Imagens do Carrossel ============ --}}
        <div class="{{ $cardClass }} overflow-hidden">
            <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-barber-100">
                        <svg class="h-5 w-5 text-barber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-zinc-900">Imagens do Carrossel</h3>
                </div>
                <button @click="abrirModal('imagens')" class="{{ $editarBtnClass }}">{!! $editarBtnInner !!}</button>
            </div>
            <div class="p-6">
                <div x-show="imagens.length" class="flex flex-wrap gap-3">
                    <template x-for="img in imagens" :key="img.id">
                        <img :src="img.url" alt="" class="h-24 w-24 rounded-xl object-cover border border-zinc-200">
                    </template>
                </div>
                <p x-show="!imagens.length" class="text-sm text-zinc-400">Nenhuma imagem adicionada. Clique em Editar para adicionar fotos da sua barbearia.</p>
            </div>
        </div>
    </div>

    {{-- =================== MODAL ÚNICA POR SEÇÃO =================== --}}
    <div x-cloak x-show="modalSecao !== null" @keydown.escape.window="fecharModal()"
         class="fixed inset-0 z-[90] flex items-center justify-center bg-zinc-900/60 backdrop-blur-sm p-4" x-transition.opacity>
        <div class="w-full max-h-[92vh] rounded-3xl bg-white shadow-2xl flex flex-col"
             :class="modalSecao === 'imagens' ? 'max-w-3xl' : 'max-w-2xl'" @click.outside="fecharModal()">

            {{-- Cabeçalho --}}
            <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4">
                <h3 class="text-lg font-bold text-zinc-900" x-text="tituloModal()"></h3>
                <button @click="fecharModal()" class="text-zinc-400 hover:text-zinc-700"><svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>

            <div class="flex-1 overflow-y-auto px-6 py-5">

                {{-- SEÇÃO: Informações Básicas --}}
                <div x-show="modalSecao === 'basico'" class="space-y-5">
                    <div class="flex items-center gap-4">
                        <div class="w-20 h-20 rounded-2xl bg-zinc-100 overflow-hidden flex items-center justify-center border border-zinc-200 flex-shrink-0">
                            <template x-if="logoPreview"><img :src="logoPreview" alt="" class="w-full h-full object-cover"></template>
                            <template x-if="!logoPreview"><svg class="w-8 h-8 text-zinc-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M18 9h.008M5.25 21h13.5A2.25 2.25 0 0021 18.75V5.25A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25v13.5A2.25 2.25 0 005.25 21z"/></svg></template>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-zinc-700">Logo da barbearia</label>
                            <p class="text-xs text-zinc-500 mb-2">Aparece como foto de perfil no chat.</p>
                            <div class="flex gap-2">
                                <button type="button" @click="$refs.logoInput.click()" class="rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-100">Escolher imagem</button>
                                <button type="button" x-show="logoPreview" @click="removerLogo()" class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-100">Remover</button>
                            </div>
                            <input type="file" accept="image/*" class="hidden" x-ref="logoInput" @change="onLogoChange($event)">
                            <p x-show="errors.logo" x-text="errors.logo" class="mt-1 text-xs text-red-600"></p>
                        </div>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Nome da Barbearia <span class="text-red-500">*</span></label>
                        <input type="text" x-model="form.nome_barbearia" required class="{{ $inputClass }}" placeholder="Ex: Duarte Barbearia">
                        <p x-show="errors.nome_barbearia" x-text="errors.nome_barbearia" class="mt-1 text-xs text-red-600"></p>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Descrição</label>
                        <textarea x-model="form.descricao" rows="3" class="{{ $inputClass }}" placeholder="Descreva sua barbearia..."></textarea>
                    </div>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="text-sm font-semibold text-zinc-700">Telefone</label>
                            <input type="text" x-model="form.telefone" class="{{ $inputClass }}" placeholder="(11) 99999-9999">
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-zinc-700">Endereço</label>
                            <input type="text" x-model="form.endereco" class="{{ $inputClass }}" placeholder="Rua, número, bairro">
                        </div>
                    </div>
                </div>

                {{-- SEÇÃO: Horários --}}
                <div x-show="modalSecao === 'horarios'" class="space-y-5">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label class="text-sm font-semibold text-zinc-700">Início <span class="text-red-500">*</span></label>
                            <input type="time" x-model="form.horario_inicio" required class="{{ $inputClass }}">
                            <p x-show="errors.horario_inicio" x-text="errors.horario_inicio" class="mt-1 text-xs text-red-600"></p>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-zinc-700">Término <span class="text-red-500">*</span></label>
                            <input type="time" x-model="form.horario_fim" required class="{{ $inputClass }}">
                            <p x-show="errors.horario_fim" x-text="errors.horario_fim" class="mt-1 text-xs text-red-600"></p>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-zinc-700">Intervalo (min) <span class="text-red-500">*</span></label>
                            <input type="number" x-model="form.intervalo_slots" min="15" max="120" required class="{{ $inputClass }}">
                            <p x-show="errors.intervalo_slots" x-text="errors.intervalo_slots" class="mt-1 text-xs text-red-600"></p>
                        </div>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Dias de Atendimento</label>
                        <div class="mt-3 grid grid-cols-2 gap-3 md:grid-cols-3">
                            <template x-for="(label, key) in diasSemana" :key="key">
                                <label class="flex items-center gap-2 rounded-lg border border-zinc-200 bg-zinc-50 p-3 cursor-pointer hover:bg-zinc-100 transition">
                                    <input type="checkbox" :value="key" x-model="form.dias" class="h-5 w-5 rounded border-zinc-300 text-barber-500 focus:ring-barber-500">
                                    <span class="text-sm font-medium text-zinc-700" x-text="label"></span>
                                </label>
                            </template>
                        </div>
                        <p x-show="errors.dias_atendimento" x-text="errors.dias_atendimento" class="mt-2 text-xs text-red-600"></p>
                    </div>
                </div>

                {{-- SEÇÃO: Acesso --}}
                <div x-show="modalSecao === 'acesso'">
                    <div class="flex items-center justify-between rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                        <div>
                            <p class="text-sm font-semibold text-zinc-800">Agenda ativa</p>
                            <p class="text-xs text-zinc-500" x-text="form.ativa ? 'Clientes podem agendar pelo link público.' : 'O link público está desativado.'"></p>
                        </div>
                        <button type="button" @click="form.ativa = !form.ativa" class="relative inline-flex h-7 w-12 flex-shrink-0 items-center rounded-full transition" :class="form.ativa ? 'bg-barber-500' : 'bg-zinc-300'">
                            <span class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition" :class="form.ativa ? 'translate-x-6' : 'translate-x-1'"></span>
                        </button>
                    </div>
                </div>

                {{-- SEÇÃO: Link --}}
                <div x-show="modalSecao === 'link'" class="space-y-4">
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Endereço do link <span class="text-red-500">*</span></label>
                        <p class="text-xs text-zinc-500 mb-2">Apenas letras minúsculas, números e hífens.</p>
                        <div class="flex items-stretch rounded-2xl border border-zinc-200 bg-zinc-50 overflow-hidden focus-within:border-barber-500 focus-within:ring-2 focus-within:ring-barber-500/20">
                            <span class="flex items-center bg-zinc-100 px-3 text-xs text-zinc-500 border-r border-zinc-200" x-text="publicBase"></span>
                            <input type="text" x-model="form.slug" @input.debounce.400ms="checarSlug()" class="flex-1 bg-transparent px-3 py-3 text-sm text-zinc-900 outline-none" placeholder="duarte-barbearia">
                        </div>
                        <div class="mt-2 text-xs">
                            <template x-if="slugStatus === 'checking'"><span class="text-zinc-500">Verificando disponibilidade...</span></template>
                            <template x-if="slugStatus === 'available'"><span class="text-emerald-600 font-medium">✓ Disponível</span></template>
                            <template x-if="slugStatus === 'taken'"><span class="text-red-600 font-medium">✗ Já está em uso, escolha outro</span></template>
                            <template x-if="slugStatus === 'invalid'"><span class="text-red-600 font-medium">Use apenas letras minúsculas, números e hífens</span></template>
                        </div>
                        <p x-show="errors.slug" x-text="errors.slug" class="mt-1 text-xs text-red-600"></p>
                    </div>
                    <div class="rounded-2xl bg-barber-50 border border-barber-200 p-3">
                        <p class="text-xs text-barber-700">Link final: <span class="font-semibold" x-text="publicBase + (form.slug || '...')"></span></p>
                    </div>
                </div>

                {{-- SEÇÃO: Imagens --}}
                <div x-show="modalSecao === 'imagens'" class="space-y-4">
                    <div class="rounded-2xl border-2 border-dashed border-zinc-300 bg-zinc-50 p-6 text-center transition hover:bg-zinc-100 cursor-pointer"
                         :class="uploading ? 'opacity-60 pointer-events-none' : ''"
                         @click="$refs.imagemInput.click()"
                         @dragover.prevent @drop.prevent="uploadImagens($event.dataTransfer.files)">
                        <input type="file" multiple accept="image/*" class="hidden" x-ref="imagemInput" @change="uploadImagens($event.target.files)">
                        <template x-if="!uploading">
                            <div>
                                <svg class="mx-auto h-10 w-10 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                <p class="mt-2 text-sm font-medium text-zinc-700">Clique ou arraste imagens</p>
                            </div>
                        </template>
                        <template x-if="uploading">
                            <div class="flex flex-col items-center">
                                <svg class="h-8 w-8 animate-spin text-barber-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                                <p class="mt-2 text-sm text-zinc-600">Enviando...</p>
                            </div>
                        </template>
                    </div>
                    <div x-show="imagens.length" class="grid grid-cols-3 gap-3 sm:grid-cols-4">
                        <template x-for="img in imagens" :key="img.id">
                            <div class="group relative rounded-lg overflow-hidden bg-zinc-100 aspect-square">
                                <img :src="img.url" alt="" class="h-full w-full object-cover">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                    <button type="button" @click="confirmDeleteId = img.id" class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-red-600 text-white hover:bg-red-700"><svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                </div>
                            </div>
                        </template>
                    </div>
                    <p x-show="!imagens.length" class="text-sm text-zinc-400 text-center py-2">Nenhuma imagem ainda.</p>
                </div>
            </div>

            {{-- Rodapé (Imagens não tem "salvar": já é instantâneo) --}}
            <div class="flex justify-end gap-3 border-t border-zinc-200 px-6 py-4">
                <button type="button" @click="fecharModal()" class="rounded-2xl border border-zinc-300 px-5 py-2.5 text-sm font-semibold text-zinc-700 hover:bg-zinc-100" x-text="modalSecao === 'imagens' ? 'Fechar' : 'Cancelar'"></button>
                <button type="button" x-show="modalSecao !== 'imagens'" @click="salvar()" :disabled="saving" class="inline-flex items-center gap-2 rounded-2xl bg-barber-500 px-6 py-2.5 text-sm font-bold text-white hover:bg-barber-600 disabled:opacity-60">
                    <svg x-show="saving" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                    <span x-text="saving ? 'Salvando...' : 'Salvar'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- ===== Confirmação de exclusão de imagem ===== --}}
    <div x-cloak x-show="confirmDeleteId !== null" @keydown.escape.window="confirmDeleteId = null"
         class="fixed inset-0 z-[95] flex items-center justify-center bg-zinc-900/60 backdrop-blur-sm p-4" x-transition.opacity>
        <div class="w-full max-w-sm rounded-3xl bg-white p-6 shadow-xl" @click.outside="confirmDeleteId = null">
            <h3 class="text-base font-bold text-zinc-900">Remover imagem</h3>
            <p class="text-sm text-zinc-500 mt-1">Esta ação não pode ser desfeita.</p>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" @click="confirmDeleteId = null" class="rounded-2xl border border-zinc-300 px-5 py-2.5 text-sm font-semibold text-zinc-700 hover:bg-zinc-100">Cancelar</button>
                <button type="button" @click="removerImagem(confirmDeleteId)" :disabled="deleting" class="rounded-2xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-60"><span x-text="deleting ? 'Removendo...' : 'Remover'"></span></button>
            </div>
        </div>
    </div>
</div>

<script>
function agendaConfigApp(opts) {
    return {
        ...opts,
        modalSecao: null,
        form: {},
        logoPreview: null,
        logoFile: null,
        removerLogoFlag: false,
        slugStatus: null,
        saving: false,
        uploading: false,
        deleting: false,
        confirmDeleteId: null,
        errors: {},
        toast: { show: false, type: 'success', message: '', timer: null },

        publicUrl() { return this.publicBase + (this.saved.slug || ''); },
        tituloModal() {
            return {
                basico: 'Informações Básicas', horarios: 'Horário de Atendimento',
                acesso: 'Acesso da Agenda', link: 'Link Compartilhável', imagens: 'Imagens do Carrossel',
            }[this.modalSecao] || '';
        },

        notify(message, type = 'success') {
            clearTimeout(this.toast.timer);
            this.toast.message = message; this.toast.type = type; this.toast.show = true;
            this.toast.timer = setTimeout(() => { this.toast.show = false; }, 3500);
        },

        abrirModal(secao) {
            // Cópia completa do estado salvo (envia tudo ao salvar, edita só a seção).
            this.form = { ...this.saved, dias: [...(this.saved.dias || [])] };
            this.logoPreview = this.saved.logoUrl;
            this.logoFile = null;
            this.removerLogoFlag = false;
            this.errors = {};
            this.slugStatus = null;
            this.modalSecao = secao;
        },
        fecharModal() { this.modalSecao = null; },

        onLogoChange(e) {
            const file = e.target.files[0];
            if (!file) return;
            this.logoFile = file;
            this.removerLogoFlag = false;
            const reader = new FileReader();
            reader.onload = ev => { this.logoPreview = ev.target.result; };
            reader.readAsDataURL(file);
        },
        removerLogo() {
            this.logoPreview = null;
            this.logoFile = null;
            this.removerLogoFlag = true;
            this.$refs.logoInput.value = '';
        },

        checarSlug() {
            const slug = (this.form.slug || '').toLowerCase().trim();
            if (!/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(slug)) { this.slugStatus = slug === '' ? null : 'invalid'; return; }
            this.slugStatus = 'checking';
            fetch(this.checkSlugUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf, 'Accept': 'application/json' },
                body: JSON.stringify({ slug }),
            }).then(r => r.json()).then(d => {
                this.form.slug = d.slug;
                this.slugStatus = d.available ? 'available' : 'taken';
            }).catch(() => { this.slugStatus = null; });
        },

        async salvar() {
            this.saving = true; this.errors = {};
            const m = this.form;
            const fd = new FormData();
            fd.append('_method', 'PUT');
            fd.append('nome_barbearia', m.nome_barbearia ?? '');
            fd.append('slug', m.slug ?? '');
            fd.append('descricao', m.descricao ?? '');
            fd.append('telefone', m.telefone ?? '');
            fd.append('endereco', m.endereco ?? '');
            fd.append('horario_inicio', m.horario_inicio ?? '');
            fd.append('horario_fim', m.horario_fim ?? '');
            fd.append('intervalo_slots', m.intervalo_slots ?? '');
            (m.dias || []).forEach(d => fd.append('dias_atendimento[]', d));
            fd.append('ativa', m.ativa ? 1 : 0);
            if (this.logoFile) fd.append('logo', this.logoFile);
            if (this.removerLogoFlag) fd.append('remover_logo', 1);

            try {
                const res = await fetch(this.updateUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': this.csrf, 'Accept': 'application/json' }, body: fd });
                if (res.status === 422) {
                    const data = await res.json();
                    this.errors = Object.fromEntries(Object.entries(data.errors || {}).map(([k, v]) => [k, Array.isArray(v) ? v[0] : v]));
                    this.notify(data.message || 'Verifique os campos destacados.', 'error');
                    return;
                }
                if (!res.ok) throw new Error('fail');
                const data = await res.json();
                this.saved = { ...this.form, slug: data.slug ?? this.form.slug, logoUrl: data.logo_url ?? null, dias: [...this.form.dias] };
                this.notify(data.message || 'Configurações salvas!');
                this.modalSecao = null;
            } catch (e) {
                this.notify('Erro ao salvar. Tente novamente.', 'error');
            } finally {
                this.saving = false;
            }
        },

        async uploadImagens(fileList) {
            const files = Array.from(fileList || []);
            if (!files.length) return;
            this.uploading = true;
            const fd = new FormData();
            files.forEach(f => fd.append('imagens[]', f));
            try {
                const res = await fetch(this.uploadUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': this.csrf, 'Accept': 'application/json' }, body: fd });
                if (res.status === 422) { const data = await res.json(); const first = Object.values(data.errors || {})[0]; this.notify(Array.isArray(first) ? first[0] : (data.message || 'Imagem inválida.'), 'error'); return; }
                if (!res.ok) throw new Error('fail');
                const data = await res.json();
                this.imagens.push(...(data.imagens || []));
                this.notify(data.message || 'Imagens adicionadas!');
            } catch (e) {
                this.notify('Erro ao enviar imagens.', 'error');
            } finally {
                this.uploading = false;
                this.$refs.imagemInput.value = '';
            }
        },

        async removerImagem(id) {
            this.deleting = true;
            try {
                const res = await fetch(`${this.deleteUrlBase}/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': this.csrf, 'Accept': 'application/json' } });
                if (!res.ok) throw new Error('fail');
                this.imagens = this.imagens.filter(i => i.id !== id);
                this.notify('Imagem removida!');
            } catch (e) {
                this.notify('Erro ao remover imagem.', 'error');
            } finally {
                this.deleting = false;
                this.confirmDeleteId = null;
            }
        },

        copiarLink() {
            const input = this.$refs.linkInput;
            input.select(); input.setSelectionRange(0, 99999);
            const done = () => this.notify('Link copiado!');
            if (navigator.clipboard) { navigator.clipboard.writeText(this.publicUrl()).then(done).catch(() => { document.execCommand('copy'); done(); }); }
            else { document.execCommand('copy'); done(); }
        },
    };
}
</script>
@endsection
