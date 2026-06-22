@extends('layouts.app')

@section('content')
@php
    $inputClass = 'mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20';
    $cardClass = 'rounded-3xl border border-zinc-200 bg-white/95 shadow-sm';
    $imagensIniciais = $agendaConfig->imagens->map(fn ($img) => [
        'id' => $img->id,
        'url' => asset('storage/' . $img->caminho_imagem),
    ])->values();
@endphp

<div x-data="agendaConfigApp({
        updateUrl: @js(route('agenda.config.update')),
        uploadUrl: @js(route('agenda.imagens.upload')),
        deleteUrlBase: @js(url('agenda/imagens')),
        publicUrl: @js($agendaConfig->getPublicUrl()),
        csrf: @js(csrf_token()),
        imagens: @js($imagensIniciais),
        ativa: @js((bool) $agendaConfig->ativa),
     })"
     class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- ===== Toast (canto superior direito) ===== --}}
    <div x-cloak x-show="toast.show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-x-4"
         x-transition:enter-end="opacity-100 translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-x-0"
         x-transition:leave-end="opacity-0 translate-x-4"
         class="fixed right-4 top-4 z-[100] max-w-sm">
        <div class="flex items-start gap-3 rounded-2xl px-4 py-3 shadow-lg border"
             :class="toast.type === 'error' ? 'border-red-200 bg-red-50' : 'border-emerald-200 bg-emerald-50'">
            <svg x-show="toast.type !== 'error'" class="h-5 w-5 flex-shrink-0 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            <svg x-show="toast.type === 'error'" class="h-5 w-5 flex-shrink-0 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
            <p class="text-sm font-medium" :class="toast.type === 'error' ? 'text-red-700' : 'text-emerald-700'" x-text="toast.message"></p>
        </div>
    </div>

    {{-- ===== Header ===== --}}
    <div class="mb-8 rounded-3xl border border-zinc-200 bg-white px-6 py-7 shadow-sm sm:px-8">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500">Barbearia</p>
                <h1 class="mt-2 text-3xl font-bold leading-tight text-zinc-900 sm:text-4xl">Configurações da Agenda</h1>
            </div>
            <a href="{{ route('profile.settings') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700 transition hover:bg-zinc-100">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Voltar
            </a>
        </div>
    </div>

    <form @submit.prevent="salvar()" x-ref="form" class="space-y-8">
        @csrf
        @method('PUT')

        {{-- Seção 1: Informações Básicas --}}
        <div class="{{ $cardClass }} overflow-hidden">
            <div class="border-b border-zinc-200 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-barber-100">
                        <svg class="h-5 w-5 text-barber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-zinc-900">Informações Básicas</h3>
                        <p class="text-sm text-zinc-500">Dados da sua barbearia</p>
                    </div>
                </div>
            </div>
            <div class="p-6 sm:p-7">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="text-sm font-semibold text-zinc-700">Nome da Barbearia <span class="text-red-500">*</span></label>
                        <input type="text" name="nome_barbearia" required value="{{ old('nome_barbearia', $agendaConfig->nome_barbearia) }}" class="{{ $inputClass }}" placeholder="Ex: Barbearia Moderna">
                        <p x-show="errors.nome_barbearia" x-text="errors.nome_barbearia" class="mt-1 text-xs text-red-600"></p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-sm font-semibold text-zinc-700">Descrição</label>
                        <textarea name="descricao" rows="3" class="{{ $inputClass }}" placeholder="Descreva sua barbearia...">{{ old('descricao', $agendaConfig->descricao) }}</textarea>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Telefone</label>
                        <input type="text" name="telefone" value="{{ old('telefone', $agendaConfig->telefone) }}" class="{{ $inputClass }}" placeholder="(11) 99999-9999">
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Endereço</label>
                        <input type="text" name="endereco" value="{{ old('endereco', $agendaConfig->endereco) }}" class="{{ $inputClass }}" placeholder="Rua, número, complemento">
                    </div>
                </div>
            </div>
        </div>

        {{-- Seção 2: Horários de Atendimento --}}
        <div class="{{ $cardClass }} overflow-hidden">
            <div class="border-b border-zinc-200 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-barber-100">
                        <svg class="h-5 w-5 text-barber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-zinc-900">Horários de Atendimento</h3>
                        <p class="text-sm text-zinc-500">Configure seus horários e intervalo de agendamento</p>
                    </div>
                </div>
            </div>
            <div class="p-6 sm:p-7">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Hora de Início <span class="text-red-500">*</span></label>
                        <input type="time" name="horario_inicio" required value="{{ old('horario_inicio', \Illuminate\Support\Str::substr($agendaConfig->horario_inicio, 0, 5)) }}" class="{{ $inputClass }}">
                        <p x-show="errors.horario_inicio" x-text="errors.horario_inicio" class="mt-1 text-xs text-red-600"></p>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Hora de Término <span class="text-red-500">*</span></label>
                        <input type="time" name="horario_fim" required value="{{ old('horario_fim', \Illuminate\Support\Str::substr($agendaConfig->horario_fim, 0, 5)) }}" class="{{ $inputClass }}">
                        <p x-show="errors.horario_fim" x-text="errors.horario_fim" class="mt-1 text-xs text-red-600"></p>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Intervalo (minutos) <span class="text-red-500">*</span></label>
                        <input type="number" name="intervalo_slots" required min="15" max="120" value="{{ old('intervalo_slots', $agendaConfig->intervalo_slots) }}" class="{{ $inputClass }}" placeholder="30">
                        <p x-show="errors.intervalo_slots" x-text="errors.intervalo_slots" class="mt-1 text-xs text-red-600"></p>
                    </div>
                    <div class="md:col-span-3">
                        <label class="text-sm font-semibold text-zinc-700">Dias de Atendimento</label>
                        <div class="mt-3 grid grid-cols-2 gap-3 md:grid-cols-3">
                            @foreach($diasSemana as $dia => $label)
                                <label class="flex items-center gap-2 rounded-lg border border-zinc-200 bg-zinc-50 p-3 cursor-pointer hover:bg-zinc-100 transition">
                                    <input type="checkbox" name="dias_atendimento[]" value="{{ $dia }}" {{ in_array($dia, $agendaConfig->dias_atendimento ?? []) ? 'checked' : '' }} class="h-5 w-5 rounded border-zinc-300 text-barber-500 focus:ring-barber-500">
                                    <span class="text-sm font-medium text-zinc-700">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p x-show="errors.dias_atendimento" x-text="errors.dias_atendimento" class="mt-2 text-xs text-red-600"></p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Seção 3: Status --}}
        <div class="{{ $cardClass }} overflow-hidden">
            <div class="border-b border-zinc-200 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-barber-100">
                        <svg class="h-5 w-5 text-barber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-zinc-900">Status da Agenda</h3>
                        <p class="text-sm text-zinc-500">Ative ou desative o link público de agendamento</p>
                    </div>
                </div>
            </div>
            <div class="p-6 sm:p-7">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                              :class="ativa ? 'bg-emerald-100 text-emerald-700' : 'bg-zinc-100 text-zinc-700'"
                              x-text="ativa ? '✓ Ativo' : '○ Inativo'"></span>
                        <p class="text-sm text-zinc-600" x-text="ativa ? 'Seus clientes podem agendar pelo link público.' : 'O link público está desativado no momento.'"></p>
                    </div>
                    {{-- Toggle --}}
                    <button type="button" @click="ativa = !ativa" :aria-pressed="ativa"
                            class="relative inline-flex h-7 w-12 flex-shrink-0 items-center rounded-full transition"
                            :class="ativa ? 'bg-barber-500' : 'bg-zinc-300'">
                        <span class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition" :class="ativa ? 'translate-x-6' : 'translate-x-1'"></span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Seção 4: Link Compartilhável --}}
        <div class="{{ $cardClass }} overflow-hidden">
            <div class="border-b border-zinc-200 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-barber-100">
                        <svg class="h-5 w-5 text-barber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.658 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-zinc-900">Link Compartilhável</h3>
                        <p class="text-sm text-zinc-500">Compartilhe com seus clientes para que eles agendem</p>
                    </div>
                </div>
            </div>
            <div class="p-6 sm:p-7">
                <div class="space-y-4">
                    <div class="rounded-lg bg-zinc-50 p-4 border border-zinc-200">
                        <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-2">Seu link de agendamento</p>
                        <div class="flex items-center gap-2">
                            <input type="text" :value="publicUrl" readonly class="{{ $inputClass }} !mt-0" x-ref="linkInput">
                            <button type="button" @click="copiarLink()" class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-barber-500 text-white hover:bg-barber-600 transition flex-shrink-0" title="Copiar link">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                            </button>
                            <a :href="publicUrl" target="_blank" class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-emerald-500 text-white hover:bg-emerald-600 transition flex-shrink-0" title="Abrir em nova aba">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                            </a>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-4 rounded-lg bg-barber-50 border border-barber-200">
                        <svg class="h-5 w-5 text-barber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="text-sm text-barber-700"><strong>Dica:</strong> Compartilhe este link via WhatsApp, email ou redes sociais para seus clientes agendarem.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Botões --}}
        <div class="flex flex-wrap gap-3">
            <button type="submit" :disabled="saving" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-barber-500 px-8 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-barber-600 active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed">
                <svg x-show="!saving" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <svg x-show="saving" class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                <span x-text="saving ? 'Salvando...' : 'Salvar Configurações'"></span>
            </button>
            <a :href="publicUrl" target="_blank" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-barber-500 px-8 py-3 text-xs font-bold uppercase tracking-[0.08em] text-barber-600 transition hover:bg-barber-50">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                Visualizar Página Pública
            </a>
        </div>
    </form>

    {{-- ===== Imagens do Carrossel ===== --}}
    <div class="{{ $cardClass }} overflow-hidden mt-8">
        <div class="border-b border-zinc-200 px-6 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-barber-100">
                    <svg class="h-5 w-5 text-barber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-zinc-900">Imagens do Carrossel</h3>
                    <p class="text-sm text-zinc-500">Adicione fotos da sua barbearia para a página pública</p>
                </div>
            </div>
        </div>
        <div class="p-6 sm:p-7">
            <div class="space-y-6">
                {{-- Upload --}}
                <div class="rounded-2xl border-2 border-dashed border-zinc-300 bg-zinc-50 p-6 text-center transition hover:bg-zinc-100 cursor-pointer"
                     :class="uploading ? 'opacity-60 pointer-events-none' : ''"
                     @click="$refs.imagemInput.click()"
                     @dragover.prevent="$el.classList.add('bg-zinc-100')"
                     @dragleave.prevent="$el.classList.remove('bg-zinc-100')"
                     @drop.prevent="uploadImagens($event.dataTransfer.files)">
                    <input type="file" multiple accept="image/*" class="hidden" x-ref="imagemInput" @change="uploadImagens($event.target.files)">
                    <template x-if="!uploading">
                        <div>
                            <svg class="mx-auto h-12 w-12 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            <p class="mt-2 text-sm font-medium text-zinc-700">Clique para selecionar imagens</p>
                            <p class="text-xs text-zinc-500">ou arraste para esta área</p>
                        </div>
                    </template>
                    <template x-if="uploading">
                        <div class="flex flex-col items-center">
                            <svg class="h-10 w-10 animate-spin text-barber-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                            <p class="mt-2 text-sm font-medium text-zinc-600">Enviando imagens...</p>
                        </div>
                    </template>
                </div>

                {{-- Galeria --}}
                <div x-show="imagens.length > 0">
                    <h4 class="font-semibold text-zinc-900 mb-4"><span x-text="imagens.length"></span> imagem(ns) adicionada(s)</h4>
                    <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
                        <template x-for="img in imagens" :key="img.id">
                            <div class="group relative rounded-lg overflow-hidden bg-zinc-100 aspect-[4/3]">
                                <img :src="img.url" alt="Imagem" class="h-full w-full object-cover">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                    <button type="button" @click="confirmDeleteId = img.id" class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-red-600 text-white hover:bg-red-700 transition" title="Remover">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <p x-show="imagens.length === 0" class="text-sm text-zinc-400 text-center py-4">Nenhuma imagem adicionada ainda.</p>
            </div>
        </div>
    </div>

    {{-- ===== Modal de confirmação de exclusão ===== --}}
    <div x-cloak x-show="confirmDeleteId !== null" @keydown.escape.window="confirmDeleteId = null"
         class="fixed inset-0 z-[90] flex items-center justify-center bg-zinc-900/60 backdrop-blur-sm p-4"
         x-transition.opacity>
        <div class="w-full max-w-sm rounded-3xl bg-white p-6 shadow-xl" @click.outside="confirmDeleteId = null">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-full bg-red-100">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-zinc-900">Remover imagem</h3>
                    <p class="text-sm text-zinc-500">Esta ação não pode ser desfeita.</p>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" @click="confirmDeleteId = null" class="rounded-2xl border border-zinc-300 px-5 py-2.5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-100">Cancelar</button>
                <button type="button" @click="removerImagem(confirmDeleteId)" :disabled="deleting" class="rounded-2xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700 disabled:opacity-60">
                    <span x-text="deleting ? 'Removendo...' : 'Remover'"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function agendaConfigApp(opts) {
    return {
        ...opts,
        saving: false,
        uploading: false,
        deleting: false,
        confirmDeleteId: null,
        errors: {},
        toast: { show: false, type: 'success', message: '', timer: null },

        notify(message, type = 'success') {
            clearTimeout(this.toast.timer);
            this.toast.message = message;
            this.toast.type = type;
            this.toast.show = true;
            this.toast.timer = setTimeout(() => { this.toast.show = false; }, 3500);
        },

        async salvar() {
            this.saving = true;
            this.errors = {};
            const fd = new FormData(this.$refs.form);
            fd.set('ativa', this.ativa ? 1 : 0);

            try {
                const res = await fetch(this.updateUrl, {
                    method: 'POST', // _method=PUT já vem no FormData
                    headers: { 'X-CSRF-TOKEN': this.csrf, 'Accept': 'application/json' },
                    body: fd,
                });

                if (res.status === 422) {
                    const data = await res.json();
                    this.errors = Object.fromEntries(Object.entries(data.errors || {}).map(([k, v]) => [k, Array.isArray(v) ? v[0] : v]));
                    this.notify(data.message || 'Verifique os campos destacados.', 'error');
                    return;
                }
                if (!res.ok) throw new Error('Falha ao salvar');

                const data = await res.json();
                this.notify(data.message || 'Configurações salvas!');
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
                const res = await fetch(this.uploadUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': this.csrf, 'Accept': 'application/json' },
                    body: fd,
                });

                if (res.status === 422) {
                    const data = await res.json();
                    const first = Object.values(data.errors || {})[0];
                    this.notify(Array.isArray(first) ? first[0] : (data.message || 'Imagem inválida.'), 'error');
                    return;
                }
                if (!res.ok) throw new Error('Falha no upload');

                const data = await res.json();
                this.imagens.push(...(data.imagens || []));
                this.notify(data.message || 'Imagens adicionadas!');
            } catch (e) {
                this.notify('Erro ao enviar imagens. Tente novamente.', 'error');
            } finally {
                this.uploading = false;
                this.$refs.imagemInput.value = '';
            }
        },

        async removerImagem(id) {
            this.deleting = true;
            try {
                const res = await fetch(`${this.deleteUrlBase}/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': this.csrf, 'Accept': 'application/json' },
                });
                if (!res.ok) throw new Error('Falha ao remover');

                this.imagens = this.imagens.filter(i => i.id !== id);
                this.notify('Imagem removida com sucesso!');
            } catch (e) {
                this.notify('Erro ao remover imagem.', 'error');
            } finally {
                this.deleting = false;
                this.confirmDeleteId = null;
            }
        },

        copiarLink() {
            const input = this.$refs.linkInput;
            input.select();
            input.setSelectionRange(0, 99999);
            const done = () => this.notify('Link copiado para a área de transferência!');
            if (navigator.clipboard) {
                navigator.clipboard.writeText(input.value).then(done).catch(() => { document.execCommand('copy'); done(); });
            } else {
                document.execCommand('copy');
                done();
            }
        },
    };
}
</script>
@endsection
