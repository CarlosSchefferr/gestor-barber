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
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500">Barbearia</p>
                <h1 class="mt-2 text-3xl font-bold leading-tight text-zinc-900 sm:text-4xl">Configurações da Agenda</h1>
            </div>
            <a href="{{ route('profile.settings') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700 transition hover:bg-zinc-100">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Voltar
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 animate-pulse">
            <p class="text-sm font-medium text-emerald-700">✓ {{ session('success') }}</p>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3">
            <ul class="list-disc pl-5 text-sm text-red-700">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('agenda.config.update') }}" method="POST" id="configForm" class="space-y-8">
        @csrf
        @method('PUT')

        <div class="space-y-8">
            <!-- Seção 1: Informações Básicas -->
            <div class="{{ $cardClass }} overflow-hidden">
                <div class="border-b border-zinc-200 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-barber-100">
                            <svg class="h-5 w-5 text-barber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"></path>
                            </svg>
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

            <!-- Seção 2: Horários de Atendimento -->
            <div class="{{ $cardClass }} overflow-hidden">
                <div class="border-b border-zinc-200 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-barber-100">
                            <svg class="h-5 w-5 text-barber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
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
                            <input type="time" name="horario_inicio" required value="{{ old('horario_inicio', $agendaConfig->horario_inicio) }}" class="{{ $inputClass }}">
                        </div>

                        <div>
                            <label class="text-sm font-semibold text-zinc-700">Hora de Término <span class="text-red-500">*</span></label>
                            <input type="time" name="horario_fim" required value="{{ old('horario_fim', $agendaConfig->horario_fim) }}" class="{{ $inputClass }}">
                        </div>

                        <div>
                            <label class="text-sm font-semibold text-zinc-700">Intervalo entre Agendamentos (minutos) <span class="text-red-500">*</span></label>
                            <input type="number" name="intervalo_slots" required min="15" max="120" value="{{ old('intervalo_slots', $agendaConfig->intervalo_slots) }}" class="{{ $inputClass }}" placeholder="30">
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
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seção 3: Imagens do Carrossel -->
            <div class="{{ $cardClass }} overflow-hidden">
                <div class="border-b border-zinc-200 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-barber-100">
                            <svg class="h-5 w-5 text-barber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-zinc-900">Imagens do Carrossel</h3>
                            <p class="text-sm text-zinc-500">Adicione fotos da sua barbearia para a página pública</p>
                        </div>
                    </div>
                </div>
                <div class="p-6 sm:p-7">
                    <div class="space-y-6">
                        <!-- Upload -->
                        <form action="{{ route('agenda.imagens.upload') }}" method="POST" enctype="multipart/form-data" id="imagemUploadForm">
                            @csrf
                            <div class="rounded-2xl border-2 border-dashed border-zinc-300 bg-zinc-50 p-6 text-center hover:bg-zinc-100 transition cursor-pointer" onclick="document.getElementById('imagemInput').click()">
                                <input type="file" name="imagens[]" multiple accept="image/*" class="hidden" id="imagemInput" onchange="document.getElementById('imagemUploadForm').submit()">
                                <svg class="mx-auto h-12 w-12 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                <p class="mt-2 text-sm font-medium text-zinc-700">Clique para selecionar imagens</p>
                                <p class="text-xs text-zinc-500">ou arraste para esta área</p>
                            </div>
                        </form>

                        <!-- Galeria de Imagens -->
                        @if($agendaConfig->imagens()->count())
                            <div class="mt-8">
                                <h4 class="font-semibold text-zinc-900 mb-4">{{ $agendaConfig->imagens()->count() }} imagem(ns) adicionada(s)</h4>
                                <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
                                    @foreach($agendaConfig->imagens as $imagem)
                                        <div class="group relative rounded-lg overflow-hidden bg-zinc-100">
                                            <img src="{{ asset('storage/' . $imagem->caminho_imagem) }}" alt="Imagem" class="h-32 w-full object-cover">
                                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center gap-2">
                                                <form action="{{ route('agenda.imagens.delete', $imagem) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-red-600 text-white hover:bg-red-700 transition" onclick="return confirm('Tem certeza que deseja remover?')">
                                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Seção 4: Link Compartilhável -->
            <div class="{{ $cardClass }} overflow-hidden">
                <div class="border-b border-zinc-200 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-barber-100">
                            <svg class="h-5 w-5 text-barber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.658 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                            </svg>
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
                                <input type="text" value="{{ $agendaConfig->getPublicUrl() }}" readonly class="{{ $inputClass }} !mt-0" id="linkInput">
                                <button type="button" onclick="copiarLink()" class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-barber-500 text-white hover:bg-barber-600 transition flex-shrink-0" title="Copiar link">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                    </svg>
                                </button>
                                <a href="{{ $agendaConfig->getPublicUrl() }}" target="_blank" class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-emerald-500 text-white hover:bg-emerald-600 transition flex-shrink-0" title="Abrir em nova aba">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-4 rounded-lg bg-barber-50 border border-barber-200">
                            <svg class="h-5 w-5 text-barber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-sm text-barber-700"><strong>Dica:</strong> Compartilhe este link via WhatsApp, email ou redes sociais para seus clientes agendarem.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seção 5: Status -->
            <div class="{{ $cardClass }} overflow-hidden">
                <div class="border-b border-zinc-200 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-barber-100">
                            <svg class="h-5 w-5 text-barber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-zinc-900">Status da Agenda</h3>
                            <p class="text-sm text-zinc-500">Seu link de agendamento está {{ $agendaConfig->ativa ? 'ativo' : 'inativo' }}</p>
                        </div>
                    </div>
                </div>
                <div class="p-6 sm:p-7">
                    <div class="flex items-center gap-4">
                        <span class="inline-flex rounded-full {{ $agendaConfig->ativa ? 'bg-emerald-100 text-emerald-700' : 'bg-zinc-100 text-zinc-700' }} px-3 py-1 text-xs font-semibold">
                            {{ $agendaConfig->ativa ? '✓ Ativo' : '○ Inativo' }}
                        </span>
                        <p class="text-sm text-zinc-600">
                            {{ $agendaConfig->ativa ? 'Seus clientes podem agendar normalmente através do link público.' : 'Seu link público está desativado. Nenhum cliente pode agendar no momento.' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Botão de Submit -->
            <div class="flex gap-3">
                <button type="submit" form="configForm" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-barber-500 px-8 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-barber-600 active:scale-95">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Salvar Todas as Configurações
                </button>
                <a href="{{ $agendaConfig->getPublicUrl() }}" target="_blank" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-barber-500 px-8 py-3 text-xs font-bold uppercase tracking-[0.08em] text-barber-600 transition hover:bg-barber-50">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                    </svg>
                    Visualizar Página Pública
                </a>
            </div>
        </div>
    </form>
</div>

<script>
function copiarLink() {
    const input = document.getElementById('linkInput');
    input.select();
    document.execCommand('copy');
    alert('Link copiado para a área de transferência!');
}
</script>
@endsection
