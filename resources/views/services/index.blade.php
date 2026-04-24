@extends('layouts.app')

@section('content')
@php
    $inputClass = 'mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20';
    $cardClass = 'rounded-3xl border border-zinc-200 bg-white/95 shadow-sm';
    $servicosJs = $services->keyBy('id')->map(function ($service) {
        return [
            'id' => $service->id,
            'name' => $service->name,
            'description' => $service->description,
            'duration' => $service->duration,
            'price' => $service->price,
            'commission' => $service->commission,
            'type' => $service->type,
            'return_alert_days' => $service->return_alert_days,
            'observations' => $service->observations,
            'combo_services' => $service->comboServices ? $service->comboServices->map(function($s) {
                return [
                    'id' => $s->id,
                    'name' => $s->name,
                    'duration' => $s->duration,
                    'price' => $s->price,
                    'commission' => $s->commission,
                ];
            })->values()->toArray() : []
        ];
    })->toArray();
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-8 rounded-3xl border border-zinc-200 bg-white px-6 py-7 shadow-sm sm:px-8">
        <div class="flex flex-col gap-5">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500">Catalogo</p>
                <h1 class="mt-2 text-3xl font-bold leading-tight text-zinc-900 sm:text-4xl">Servicos</h1>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button type="button" onclick="abrirModalNovoServico()" class="inline-flex items-center justify-center rounded-2xl bg-barber-500 px-4 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-barber-600">
                    Novo servico
                </button>
            </div>
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

    <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="{{ $cardClass }} p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Total de serviços</p>
            <p class="mt-3 text-3xl font-bold text-zinc-900">{{ $totalServicos ?? 0 }}</p>
            <p class="mt-1 text-sm text-zinc-500">Catálogo completo</p>
        </div>
        <div class="{{ $cardClass }} p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Serviços ativos</p>
            <p class="mt-3 text-3xl font-bold text-emerald-600">{{ $servicosAtivos ?? 0 }}</p>
            <p class="mt-1 text-sm text-zinc-500">Disponíveis para agenda</p>
        </div>
        <div class="{{ $cardClass }} p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Serviços inativos</p>
            <p class="mt-3 text-3xl font-bold text-zinc-900">{{ $servicosInativos ?? 0 }}</p>
            <p class="mt-1 text-sm text-zinc-500">Ocultos na operação</p>
        </div>
        <div class="{{ $cardClass }} p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Preço médio</p>
            <p class="mt-3 text-3xl font-bold text-zinc-900">R$ {{ number_format($ticketMedio ?? 0, 2, ',', '.') }}</p>
            <p class="mt-1 text-sm text-zinc-500">Referência de venda</p>
        </div>
    </div>

    <div class="{{ $cardClass }} mb-8 p-6 sm:p-7">
        <div class="mb-5 flex items-center justify-between">
            <h2 class="text-lg font-bold text-zinc-900">Filtros</h2>
            <span class="rounded-full border border-zinc-200 bg-zinc-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-zinc-500">Busca avançada</span>
        </div>
        <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <label class="text-sm font-semibold text-zinc-700">Buscar serviço</label>
                <input type="text" name="search" value="{{ request('search') }}" class="{{ $inputClass }}" placeholder="Nome ou descrição">
            </div>
            <div>
                <label class="text-sm font-semibold text-zinc-700">Status</label>
                <x-custom-select name="status" :options="['' => 'Todos', 'active' => 'Ativos', 'inactive' => 'Inativos']" :value="request('status', '')" placeholder="Selecione o status" />
            </div>
            <div>
                <label class="text-sm font-semibold text-zinc-700">Ordenar por</label>
                <x-custom-select name="sort" :options="['name' => 'Nome', 'price' => 'Preço', 'commission' => 'Comissão']" :value="request('sort', 'name')" placeholder="Selecione a ordenação" />
            </div>
            <div class="md:col-span-3 flex flex-wrap items-center justify-center gap-3 pt-1">
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-barber-500 px-5 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-barber-600">Aplicar filtros</button>
                <a href="{{ route('admin.services.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-zinc-300 bg-white px-5 py-3 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700 transition hover:bg-zinc-100">Limpar</a>
            </div>
        </form>
    </div>

    <!-- Tabela de Servicos -->
    <div class="{{ $cardClass }} overflow-hidden">
        <div class="border-b border-zinc-200 px-6 py-4">
            <h3 class="text-lg font-bold text-zinc-900">Lista de serviços</h3>
            <p class="mt-1 text-sm text-zinc-500">Todos os serviços oferecidos pela barbearia</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-zinc-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Serviço</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Descrição</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Preço</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Comissão</th>
                        <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wide text-zinc-500">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 bg-white">
                    @forelse($services as $service)
                        <tr class="transition hover:bg-zinc-50">
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-barber-100">
                                        <svg class="h-5 w-5 text-barber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 10-4.243 4.243 3 3 0 004.243-4.243zm0-5.758a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243z"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-semibold text-zinc-900">{{ $service->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-zinc-600">{{ Str::limit($service->description ?? '-', 60) }}</span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="text-sm font-semibold text-emerald-600">R$ {{ number_format($service->price ?? 0, 2, ',', '.') }}</span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="inline-flex rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-700">
                                    R$ {{ number_format($service->commission ?? 0, 2, ',', '.') }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" onclick="abrirModalEditarServico({{ $service->id }})" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-600 transition hover:bg-zinc-50 hover:text-barber-600" title="Editar">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5h6M4 21l4-4 9-9a2.828 2.828 0 10-4-4L4 13v8z"></path>
                                        </svg>
                                    </button>
                                    <button type="button" onclick="confirmDelete({{ $service->id }})" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-600 transition hover:bg-red-50 hover:text-red-600" title="Remover">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100">
                                    <svg class="h-6 w-6 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 10-4.243 4.243 3 3 0 004.243-4.243zm0-5.758a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-sm font-bold text-zinc-900">Nenhum serviço encontrado</h3>
                                <p class="mt-1 text-sm text-zinc-500">Comece cadastrando um novo serviço.</p>
                                <button type="button" onclick="abrirModalNovoServico()" class="mt-4 inline-flex items-center justify-center rounded-2xl bg-barber-500 px-4 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-barber-600">
                                    Novo serviço
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($services->hasPages())
            <div class="border-t border-zinc-200 bg-white px-6 py-4">
                {{ $services->links() }}
            </div>
        @endif
    </div>
</div>

<div id="modalNovoServico" class="fixed inset-0 z-50 hidden pointer-events-none h-full w-full bg-zinc-900/60 backdrop-blur-[2px] flex items-center justify-center p-4 sm:p-6" x-data="{ abaAtiva: 'dados', tipoCadastro: 'service' }">
    <div class="relative w-full max-w-5xl rounded-3xl border border-zinc-200 bg-white shadow-xl flex flex-col max-h-[90vh] transition-all duration-300 ease-in-out">
        <div class="p-6 sm:p-8 pb-5 shrink-0">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-barber-500">Cadastro</p>
                <h3 class="mt-2 text-2xl font-bold text-zinc-900">Novo serviço</h3>
            </div>
        </div>

        <div class="flex shrink-0 px-6 sm:px-8 pb-6 gap-2">
            <button @click="abaAtiva = 'dados'" :class="abaAtiva === 'dados' ? 'bg-barber-50 border-barber-500 text-barber-600 font-semibold' : 'bg-white border-zinc-300 text-zinc-500 hover:text-zinc-700 hover:border-zinc-400'" class="flex-1 py-2.5 text-sm font-medium transition-all duration-200 rounded-xl border">
                Dados do serviço
            </button>
            
            <button 
                x-show="tipoCadastro === 'combo'"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                @click="abaAtiva = 'combo'" 
                :class="abaAtiva === 'combo' ? 'bg-barber-50 border-barber-500 text-barber-600 font-semibold' : 'bg-white border-zinc-300 text-zinc-500 hover:text-zinc-700 hover:border-zinc-400'" 
                class="flex-1 py-2.5 text-sm font-medium transition-all duration-200 rounded-xl border">
                Serviços do combo
            </button>

            <button @click="abaAtiva = 'promocoes'" :class="abaAtiva === 'promocoes' ? 'bg-barber-50 border-barber-500 text-barber-600 font-semibold' : 'bg-white border-zinc-300 text-zinc-500 hover:text-zinc-700 hover:border-zinc-400'" class="flex-1 py-2.5 text-sm font-medium transition-all duration-200 rounded-xl border">
                Promoções
            </button>
        </div>

        <form id="formNovoServico" action="{{ route('admin.services.store') }}" method="POST" class="p-6 sm:p-8 flex-1 overflow-y-auto border-t border-zinc-200">
            @csrf
            
            <div x-show="abaAtiva === 'dados'" class="space-y-6">
                <div>
                    <label class="text-sm font-semibold text-zinc-700">Tipo de cadastro <span class="text-red-500">*</span></label>
                    <x-custom-select name="type" :options="['service' => 'Serviço', 'combo' => 'Combo de serviços']" @change="tipoCadastro = $event.target.value; if(tipoCadastro !== 'combo' && abaAtiva === 'combo') abaAtiva = 'dados'" />
                </div>
                
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="text-sm font-semibold text-zinc-700">Descrição <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required class="{{ $inputClass }}" placeholder="Ex: Corte + barba">
                    </div>
                    
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Duração <span class="text-red-500">*</span></label>
                        <input type="time" name="duration" required class="{{ $inputClass }}">
                    </div>
                    
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Valor <span class="text-red-500">*</span></label>
                        <div class="mt-2"><x-currency-input name="price" required /></div>
                    </div>
                    
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Comissionamento <span class="text-red-500">*</span></label>
                        <div class="mt-2"><x-percent-input name="commission" required value="0" /></div>
                    </div>
                    
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Alerta de retorno</label>
                        <div class="relative mt-2">
                            <input type="number" min="0" name="return_alert_days" class="w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 pr-14 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20" placeholder="Ex: 15">
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4">
                                <span class="text-zinc-500 sm:text-sm">dias</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="sm:col-span-2">
                        <label class="text-sm font-semibold text-zinc-700">Observações</label>
                        <textarea name="observations" rows="3" class="{{ $inputClass }} resize-none" placeholder="Observações opcionais..."></textarea>
                    </div>
                </div>

                </div> 
            
            <div x-show="abaAtiva === 'combo'" class="space-y-6">
                
                <div class="flex flex-col items-center gap-4 max-w-md mx-auto mb-2">
                    <div class="w-full">
                        <x-custom-select name="select_servico_combo_novo" :options="['' => 'Selecione o serviço para adicionar ao combo'] + $services->pluck('name', 'id')->toArray()" />
                    </div>
                    <button type="button" onclick="adicionarServicoCombo('novo')" class="inline-flex h-[46px] items-center justify-center rounded-2xl bg-zinc-900 px-8 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-zinc-800">
                        Adicionar Serviço
                    </button>
                </div>

                <div id="combo-info-novo" class="hidden grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-zinc-500">Tempo Total</p>
                        <p class="mt-1 text-2xl font-bold text-zinc-900"><span id="combo-total-duracao-novo">0</span><span class="text-sm font-medium text-zinc-500 ml-1">min</span></p>
                    </div>
                    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-zinc-500">Valor Total</p>
                        <p class="mt-1 text-2xl font-bold text-zinc-900"><span class="text-sm font-medium text-zinc-500 mr-1">R$</span><span id="combo-total-valor-novo">0,00</span></p>
                    </div>
                    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-zinc-500">Soma Comissão</p>
                        <p class="mt-1 text-2xl font-bold text-zinc-900"><span id="combo-media-comissao-novo">0</span><span class="text-sm font-medium text-zinc-500 ml-1">%</span></p>
                    </div>
                </div>

                <div class="rounded-2xl border border-zinc-200 overflow-hidden">
                    <table class="w-full min-w-max">
                        <thead class="bg-zinc-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-700">Serviço</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-zinc-700 w-24">Ação</th>
                            </tr>
                        </thead>
                        <tbody id="combo-tbody-novo" class="divide-y divide-zinc-100 bg-white">
                        </tbody>
                    </table>
                </div>
            </div> 

            <div x-show="abaAtiva === 'promocoes'" class="space-y-6">
                <div class="flex items-center justify-center h-40 rounded-2xl border border-dashed border-zinc-300 bg-zinc-50">
                    <p class="text-sm font-medium text-zinc-500">Em andamento.</p>
                </div>
            </div>
        </form>

        <div class="flex justify-center gap-3 border-t border-zinc-200 p-6 sm:p-8 shrink-0">
            <button type="button" onclick="fecharModalNovoServico()" class="inline-flex items-center justify-center rounded-2xl border border-zinc-300 bg-white px-5 py-3 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700 transition hover:bg-zinc-100">
                Fechar
            </button>
            <button type="button" onclick="salvarNovoServico()" class="inline-flex items-center justify-center rounded-2xl bg-barber-500 px-5 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-barber-600">
                Salvar
            </button>
        </div>
    </div>
</div>

<div id="modalEditarServico" class="fixed inset-0 z-50 hidden pointer-events-none h-full w-full bg-zinc-900/60 backdrop-blur-[2px] flex items-center justify-center p-4 sm:p-6" x-data="{ abaAtiva: 'dados', tipoCadastro: 'service' }">
    <div class="relative w-full max-w-5xl rounded-3xl border border-zinc-200 bg-white shadow-xl flex flex-col max-h-[90vh] transition-all duration-300 ease-in-out">
        <div class="p-6 sm:p-8 pb-5 shrink-0">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-barber-500">Edição</p>
                <h3 class="mt-2 text-2xl font-bold text-zinc-900">Editar serviço</h3>
            </div>
        </div>

        <div class="flex shrink-0 px-6 sm:px-8 pb-6 gap-2">
            <button @click="abaAtiva = 'dados'" :class="abaAtiva === 'dados' ? 'bg-barber-50 border-barber-500 text-barber-600 font-semibold' : 'bg-white border-zinc-300 text-zinc-500 hover:text-zinc-700 hover:border-zinc-400'" class="flex-1 py-2.5 text-sm font-medium transition-all duration-200 rounded-xl border">
                Dados do serviço
            </button>
            
            <button 
                x-show="tipoCadastro === 'combo'"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                @click="abaAtiva = 'combo'" 
                :class="abaAtiva === 'combo' ? 'bg-barber-50 border-barber-500 text-barber-600 font-semibold' : 'bg-white border-zinc-300 text-zinc-500 hover:text-zinc-700 hover:border-zinc-400'" 
                class="flex-1 py-2.5 text-sm font-medium transition-all duration-200 rounded-xl border">
                Serviços do combo
            </button>

            <button @click="abaAtiva = 'promocoes'" :class="abaAtiva === 'promocoes' ? 'bg-barber-50 border-barber-500 text-barber-600 font-semibold' : 'bg-white border-zinc-300 text-zinc-500 hover:text-zinc-700 hover:border-zinc-400'" class="flex-1 py-2.5 text-sm font-medium transition-all duration-200 rounded-xl border">
                Promoções
            </button>
        </div>

        <form id="formEditarServico" method="POST" class="p-6 sm:p-8 flex-1 overflow-y-auto border-t border-zinc-200">
            @csrf
            @method('PUT')
            
            <div x-show="abaAtiva === 'dados'" class="space-y-6">
                <div>
                    <label class="text-sm font-semibold text-zinc-700">Tipo de cadastro <span class="text-red-500">*</span></label>
                    <x-custom-select name="type" id="editarServicoTipo" :options="['service' => 'Serviço', 'combo' => 'Combo de serviços']" @change="tipoCadastro = $event.target.value; if(tipoCadastro !== 'combo' && abaAtiva === 'combo') abaAtiva = 'dados'" />
                </div>
                
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="text-sm font-semibold text-zinc-700">Descrição <span class="text-red-500">*</span></label>
                        <input type="text" id="editarServicoNome" name="name" required class="{{ $inputClass }}">
                    </div>
                    
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Duração <span class="text-red-500">*</span></label>
                        <input type="time" id="editarServicoDuracao" name="duration" required class="{{ $inputClass }}">
                    </div>
                    
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Valor <span class="text-red-500">*</span></label>
                        <div class="mt-2"><x-currency-input name="price" id="editarServicoPreco" required /></div>
                    </div>
                    
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Comissionamento <span class="text-red-500">*</span></label>
                        <div class="mt-2"><x-percent-input name="commission" id="editarServicoComissao" required /></div>
                    </div>
                    
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Alerta de retorno</label>
                        <div class="relative mt-2">
                            <input type="number" id="editarServicoAlerta" min="0" name="return_alert_days" class="w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 pr-14 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20">
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4">
                                <span class="text-zinc-500 sm:text-sm">dias</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="sm:col-span-2">
                        <label class="text-sm font-semibold text-zinc-700">Observações</label>
                        <textarea id="editarServicoObs" name="observations" rows="3" class="{{ $inputClass }} resize-none"></textarea>
                    </div>
                </div>

                </div> 
            
            <div x-show="abaAtiva === 'combo'" class="space-y-6">
                
                <div class="flex flex-col items-center gap-4 max-w-md mx-auto mb-2">
                    <div class="w-full">
                        <x-custom-select name="select_servico_combo_edit" :options="['' => 'Selecione o serviço para adicionar ao combo'] + $services->pluck('name', 'id')->toArray()" />
                    </div>
                    <button type="button" onclick="adicionarServicoCombo('edit')" class="inline-flex h-[46px] items-center justify-center rounded-2xl bg-zinc-900 px-8 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-zinc-800">
                        Adicionar serviço
                    </button>
                </div>

                <div id="combo-info-edit" class="hidden grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-zinc-500">Tempo Total</p>
                        <p class="mt-1 text-2xl font-bold text-zinc-900"><span id="combo-total-duracao-edit">0</span><span class="text-sm font-medium text-zinc-500 ml-1">min</span></p>
                    </div>
                    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-zinc-500">Valor Total</p>
                        <p class="mt-1 text-2xl font-bold text-zinc-900"><span class="text-sm font-medium text-zinc-500 mr-1">R$</span><span id="combo-total-valor-edit">0,00</span></p>
                    </div>
                    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-zinc-500">Soma Comissão</p>
                        <p class="mt-1 text-2xl font-bold text-zinc-900"><span id="combo-media-comissao-edit">0</span><span class="text-sm font-medium text-zinc-500 ml-1">%</span></p>
                    </div>
                </div>

                <div class="rounded-2xl border border-zinc-200 overflow-hidden">
                    <table class="w-full min-w-max">
                        <thead class="bg-zinc-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-700">Serviço</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-zinc-700 w-24">Ação</th>
                            </tr>
                        </thead>
                        <tbody id="combo-tbody-edit" class="divide-y divide-zinc-100 bg-white">
                        </tbody>
                    </table>
                </div>
            </div>

            <div x-show="abaAtiva === 'promocoes'" class="space-y-6">
                <div class="flex items-center justify-center h-40 rounded-2xl border border-dashed border-zinc-300 bg-zinc-50">
                    <p class="text-sm font-medium text-zinc-500">Em andamento.</p>
                </div>
            </div>
        </form>

        <div class="flex justify-center gap-3 border-t border-zinc-200 p-6 sm:p-8 shrink-0">
            <button type="button" onclick="fecharModalEditarServico()" class="inline-flex items-center justify-center rounded-2xl border border-zinc-300 bg-white px-5 py-3 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700 transition hover:bg-zinc-100">
                Fechar
            </button>
            <button type="button" onclick="salvarEditarServico()" class="inline-flex items-center justify-center rounded-2xl bg-barber-500 px-5 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-barber-600">
                Salvar alterações
            </button>
        </div>
    </div>
</div>

<!-- Modal de Confirmacao -->
<div id="confirmModal" class="fixed inset-0 z-50 hidden h-full w-full overflow-y-auto bg-zinc-900/60 backdrop-blur-[2px]">
    <div class="relative top-20 mx-auto w-full max-w-md rounded-3xl border border-zinc-200 bg-white p-6 shadow-xl sm:p-8">
        <div class="text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-100">
                <svg class="h-7 w-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
            <h3 class="mt-5 text-xl font-bold text-zinc-900">Confirmar exclusão</h3>
            <p class="mt-2 text-sm text-zinc-500">
                Tem certeza que deseja excluir este serviço? Esta ação não pode ser desfeita.
            </p>
            <div class="mt-6 flex justify-center gap-3">
                <button type="button" onclick="closeModal()" class="inline-flex items-center justify-center rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700 transition hover:bg-zinc-100">
                    Fechar
                </button>
                <button id="confirmDeleteBtn" class="inline-flex items-center justify-center rounded-2xl bg-red-600 px-4 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-red-700">
                    Sim, excluir
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let serviceIdToDelete = null;
const servicos = @json($servicosJs);

let servicosComboNovo = [];
let servicosComboEdit = [];

function adicionarServicoCombo(modalType) {
    const isNovo = modalType === 'novo';
    const selectName = isNovo ? 'select_servico_combo_novo' : 'select_servico_combo_edit';
    const list = isNovo ? servicosComboNovo : servicosComboEdit;
    
    const selectEl = document.querySelector(`[name="${selectName}"]`);
    const serviceId = selectEl.value;
    if (!serviceId) return;
    
    const service = servicos[serviceId];
    if (!service) return;

    if (list.some(s => s.id == serviceId)) return;

    list.push(service);
    renderComboTable(modalType);
    
    selectEl.value = '';
    selectEl.dispatchEvent(new Event('input', { bubbles: true }));
    selectEl.dispatchEvent(new Event('change', { bubbles: true }));
    
    const wrapper = selectEl.closest('.cs-wrapper');
    const wrapperData = wrapper ? getAlpineData(wrapper) : null;
    if (wrapperData) {
        wrapperData.value = '';
    }
}

function removerServicoCombo(modalType, id) {
    const isNovo = modalType === 'novo';
    if (isNovo) {
        servicosComboNovo = servicosComboNovo.filter(s => s.id != id);
    } else {
        servicosComboEdit = servicosComboEdit.filter(s => s.id != id);
    }
    renderComboTable(modalType);
}

function renderComboTable(modalType) {
    const isNovo = modalType === 'novo';
    const tbodyId = isNovo ? 'combo-tbody-novo' : 'combo-tbody-edit';
    const list = isNovo ? servicosComboNovo : servicosComboEdit;
    const infoId = isNovo ? 'combo-info-novo' : 'combo-info-edit';
    
    const tbody = document.getElementById(tbodyId);
    tbody.innerHTML = '';
    
    if (list.length > 0) {
        document.getElementById(infoId).classList.remove('hidden');
    } else {
        document.getElementById(infoId).classList.add('hidden');
    }
    
    let totalDur = 0;
    let totalVal = 0;
    let totalCom = 0;

    list.forEach(s => {
        totalDur += Number(s.duration || 0);
        totalVal += Number(s.price || 0);
        totalCom += Number(s.commission || 0);
        
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900">${s.name}</td>
            <td class="px-6 py-4 whitespace-nowrap text-center">
                <button type="button" onclick="removerServicoCombo('${modalType}', ${s.id})" class="text-red-500 hover:text-red-700 font-semibold text-xs uppercase tracking-wide">Remover</button>
                <input type="hidden" name="combo_services[]" value="${s.id}">
            </td>
        `;
        tbody.appendChild(tr);
    });

    if (list.length > 0) {
        document.getElementById(`combo-total-duracao-${modalType}`).innerText = totalDur;
        document.getElementById(`combo-total-valor-${modalType}`).innerText = totalVal.toFixed(2).replace('.', ',');
        document.getElementById(`combo-media-comissao-${modalType}`).innerText = totalCom.toFixed(2);
    }
}

function salvarNovoServico() {
    const form = document.getElementById('formNovoServico');
    const tipo = form.querySelector('[name="type"]').value;
    if (tipo === 'combo' && servicosComboNovo.length < 2) {
        alert('É necessário adicionar pelo menos 2 serviços para o combo.');
        return;
    }
    if(form.reportValidity()) {
        form.submit();
    }
}

function salvarEditarServico() {
    const form = document.getElementById('formEditarServico');
    const tipo = form.querySelector('[name="type"]').value;
    if (tipo === 'combo' && servicosComboEdit.length < 2) {
        alert('É necessário adicionar pelo menos 2 serviços para o combo.');
        return;
    }
    if(form.reportValidity()) {
        form.submit();
    }
}

function getAlpineData(el) {
    if (el.__x && el.__x.$data) return el.__x.$data;
    if (el._x_dataStack && el._x_dataStack.length) return el._x_dataStack[0];
    if (typeof Alpine !== 'undefined' && Alpine.$data) return Alpine.$data(el);
    return null;
}

function abrirModalNovoServico() {
    const modal = document.getElementById('modalNovoServico');
    modal.classList.remove('hidden', 'pointer-events-none');
    
    const alpineData = getAlpineData(modal);
    if (alpineData) {
        alpineData.abaAtiva = 'dados';
        alpineData.tipoCadastro = 'service';
    }
    
    const typeSelectWrapper = document.querySelector('#modalNovoServico [name="type"]').closest('.cs-wrapper');
    const selectData = typeSelectWrapper ? getAlpineData(typeSelectWrapper) : null;
    if (selectData) {
        selectData.value = 'service';
    }
    servicosComboNovo = [];
    renderComboTable('novo');
}

function fecharModalNovoServico() {
    document.getElementById('modalNovoServico').classList.add('hidden', 'pointer-events-none');
}

function abrirModalEditarServico(id) {
    try {
        const servico = servicos[id];
        if (!servico) return;

        const form = document.getElementById('formEditarServico');
        form.action = `/admin/services/${id}`;
        document.getElementById('editarServicoNome').value = servico.name ?? '';
        
        let dur = servico.duration || 0;
        let h = Math.floor(dur / 60).toString().padStart(2, '0');
        let m = (dur % 60).toString().padStart(2, '0');
        document.getElementById('editarServicoDuracao').value = `${h}:${m}`;
        
        document.getElementById('editarServicoPreco').value = Number(servico.price ?? 0).toFixed(2);
        document.getElementById('editarServicoComissao').value = Number(servico.commission ?? 0).toFixed(2);
        
        const typeHidden = document.getElementById('formEditarServico').querySelector('input[type="hidden"][name="type"]');
        if (typeHidden) {
            typeHidden.value = servico.type ?? 'service';
            typeHidden.dispatchEvent(new Event('input', { bubbles: true }));
            typeHidden.dispatchEvent(new Event('change', { bubbles: true }));
        }
        
        document.getElementById('editarServicoAlerta').value = servico.return_alert_days ?? '';
        document.getElementById('editarServicoObs').value = servico.observations ?? '';

        const modal = document.getElementById('modalEditarServico');
        modal.classList.remove('hidden', 'pointer-events-none');
        
        const typeValue = servico.type ?? 'service';
        const alpineData = getAlpineData(modal);
        if (alpineData) {
            alpineData.tipoCadastro = typeValue;
            alpineData.abaAtiva = 'dados';
        }
        
        const typeSelectWrapper = typeHidden ? typeHidden.closest('.cs-wrapper') : null;
        const selectData = typeSelectWrapper ? getAlpineData(typeSelectWrapper) : null;
        if (selectData) {
            selectData.value = typeValue;
        }
        
        servicosComboEdit = [];
        if (servico.combo_services) {
            servicosComboEdit = Array.isArray(servico.combo_services) 
                ? [...servico.combo_services] 
                : Object.values(servico.combo_services);
        }
        renderComboTable('edit');
    } catch (e) {
        alert("Erro no JS: " + e.message + "\n" + e.stack);
    }
}

function fecharModalEditarServico() {
    document.getElementById('modalEditarServico').classList.add('hidden', 'pointer-events-none');
}

function confirmDelete(id) {
    serviceIdToDelete = id;
    document.getElementById('confirmModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('confirmModal').classList.add('hidden');
    serviceIdToDelete = null;
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (serviceIdToDelete) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/services/${serviceIdToDelete}`;

        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';

        const tokenField = document.createElement('input');
        tokenField.type = 'hidden';
        tokenField.name = '_token';
        tokenField.value = '{{ csrf_token() }}';

        form.appendChild(methodField);
        form.appendChild(tokenField);
        document.body.appendChild(form);
        form.submit();
    }
});

document.getElementById('confirmModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

document.getElementById('modalNovoServico').addEventListener('click', function(e) {
    if (e.target === this) fecharModalNovoServico();
});

document.getElementById('modalEditarServico').addEventListener('click', function(e) {
    if (e.target === this) fecharModalEditarServico();
});
</script>
@endsection
