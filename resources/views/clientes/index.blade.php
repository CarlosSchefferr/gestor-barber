@extends('layouts.app')

@section('content')
@php
    $inputClass = 'mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20';
    $cardClass = 'rounded-3xl border border-zinc-200 bg-white/95 shadow-sm';

    // Opções para selects de filtro
    $opcoesBarbeiros = ['' => 'Todos barbeiros'];
    foreach($barbeiros ?? [] as $barbeiro) {
        $opcoesBarbeiros[$barbeiro->id] = $barbeiro->name;
    }

    $opcoesServicos = ['' => 'Todos serviços'];
    foreach($servicos ?? [] as $servico) {
        $opcoesServicos[$servico] = $servico;
    }

    $opcoesProdutos = ['' => 'Todos produtos'];
    foreach($produtos ?? [] as $produto) {
        $opcoesProdutos[$produto->id] = $produto->name;
    }
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-8 rounded-3xl border border-zinc-200 bg-white px-6 py-7 shadow-sm sm:px-8">
        <div class="flex flex-col gap-5">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500">Gestão</p>
                <h1 class="mt-2 text-3xl font-bold leading-tight text-zinc-900 sm:text-4xl">Clientes</h1>
            </div>
            @if(auth()->user()->role !== 'barber')
            <div class="flex flex-wrap items-center gap-3">
                <button type="button" onclick="abrirModalNovoCliente()" class="inline-flex items-center justify-center rounded-2xl bg-barber-500 px-4 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-barber-600">
                    Novo cliente
                </button>
            </div>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3">
            <p class="text-sm font-medium text-emerald-700">{{ session('success') }}</p>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3">
            <ul class="list-disc pl-5 text-sm text-red-700">
                @foreach($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="{{ $cardClass }} p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Mais atendido</p>
            <p class="mt-3 text-2xl font-bold text-zinc-900 truncate" title="{{ $mostAttended->cliente ?? 'Sem dados' }}">
                {{ $mostAttended->cliente ?? '-' }}
            </p>
            <p class="mt-1 text-sm text-zinc-500">{{ $mostAttended->count ?? 0 }} atendimentos</p>
        </div>

        <div class="{{ $cardClass }} p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Mais lucrativo</p>
            <p class="mt-3 text-2xl font-bold text-emerald-700 truncate" title="{{ $mostProfitable->cliente ?? 'Sem dados' }}">
                {{ $mostProfitable->cliente ?? '-' }}
            </p>
            <p class="mt-1 text-sm text-zinc-500">R$ {{ number_format($mostProfitable->valor ?? 0, 2, ',', '.') }}</p>
        </div>
    </div>

    <div class="{{ $cardClass }} mb-8 p-6 sm:p-7">
        <div class="mb-5 flex items-center justify-between">
            <h2 class="text-lg font-bold text-zinc-900">Filtros</h2>
            <span class="rounded-full border border-zinc-200 bg-zinc-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-zinc-500">Busca avançada</span>
        </div>

        <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div>
                <label class="text-sm font-semibold text-zinc-700 block mb-2">Buscar por nome</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Digite o nome do cliente..." class="{{ $inputClass }} !mt-0">
            </div>

            <div>
                <label class="text-sm font-semibold text-zinc-700 block mb-2">Status</label>
                <x-custom-select
                    name="status"
                    :options="[
                        '' => 'Todos',
                        'active' => 'Ativos',
                        'inactive' => 'Inativos'
                    ]"
                    :value="request('status', '')"
                    placeholder="Selecione o status"
                />
            </div>

            <div>
                <label class="text-sm font-semibold text-zinc-700 block mb-2">Bairro</label>
                <x-custom-select
                    name="bairro"
                    :options="$opcoesBairros ?? ['' => 'Todos bairros']"
                    :value="request('bairro', '')"
                    placeholder="Todos bairros"
                />
            </div>

            <div>
                <label class="text-sm font-semibold text-zinc-700 block mb-2">Último atendimento</label>
                <x-custom-select
                    name="ultimo_atendimento"
                    :options="[
                        '' => 'Todos',
                        '7' => 'Últimos 7 dias',
                        '15' => 'Últimos 15 dias',
                        '30' => 'Últimos 30 dias',
                        '60' => 'Últimos 60 dias',
                        '90' => 'Últimos 90 dias',
                        'nunca' => 'Nunca atendido'
                    ]"
                    :value="request('ultimo_atendimento', '')"
                    placeholder="Filtrar por atendimento"
                />
            </div>

            <div class="md:col-span-2 lg:col-span-4 flex flex-wrap items-center justify-center gap-3 pt-2">
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-barber-500 px-6 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white transition hover:bg-barber-600">
                    Aplicar filtros
                </button>
                <a href="{{ route('clientes.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-zinc-300 bg-white px-6 py-3 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700 transition hover:bg-zinc-100">
                    Limpar
                </a>
            </div>
        </form>
    </div>

    <div class="{{ $cardClass }} overflow-hidden">
        <div class="border-b border-zinc-200 px-6 py-4">
            <h3 class="text-lg font-bold text-zinc-900">Lista de clientes</h3>
            <p class="mt-1 text-sm text-zinc-500">Todos os clientes cadastrados no sistema</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-zinc-50">
                    <tr>
                        <th class="flex-1 px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Cliente</th>
                        <th class="flex-1 px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Contato</th>
                        <th class="flex-1 px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Último atendimento</th>
                        <th class="w-[160px] px-6 py-3 text-center text-xs font-bold uppercase tracking-wide text-zinc-500">Status</th>
                        <th class="w-[140px] px-6 py-3 text-center text-xs font-bold uppercase tracking-wide text-zinc-500">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 bg-white">
                    @forelse($clientes as $cliente)
                        <tr class="transition hover:bg-zinc-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if($cliente->foto)
                                        <img src="{{ asset('storage/' . $cliente->foto) }}" alt="{{ $cliente->nome }}" class="h-10 w-10 rounded-full object-cover">
                                    @else
                                        <div class="h-10 w-10 rounded-full bg-barber-500 flex items-center justify-center text-white font-bold text-sm">
                                            {{ strtoupper(substr($cliente->nome, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-semibold text-zinc-900">{{ $cliente->nome }}</p>
                                        @if($cliente->data_nascimento)
                                            <p class="text-xs text-zinc-500">{{ \Carbon\Carbon::parse($cliente->data_nascimento)->format('d/m/Y') }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-zinc-900">{{ $cliente->telefone }}</p>
                                <p class="text-xs text-zinc-500">{{ $cliente->email ?: '-' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                @if($cliente->last_appointment_at)
                                    <p class="text-sm text-zinc-900">{{ \Carbon\Carbon::parse($cliente->last_appointment_at)->format('d/m/Y') }}</p>
                                    <p class="text-xs text-zinc-500">{{ \Carbon\Carbon::parse($cliente->last_appointment_at)->diffForHumans() }}</p>
                                @else
                                    <span class="text-sm text-zinc-400">Nunca atendido</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($cliente->active)
                                    <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">Ativo</span>
                                @else
                                    <span class="inline-flex rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-semibold text-zinc-700">Inativo</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" onclick="abrirModalDetalhesCliente({{ $cliente->id }})" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-600 transition hover:bg-zinc-50 hover:text-barber-600" title="Ver detalhes">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                    @if(auth()->user()->role !== 'barber')
                                    <button type="button" onclick="abrirModalEditarCliente({{ $cliente->id }})" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-600 transition hover:bg-zinc-50 hover:text-barber-600" title="Editar">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100">
                                    <svg class="h-6 w-6 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-sm font-bold text-zinc-900">Nenhum cliente encontrado</h3>
                                <p class="mt-1 text-sm text-zinc-500">Comece cadastrando um novo cliente.</p>
                                @if(auth()->user()->role !== 'barber')
                                <button type="button" onclick="abrirModalNovoCliente()" class="mt-4 inline-flex items-center justify-center rounded-2xl bg-barber-500 px-4 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-barber-600">
                                    Novo cliente
                                </button>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($clientes->hasPages())
            <div class="border-t border-zinc-200 bg-white px-6 py-4">
                {{ $clientes->links() }}
            </div>
        @endif
    </div>
</div>

@if(auth()->user()->role !== 'barber')
<div id="modalNovoCliente" class="fixed inset-0 z-50 hidden items-center justify-center bg-zinc-900/60 backdrop-blur-sm p-4">
    <div class="w-full max-w-2xl rounded-3xl border border-zinc-200 bg-white shadow-2xl flex flex-col max-h-[90vh]">
        <div class="p-6 sm:p-8 pb-5 shrink-0">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-barber-500">Cadastro</p>
                <h3 class="mt-2 text-2xl font-bold text-zinc-900">Novo cliente</h3>
            </div>
        </div>

        <div class="px-6 sm:px-8 py-6 overflow-y-auto flex-1 border-t border-zinc-200">
            <form id="formNovoCliente" method="POST" action="{{ route('clientes.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="space-y-6">
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Nome completo <span class="text-red-500">*</span></label>
                        <input type="text" name="nome" id="inputNomeNovo" required class="{{ $inputClass }}" placeholder="Digite o nome completo" onblur="checkDuplicateName(this.value)">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-semibold text-zinc-700">Data de nascimento <span class="text-red-500">*</span></label>
                            <input type="date" name="data_nascimento" required class="{{ $inputClass }}">
                        </div>

                        <div>
                            <label class="text-sm font-semibold text-zinc-700">Telefone <span class="text-red-500">*</span></label>
                            <input type="text" name="telefone" id="inputTelefoneNovo" required class="{{ $inputClass }}" placeholder="(00) 00000-0000" onblur="checkDuplicatePhone(this.value)">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-semibold text-zinc-700">E-mail</label>
                            <input type="email" name="email" class="{{ $inputClass }}" placeholder="email@exemplo.com">
                        </div>

                        <div>
                            <label class="text-sm font-semibold text-zinc-700">CEP</label>
                            <input type="text" name="cep" class="{{ $inputClass }}" placeholder="00000-000" maxlength="10">
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Bairro</label>
                        <input type="text" name="bairro" class="{{ $inputClass }}" placeholder="Digite o bairro">
                    </div>

                    <div class="flex flex-col items-center p-5 bg-zinc-50 rounded-2xl border border-zinc-200">
                        <div id="photoPreviewNovo" class="w-24 h-24 rounded-lg bg-zinc-200 flex items-center justify-center overflow-hidden mb-3 border-2 border-white shadow-lg hidden">
                            <img src="" alt="Preview" class="w-full h-full object-cover">
                        </div>
                        <label class="text-sm font-semibold text-zinc-700 mb-2">Foto do cliente</label>
                        <input type="file" name="foto" accept="image/jpeg,image/jpg,image/png" class="hidden" id="fotoInput" onchange="previewPhoto(this, 'photoPreviewNovo')">
                        <button type="button" onclick="document.getElementById('fotoInput').click()" class="inline-flex items-center gap-2 rounded-xl bg-white border border-zinc-300 px-4 py-2 text-xs font-semibold text-zinc-700 transition hover:bg-zinc-100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Escolher foto
                        </button>
                        <p class="text-xs text-zinc-400 mt-2">Opcional • JPG, PNG</p>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Observações</label>
                        <textarea name="observacoes" rows="3" class="{{ $inputClass }}" placeholder="Observações adicionais sobre o cliente"></textarea>
                    </div>
                </div>

                <div class="mt-8 flex justify-center gap-3">
                    <button type="button" onclick="fecharModalNovoCliente()" class="rounded-2xl border border-zinc-300 bg-white px-6 py-3 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700 transition hover:bg-zinc-100">
                        Cancelar
                    </button>
                    <button type="submit" class="rounded-2xl bg-barber-500 px-6 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white transition hover:bg-barber-600">
                        Salvar cliente
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<div id="modalDetalhesCliente" class="fixed inset-0 z-50 hidden items-center justify-center bg-zinc-900/60 backdrop-blur-sm p-4" x-data="{ abaAtiva: 'dados' }">
    <div class="w-full rounded-3xl border border-zinc-200 bg-white shadow-2xl flex flex-col transition-all duration-300" :class="abaAtiva === 'atendimentos' ? 'max-w-7xl max-h-[95vh]' : 'max-w-4xl max-h-[90vh]'">
        <div class="p-6 sm:p-8 pb-5 shrink-0">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-barber-500">Cliente</p>
                <h3 class="mt-2 text-2xl font-bold text-zinc-900">Detalhes</h3>
            </div>
        </div>

        <div class="flex shrink-0 px-6 sm:px-8 pb-6 gap-2 border-b border-zinc-200">
            <button type="button" @click="abaAtiva = 'dados'" :class="abaAtiva === 'dados' ? 'bg-barber-50 border-barber-500 text-barber-600 font-semibold' : 'bg-white border-zinc-300 text-zinc-500 hover:text-zinc-700 hover:border-zinc-400'" class="flex-1 py-2.5 text-sm font-medium transition-all duration-200 rounded-xl border">
                Dados do cliente
            </button>
            <button type="button" @click="abaAtiva = 'atendimentos'; setTimeout(() => { loadClienteStatistics(currentClienteId); loadClienteHistory(currentClienteId); }, 100)" :class="abaAtiva === 'atendimentos' ? 'bg-barber-50 border-barber-500 text-barber-600 font-semibold' : 'bg-white border-zinc-300 text-zinc-500 hover:text-zinc-700 hover:border-zinc-400'" class="flex-1 py-2.5 text-sm font-medium transition-all duration-200 rounded-xl border">
                Atendimentos
            </button>
        </div>

        <div class="px-6 sm:px-8 py-6 overflow-y-auto flex-1">
            <div x-show="abaAtiva === 'dados'" class="tab-content">
                <div id="clienteDataContent" class="grid grid-cols-1 gap-6 md:grid-cols-2"></div>
            </div>

            <div x-show="abaAtiva === 'atendimentos'" class="tab-content">
                <div class="mb-6 rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                    <h3 class="text-sm font-bold text-zinc-900 mb-4">Filtros do Histórico</h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
                        <div>
                            <label class="text-sm font-semibold text-zinc-700 block mb-2">Status</label>
                            <x-custom-select
                                id="filterStatus"
                                name="filterStatus"
                                :options="[
                                    '' => 'Todos os status',
                                    'atendido' => 'Atendido',
                                    'agendado' => 'Agendado',
                                    'cancelado' => 'Cancelado',
                                    'não compareceu' => 'Não compareceu'
                                ]"
                                placeholder="Filtrar por status"
                            />
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-zinc-700 block mb-2">Data início</label>
                            <input type="date" id="filterDataInicio" class="{{ $inputClass }} !mt-0">
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-zinc-700 block mb-2">Data fim</label>
                            <input type="date" id="filterDataFim" class="{{ $inputClass }} !mt-0">
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-zinc-700 block mb-2">Barbeiro</label>
                            <x-custom-select
                                name="filterBarbeiro"
                                :options="$opcoesBarbeiros"
                                placeholder="Todos barbeiros"
                            />
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-zinc-700 block mb-2">Serviço</label>
                            <x-custom-select
                                name="filterServico"
                                :options="$opcoesServicos"
                                placeholder="Todos serviços"
                            />
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-zinc-700 block mb-2">Produto</label>
                            <x-custom-select
                                name="filterProduto"
                                :options="$opcoesProdutos"
                                placeholder="Todos produtos"
                            />
                        </div>
                    </div>
                    <div class="mt-6 flex justify-center gap-3">
                        <button type="button" onclick="loadClienteHistory(currentClienteId)" class="rounded-2xl bg-barber-500 px-6 py-2.5 text-xs font-bold text-white transition hover:bg-barber-600">Aplicar filtros</button>
                        <button type="button" onclick="limparFiltros()" class="rounded-2xl border border-zinc-300 bg-white px-6 py-2.5 text-xs font-bold text-zinc-700 transition hover:bg-zinc-100">Limpar</button>
                    </div>
                </div>

                <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="{{ $cardClass }} p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Dias sem atendimento</p>
                        <p id="indicatorDaysSince" class="mt-2 text-3xl font-bold text-zinc-900">-</p>
                    </div>
                    <div class="{{ $cardClass }} p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Atendimentos</p>
                        <p id="indicatorAtendCount" class="mt-2 text-3xl font-bold text-zinc-900">-</p>
                        <p id="indicatorMostService" class="mt-1 text-xs text-zinc-500"></p>
                    </div>
                    <div class="{{ $cardClass }} p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Produtos comprados</p>
                        <p id="indicatorProdCount" class="mt-2 text-3xl font-bold text-zinc-900">-</p>
                        <p id="indicatorMostProduct" class="mt-1 text-xs text-zinc-500"></p>
                    </div>
                    <div class="{{ $cardClass }} p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Valor gasto</p>
                        <p id="indicatorValorTotal" class="mt-2 text-xl font-bold text-emerald-700">-</p>
                        <p id="indicatorValorDetalhes" class="mt-1 text-xs text-zinc-500"></p>
                    </div>
                </div>

                <div class="rounded-2xl border border-zinc-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-zinc-50">
                                <tr>
                                    <th class="flex-1 px-4 py-3 text-left text-xs font-semibold uppercase text-zinc-600">Status</th>
                                    <th class="flex-1 px-4 py-3 text-left text-xs font-semibold uppercase text-zinc-600">Data/Hora</th>
                                    <th class="flex-1 px-4 py-3 text-left text-xs font-semibold uppercase text-zinc-600">Barbeiro</th>
                                    <th class="w-[120px] px-4 py-3 text-center text-xs font-semibold uppercase text-zinc-600">Serviços</th>
                                    <th class="w-[120px] px-4 py-3 text-center text-xs font-semibold uppercase text-zinc-600">Produtos</th>
                                    <th class="w-[160px] px-4 py-3 text-center text-xs font-semibold uppercase text-zinc-600">Valor</th>
                                </tr>
                            </thead>
                            <tbody id="historyTableBody" class="divide-y divide-zinc-200 bg-white">
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-zinc-500">Carregando...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="border-t border-zinc-200 px-6 sm:px-8 py-4 shrink-0 flex justify-center">
            <button type="button" onclick="fecharModalDetalhesCliente()" class="rounded-2xl border border-zinc-300 bg-white px-6 py-3 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700 transition hover:bg-zinc-100">
                Fechar
            </button>
        </div>
    </div>
</div>

@if(auth()->user()->role !== 'barber')
<div id="modalEditarCliente" class="fixed inset-0 z-50 hidden items-center justify-center bg-zinc-900/60 backdrop-blur-sm p-4">
    <div class="w-full max-w-2xl rounded-3xl border border-zinc-200 bg-white shadow-2xl flex flex-col max-h-[90vh]">
        <div class="p-6 sm:p-8 pb-5 shrink-0">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-barber-500">Edição</p>
                    <h3 class="mt-2 text-2xl font-bold text-zinc-900">Editar cliente</h3>
                </div>
                <div id="editClienteStatusBadge"></div>
            </div>
        </div>

        <div class="px-6 sm:px-8 py-6 overflow-y-auto flex-1 border-t border-zinc-200">
            <form id="formEditarCliente" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div id="editClienteContent" class="space-y-6"></div>
            </form>
        </div>

        <div class="border-t border-zinc-200 px-6 sm:px-8 py-4 shrink-0 bg-white rounded-b-3xl">
            <div class="flex flex-wrap items-center justify-center gap-3">
                <button type="button" id="btnToggleStatusEdit" onclick="toggleClienteStatus()" class="rounded-2xl px-5 py-3 text-xs font-bold uppercase tracking-[0.08em] transition">
                    Ativar/Inativar
                </button>
                <button type="button" onclick="fecharModalEditarCliente()" class="rounded-2xl border border-zinc-300 bg-white px-6 py-3 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700 transition hover:bg-zinc-100">
                    Fechar
                </button>
                <button type="button" onclick="document.getElementById('formEditarCliente').submit()" class="rounded-2xl bg-barber-500 px-6 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white transition hover:bg-barber-600">
                    Atualizar cliente
                </button>
            </div>
        </div>
    </div>
</div>
@endif

<div id="modalDuplicateConfirm" class="fixed inset-0 z-[60] hidden items-center justify-center bg-zinc-900/60 backdrop-blur-sm p-4">
    <div class="w-full max-w-md rounded-3xl border border-zinc-200 bg-white shadow-2xl">
        <div class="p-6 sm:p-8">
            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-orange-100">
                <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-zinc-900 mb-2" id="duplicateTitle">Atenção</h3>
            <p class="text-sm text-zinc-600 mb-6" id="duplicateMessage"></p>
            <div id="duplicateActions" class="flex flex-col gap-3"></div>
        </div>
    </div>
</div>

<div id="confirmStatusModal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-zinc-900/60 backdrop-blur-sm p-4">
    <div class="w-full max-w-md rounded-3xl border border-zinc-200 bg-white shadow-2xl">
        <div class="p-6 sm:p-8 text-center">
            <div id="confirmStatusIcon" class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full"></div>
            <h3 id="confirmStatusTitle" class="text-xl font-bold text-zinc-900 mb-2"></h3>
            <p id="confirmStatusMessage" class="text-sm text-zinc-600 mb-6"></p>
            <div class="flex justify-center gap-3">
                <button type="button" onclick="closeConfirmStatusModal()" class="inline-flex items-center justify-center rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700 transition hover:bg-zinc-100">
                    Cancelar
                </button>
                <button type="button" id="confirmStatusBtn" class="inline-flex items-center justify-center rounded-2xl px-4 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition">
                    Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentClienteId = null;
let currentClienteActive = null;
let duplicateCheckTimeout = null;
let phoneCheckedAndClear = false;

function abrirModalNovoCliente() {
    document.getElementById('modalNovoCliente').classList.remove('hidden');
    document.getElementById('modalNovoCliente').classList.add('flex');
    document.getElementById('formNovoCliente').reset();
    document.getElementById('photoPreviewNovo')?.classList.add('hidden');
    phoneCheckedAndClear = false;
}

function fecharModalNovoCliente() {
    document.getElementById('modalNovoCliente').classList.add('hidden');
    document.getElementById('modalNovoCliente').classList.remove('flex');
}

function abrirModalDetalhesCliente(clienteId) {
    currentClienteId = clienteId;
    document.getElementById('modalDetalhesCliente').classList.remove('hidden');
    document.getElementById('modalDetalhesCliente').classList.add('flex');
    loadClienteDetails(clienteId);
}

function fecharModalDetalhesCliente() {
    document.getElementById('modalDetalhesCliente').classList.add('hidden');
    document.getElementById('modalDetalhesCliente').classList.remove('flex');
    currentClienteId = null;
}

function abrirModalEditarCliente(clienteId) {
    currentClienteId = clienteId;
    document.getElementById('modalEditarCliente').classList.remove('hidden');
    document.getElementById('modalEditarCliente').classList.add('flex');
    loadClienteForEdit(clienteId);
}

function fecharModalEditarCliente() {
    document.getElementById('modalEditarCliente').classList.add('hidden');
    document.getElementById('modalEditarCliente').classList.remove('flex');
    currentClienteId = null;
    currentClienteActive = null;
}

function updateToggleStatusButton() {
    const btn = document.getElementById('btnToggleStatusEdit');
    if (!btn) return;

    if (currentClienteActive) {
        btn.textContent = 'Inativar cliente';
        btn.className = 'rounded-2xl border border-red-300 bg-red-50 text-red-700 hover:bg-red-100 px-5 py-3 text-xs font-bold uppercase tracking-[0.08em] transition';
    } else {
        btn.textContent = 'Ativar cliente';
        btn.className = 'rounded-2xl border border-emerald-300 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 px-5 py-3 text-xs font-bold uppercase tracking-[0.08em] transition';
    }
}

function toggleClienteStatus() {
    if (!currentClienteId) return;
    showConfirmStatusModal();
}

function showConfirmStatusModal() {
    const modal = document.getElementById('confirmStatusModal');
    const icon = document.getElementById('confirmStatusIcon');
    const title = document.getElementById('confirmStatusTitle');
    const message = document.getElementById('confirmStatusMessage');
    const btn = document.getElementById('confirmStatusBtn');

    if (currentClienteActive) {
        icon.className = 'mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-red-100';
        icon.innerHTML = '<svg class="h-7 w-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>';
        title.textContent = 'Inativar cliente';
        message.textContent = 'Tem certeza que deseja inativar este cliente? Ele não aparecerá mais nas listagens ativas.';
        btn.textContent = 'Sim, inativar';
        btn.className = 'inline-flex items-center justify-center rounded-2xl bg-red-600 px-4 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-red-700';
    } else {
        icon.className = 'mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100';
        icon.innerHTML = '<svg class="h-7 w-7 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
        title.textContent = 'Ativar cliente';
        message.textContent = 'Tem certeza que deseja ativar este cliente? Ele voltará a aparecer nas listagens ativas.';
        btn.textContent = 'Sim, ativar';
        btn.className = 'inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-4 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-emerald-700';
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeConfirmStatusModal() {
    const modal = document.getElementById('confirmStatusModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function showSuccessNotification(message) {
    const container = document.createElement('div');
    container.className = 'fixed top-4 right-4 z-[100]';
    container.innerHTML = `
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 shadow-lg">
            <p class="text-sm font-medium text-emerald-700">${message}</p>
        </div>
    `;
    document.body.appendChild(container);

    setTimeout(() => {
        container.remove();
    }, 3000);
}

async function executeToggleStatus() {
    if (!currentClienteId) return;

    try {
        const response = await fetch(`/clientes/${currentClienteId}/toggle-status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });

        if (response.ok) {
            closeConfirmStatusModal();
            const message = currentClienteActive ? 'Cliente inativado com sucesso!' : 'Cliente ativado com sucesso!';
            showSuccessNotification(message);
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            closeConfirmStatusModal();
            alert('Erro ao alterar status do cliente');
        }
    } catch (error) {
        console.error(error);
        closeConfirmStatusModal();
        alert('Erro ao alterar status do cliente');
    }
}

async function loadClienteDetails(clienteId) {
    try {
        const response = await fetch(`/clientes/${clienteId}`);
        const cliente = await response.json();
        const content = document.getElementById('clienteDataContent');

        let infoUsuario = cliente.user ? `Registrado por: ${cliente.user.name}` : '';

        content.innerHTML = `
            <div class="md:col-span-2 flex items-center gap-4">
                ${cliente.foto ?
                    `<img src="/storage/${cliente.foto}" alt="${cliente.nome}" class="h-20 w-20 rounded-full object-cover border-2 border-barber-500">` :
                    `<div class="h-20 w-20 rounded-full bg-barber-500 flex items-center justify-center text-white text-2xl font-bold">${cliente.nome.charAt(0).toUpperCase()}</div>`
                }
                <div>
                    <h3 class="text-2xl font-bold text-zinc-900">${cliente.nome}</h3>
                    ${cliente.data_nascimento ? `<p class="text-sm text-zinc-500">Nascimento: ${formatDate(cliente.data_nascimento)}</p>` : ''}
                </div>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Telefone</p>
                <p class="mt-1 text-base text-zinc-900">${cliente.telefone || '—'}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">E-mail</p>
                <p class="mt-1 text-base text-zinc-900">${cliente.email || '—'}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">CEP</p>
                <p class="mt-1 text-base text-zinc-900">${cliente.cep || '—'}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Bairro</p>
                <p class="mt-1 text-base text-zinc-900">${cliente.bairro || '—'}</p>
            </div>
            <div class="md:col-span-2">
                <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Observações</p>
                <p class="mt-1 text-base text-zinc-900">${cliente.observacoes || '—'}</p>
            </div>
            <div class="md:col-span-2 pt-4 border-t border-zinc-200">
                <div class="flex flex-col gap-1 text-xs text-zinc-500">
                    <span>Cadastrado em: ${formatDate(cliente.created_at)} ${infoUsuario}</span>
                    ${cliente.updated_at ? `<span>Atualizado em: ${formatDate(cliente.updated_at)}</span>` : ''}
                </div>
            </div>
        `;
    } catch (error) {
        console.error(error);
    }
}

async function loadClienteForEdit(clienteId) {
    try {
        const response = await fetch(`/clientes/${clienteId}`);
        const cliente = await response.json();
        const form = document.getElementById('formEditarCliente');
        form.action = `/clientes/${clienteId}`;
        const content = document.getElementById('editClienteContent');

        currentClienteActive = cliente.active;
        updateToggleStatusButton();

        const statusBadge = document.getElementById('editClienteStatusBadge');
        if (statusBadge) {
            statusBadge.innerHTML = cliente.active
                ? '<span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">Ativo</span>'
                : '<span class="inline-flex rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-semibold text-zinc-700">Inativo</span>';
        }

        content.innerHTML = `
            <div>
                <label class="text-sm font-semibold text-zinc-700">Nome completo <span class="text-red-500">*</span></label>
                <input type="text" name="nome" value="${cliente.nome}" required class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-semibold text-zinc-700">Data de nascimento <span class="text-red-500">*</span></label>
                    <input type="date" name="data_nascimento" value="${cliente.data_nascimento ? cliente.data_nascimento.split('T')[0] : ''}" required class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20">
                </div>
                <div>
                    <label class="text-sm font-semibold text-zinc-700">Telefone <span class="text-red-500">*</span></label>
                    <input type="text" name="telefone" value="${cliente.telefone || ''}" required class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-semibold text-zinc-700">E-mail</label>
                    <input type="email" name="email" value="${cliente.email || ''}" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20">
                </div>
                <div>
                    <label class="text-sm font-semibold text-zinc-700">CEP</label>
                    <input type="text" name="cep" value="${cliente.cep || ''}" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20">
                </div>
            </div>

            <div>
                <label class="text-sm font-semibold text-zinc-700">Bairro</label>
                <input type="text" name="bairro" value="${cliente.bairro || ''}" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20">
            </div>

            <div class="flex flex-col items-center p-5 bg-zinc-50 rounded-2xl border border-zinc-200">
                <div class="relative w-24 h-24 rounded-lg bg-zinc-200 flex items-center justify-center overflow-hidden mb-3 border-2 border-white shadow-lg">
                    <div id="photoSemFotoEdit" class="absolute inset-0 flex items-center justify-center text-zinc-400 text-sm font-semibold text-center px-2 ${cliente.foto ? 'hidden' : ''}">Sem foto</div>
                    <img id="photoImgEdit" src="${cliente.foto ? '/storage/' + cliente.foto : ''}" alt="Preview" class="w-full h-full object-cover ${cliente.foto ? '' : 'hidden'}">
                </div>
                <label class="text-sm font-semibold text-zinc-700 mb-2">Foto do cliente</label>
                <input type="file" name="foto" accept="image/jpeg,image/jpg,image/png" class="hidden" id="fotoInputEdit" onchange="previewPhotoEdit(this)">
                <button type="button" onclick="document.getElementById('fotoInputEdit').click()" class="inline-flex items-center gap-2 rounded-xl bg-white border border-zinc-300 px-4 py-2 text-xs font-semibold text-zinc-700 transition hover:bg-zinc-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Escolher foto
                </button>
                <p class="text-xs text-zinc-400 mt-2">Opcional • JPG, PNG</p>
            </div>

            <div>
                <label class="text-sm font-semibold text-zinc-700">Observações</label>
                <textarea name="observacoes" rows="3" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20">${cliente.observacoes || ''}</textarea>
            </div>
        `;
    } catch (error) {
        console.error(error);
    }
}

async function loadClienteStatistics(clienteId) {
    try {
        const response = await fetch(`/clientes/${clienteId}/statistics`);
        const stats = await response.json();

        document.getElementById('indicatorDaysSince').textContent = stats.days_since_last_appointment !== null ? stats.days_since_last_appointment : 'N/A';
        document.getElementById('indicatorAtendCount').textContent = stats.atendimentos_count || 0;
        document.getElementById('indicatorMostService').textContent = stats.most_frequent_service ? `Mais frequente: ${stats.most_frequent_service}` : '';
        document.getElementById('indicatorProdCount').textContent = stats.produtos_count || 0;
        document.getElementById('indicatorMostProduct').textContent = stats.most_bought_product ? `Mais comprado: ${stats.most_bought_product}` : '';
        document.getElementById('indicatorValorTotal').textContent = 'R$ ' + formatCurrency(stats.valor_total || 0);
        document.getElementById('indicatorValorDetalhes').textContent = `Serviços: R$ ${formatCurrency(stats.valor_servicos || 0)} | Produtos: R$ ${formatCurrency(stats.valor_produtos || 0)}`;
    } catch (error) {
        console.error(error);
    }
}

async function loadClienteHistory(clienteId) {
    try {
        const statusInput = document.querySelector('input[name="filterStatus"]');
        const barbeiroInput = document.querySelector('input[name="filterBarbeiro"]');
        const servicoInput = document.querySelector('input[name="filterServico"]');
        const produtoInput = document.querySelector('input[name="filterProduto"]');

        const status = statusInput?.value || '';
        const dataInicio = document.getElementById('filterDataInicio')?.value || '';
        const dataFim = document.getElementById('filterDataFim')?.value || '';
        const barbeiroId = barbeiroInput?.value || '';
        const servico = servicoInput?.value || '';
        const produtoId = produtoInput?.value || '';

        const params = new URLSearchParams({
            status,
            data_inicio: dataInicio,
            data_fim: dataFim,
            barbeiro_id: barbeiroId,
            servico,
            produto_id: produtoId
        });

        const response = await fetch(`/clientes/${clienteId}/history?${params}`);
        const data = await response.json();
        const tbody = document.getElementById('historyTableBody');

        if (!data.data || data.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-8 text-center text-zinc-500">Nenhum atendimento no histórico.</td></tr>';
            return;
        }

        tbody.innerHTML = data.data.map(atend => {
            const statusClass = {
                'atendido': 'bg-emerald-100 text-emerald-700',
                'agendado': 'bg-blue-100 text-blue-700',
                'cancelado': 'bg-red-100 text-red-700',
                'não compareceu': 'bg-orange-100 text-orange-700'
            }[atend.status?.toLowerCase()] || 'bg-zinc-100 text-zinc-700';

            return `
                <tr class="hover:bg-zinc-50">
                    <td class="px-4 py-3">
                        <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold ${statusClass}">
                            ${atend.status}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-zinc-700">${formatDateTime(atend.data_hora)}</td>
                    <td class="px-4 py-3 text-sm text-zinc-700 truncate">${atend.barbeiro_nome || '-'}</td>
                    <td class="px-4 py-3 text-center text-sm text-zinc-700">${atend.quantidade_servicos || 0}</td>
                    <td class="px-4 py-3 text-center text-sm text-zinc-700">${atend.quantidade_produtos || 0}</td>
                    <td class="px-4 py-3 text-center text-sm text-zinc-900 font-semibold">R$ ${formatCurrency(atend.valor_total || 0)}</td>
                </tr>
            `;
        }).join('');
    } catch (error) {
        console.error(error);
    }
}

function limparFiltros() {
    // Resetar os valores dos selects
    ['filterStatus', 'filterBarbeiro', 'filterServico', 'filterProduto'].forEach(name => {
        const input = document.querySelector(`input[name="${name}"]`);
        if (input) {
            const wrapper = input.closest('[x-data]');
            if (wrapper && wrapper.__x) {
                const alpineData = wrapper.__x.$data;
                if (alpineData) {
                    alpineData.value = '';
                }
            }
        }
    });

    // Resetar campos de data
    document.getElementById('filterDataInicio').value = '';
    document.getElementById('filterDataFim').value = '';

    // Recarregar histórico
    loadClienteHistory(currentClienteId);
}

async function checkDuplicatePhone(phone) {
    if (!phone || phone.trim() === '') return;

    clearTimeout(duplicateCheckTimeout);
    duplicateCheckTimeout = setTimeout(async () => {
        try {
            const response = await fetch(`/clientes/check-phone?telefone=${encodeURIComponent(phone)}`);
            const data = await response.json();

            if (data.exists) {
                phoneCheckedAndClear = false;
                showDuplicateModal('phone', data.cliente);
            } else {
                phoneCheckedAndClear = true;
            }
        } catch (error) {
            console.error(error);
        }
    }, 400);
}

async function checkDuplicateName(name) {
    if (!name || name.trim() === '' || !phoneCheckedAndClear) return;

    clearTimeout(duplicateCheckTimeout);
    duplicateCheckTimeout = setTimeout(async () => {
        try {
            const response = await fetch(`/clientes/check-name?nome=${encodeURIComponent(name)}`);
            const data = await response.json();

            if (data.exists) {
                showDuplicateModal('name', data.cliente);
            }
        } catch (error) {
            console.error(error);
        }
    }, 400);
}

function showDuplicateModal(type, clienteData) {
    const modal = document.getElementById('modalDuplicateConfirm');
    const title = document.getElementById('duplicateTitle');
    const message = document.getElementById('duplicateMessage');
    const actions = document.getElementById('duplicateActions');

    if (type === 'phone') {
        title.textContent = 'Telefone já cadastrado';
        message.textContent = `Encontramos um cadastro existente para o telefone informado no nome de ${clienteData.nome}. Deseja atualizar este cadastro?`;
        actions.innerHTML = `
            <button type="button" onclick="redirectToEdit(${clienteData.id})" class="w-full rounded-2xl bg-barber-500 px-6 py-3 text-sm font-bold text-white hover:bg-barber-600 transition">
                Confirmar e atualizar cadastro
            </button>
            <button type="button" onclick="closeDuplicateModal()" class="w-full rounded-2xl border border-zinc-300 bg-white px-6 py-3 text-sm font-bold text-zinc-700 hover:bg-zinc-100 transition">
                Cancelar ação
            </button>
        `;
    } else if (type === 'name') {
        title.textContent = 'Nome de cliente já existe';
        message.textContent = `Foi encontrado um cliente com o nome ${clienteData.nome}. Você deseja criar um novo cadastro ou atualizar a informação existente?`;
        actions.innerHTML = `
            <button type="button" onclick="closeDuplicateModal()" class="w-full rounded-2xl bg-zinc-900 px-6 py-3 text-sm font-bold text-white hover:bg-zinc-800 transition">
                Cadastrar novo mesmo assim
            </button>
            <button type="button" onclick="redirectToEdit(${clienteData.id})" class="w-full rounded-2xl bg-barber-500 px-6 py-3 text-sm font-bold text-white hover:bg-barber-600 transition">
                Atualizar cadastro existente
            </button>
            <button type="button" onclick="closeDuplicateModal(); fecharModalNovoCliente();" class="w-full rounded-2xl border border-zinc-300 bg-white px-6 py-3 text-sm font-bold text-zinc-700 hover:bg-zinc-100 transition">
                Cancelar
            </button>
        `;
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeDuplicateModal() {
    document.getElementById('modalDuplicateConfirm').classList.add('hidden');
    document.getElementById('modalDuplicateConfirm').classList.remove('flex');
}

function redirectToEdit(clienteId) {
    closeDuplicateModal();
    fecharModalNovoCliente();
    abrirModalEditarCliente(clienteId);
}

function previewPhoto(input, previewId) {
    const preview = document.getElementById(previewId);
    if (!preview) return;

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = preview.querySelector('img');
            if (img) img.src = e.target.result;
            preview.classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function previewPhotoEdit(input) {
    const semFoto = document.getElementById('photoSemFotoEdit');
    const img = document.getElementById('photoImgEdit');
    if (!img) return;

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            img.src = e.target.result;
            img.classList.remove('hidden');
            if (semFoto) {
                semFoto.classList.add('hidden');
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR');
}

function formatDateTime(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR') + ' ' + date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
}

function formatCurrency(value) {
    return parseFloat(value || 0).toFixed(2).replace('.', ',');
}

document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('modalDetalhesCliente');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                fecharModalDetalhesCliente();
            }
        });
    }

    const modalNovoCliente = document.getElementById('modalNovoCliente');
    if (modalNovoCliente) {
        modalNovoCliente.addEventListener('click', function(e) {
            if (e.target === this) {
                fecharModalNovoCliente();
            }
        });
    }

    const modalEditarCliente = document.getElementById('modalEditarCliente');
    if (modalEditarCliente) {
        modalEditarCliente.addEventListener('click', function(e) {
            if (e.target === this) {
                fecharModalEditarCliente();
            }
        });
    }

    const confirmStatusModal = document.getElementById('confirmStatusModal');
    if (confirmStatusModal) {
        confirmStatusModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeConfirmStatusModal();
            }
        });
    }

    const confirmStatusBtn = document.getElementById('confirmStatusBtn');
    if (confirmStatusBtn) {
        confirmStatusBtn.addEventListener('click', executeToggleStatus);
    }
});
</script>
@endsection
