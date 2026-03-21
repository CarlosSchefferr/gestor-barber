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
                <h1 class="mt-1 text-2xl font-bold leading-tight text-zinc-900 sm:text-3xl">Novo Agendamento</h1>
                <p class="mt-1 text-sm text-zinc-500">Preencha os dados para criar um novo agendamento</p>
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
        <form action="{{ route('agendamentos.store') }}" method="POST" class="space-y-6">
            @csrf

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
                            <div class="mt-2 flex items-center gap-2">
                                <div class="flex-1">
                                    <x-custom-select
                                        name="cliente_id"
                                        :options="$clientes->pluck('nome', 'id')->toArray()"
                                        :value="old('cliente_id')"
                                        placeholder="Selecione um cliente"
                                        required
                                    />
                                </div>
                                <button type="button" id="addClienteBtn" title="Adicionar cliente" class="flex h-[50px] w-[50px] items-center justify-center rounded-2xl bg-barber-500 text-white shadow-sm transition hover:bg-barber-600">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                </button>
                            </div>
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
                                    :value="old('barbeiro_id')"
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
                                   value="{{ old('starts_at', request('date') ? request('date').'T09:00' : '') }}"
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
                                   value="{{ old('ends_at') }}"
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
                            <div class="mt-2 flex items-center gap-2">
                                <div class="flex-1">
                                    <select name="servico" id="servicoSelect" required class="{{ $inputClass }} !mt-0 @error('servico') !border-red-400 !ring-2 !ring-red-200 @enderror">
                                        <option value="">Selecione um servico</option>
                                        @foreach($services as $service)
                                            <option value="{{ $service->name }}" data-price="{{ $service->price ?? '0.00' }}" {{ old('servico') == $service->name ? 'selected' : '' }}>
                                                {{ $service->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @if(auth()->check() && auth()->user()->isOwner())
                                    <button type="button" id="addServiceBtn" title="Adicionar servico" class="flex h-[50px] w-[50px] items-center justify-center rounded-2xl bg-emerald-500 text-white shadow-sm transition hover:bg-emerald-600">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                    </button>
                                @endif
                            </div>
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
                                       value="{{ old('price') }}"
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
                              class="{{ $inputClass }} resize-none @error('observacoes') !border-red-400 !ring-2 !ring-red-200 @enderror">{{ old('observacoes') }}</textarea>
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
                    Salvar Agendamento
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Novo Cliente -->
<div id="modalAddCliente" class="fixed inset-0 z-50 hidden h-full w-full overflow-y-auto bg-zinc-900/60 backdrop-blur-[2px]">
    <div class="flex min-h-screen items-start justify-center p-4 pt-20">
        <div class="w-full max-w-md rounded-3xl border border-zinc-200 bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-zinc-100 px-6 py-5">
                <div>
                    <h3 class="text-lg font-bold text-zinc-900">Novo Cliente</h3>
                    <p class="mt-1 text-sm text-zinc-500">Cadastre um novo cliente rapidamente</p>
                </div>
                <button id="clienteCloseX" class="rounded-xl p-2 text-zinc-400 transition hover:bg-zinc-100 hover:text-zinc-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="p-6">
                <div id="clienteInlineError" class="mb-4 hidden rounded-xl bg-red-50 px-4 py-3 text-sm text-red-600"></div>
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Nome <span class="text-red-500">*</span></label>
                        <input id="cliente_nome" class="{{ $inputClass }}" placeholder="Nome completo do cliente">
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Email</label>
                        <input id="cliente_email" type="email" class="{{ $inputClass }}" placeholder="email@exemplo.com">
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Telefone</label>
                        <input id="cliente_telefone" class="{{ $inputClass }}" placeholder="(00) 00000-0000">
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 border-t border-zinc-100 px-6 py-4">
                <button id="clienteCancel" class="inline-flex items-center justify-center rounded-2xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50">
                    Cancelar
                </button>
                <button id="clienteSave" class="inline-flex items-center justify-center rounded-2xl bg-barber-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-barber-600">
                    Salvar Cliente
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Novo Servico -->
<div id="modalAddService" class="fixed inset-0 z-50 hidden h-full w-full overflow-y-auto bg-zinc-900/60 backdrop-blur-[2px]">
    <div class="flex min-h-screen items-start justify-center p-4 pt-20">
        <div class="w-full max-w-md rounded-3xl border border-zinc-200 bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-zinc-100 px-6 py-5">
                <div>
                    <h3 class="text-lg font-bold text-zinc-900">Novo Servico</h3>
                    <p class="mt-1 text-sm text-zinc-500">Cadastre um novo servico rapidamente</p>
                </div>
                <button id="serviceCloseX" class="rounded-xl p-2 text-zinc-400 transition hover:bg-zinc-100 hover:text-zinc-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="p-6">
                <div id="serviceInlineError" class="mb-4 hidden rounded-xl bg-red-50 px-4 py-3 text-sm text-red-600"></div>
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Nome <span class="text-red-500">*</span></label>
                        <input id="service_name" class="{{ $inputClass }}" placeholder="Nome do servico">
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Descricao</label>
                        <textarea id="service_description" rows="2" class="{{ $inputClass }} resize-none" placeholder="Descricao do servico"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-semibold text-zinc-700">Preco (R$) <span class="text-red-500">*</span></label>
                            <input id="service_price" type="number" step="0.01" class="{{ $inputClass }}" placeholder="0,00" value="0.00">
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-zinc-700">Comissao (R$)</label>
                            <input id="service_commission" type="number" step="0.01" class="{{ $inputClass }}" placeholder="0,00" value="0.00">
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 border-t border-zinc-100 px-6 py-4">
                <button id="serviceCancel" class="inline-flex items-center justify-center rounded-2xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50">
                    Cancelar
                </button>
                <button id="serviceSave" class="inline-flex items-center justify-center rounded-2xl bg-emerald-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-600">
                    Salvar Servico
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    // Modal Cliente
    var addClienteBtn = document.getElementById('addClienteBtn');
    var modalCliente = document.getElementById('modalAddCliente');

    if(addClienteBtn && modalCliente){
        addClienteBtn.addEventListener('click', function(){
            modalCliente.classList.remove('hidden');
        });

        document.getElementById('clienteCloseX').addEventListener('click', function(){ modalCliente.classList.add('hidden'); });
        document.getElementById('clienteCancel').addEventListener('click', function(){ modalCliente.classList.add('hidden'); });

        modalCliente.addEventListener('click', function(e){
            if(e.target === this) modalCliente.classList.add('hidden');
        });

        document.getElementById('clienteSave').addEventListener('click', function(){
            var nome = document.getElementById('cliente_nome').value.trim();
            var email = document.getElementById('cliente_email').value.trim();
            var telefone = document.getElementById('cliente_telefone').value.trim();
            var errEl = document.getElementById('clienteInlineError');
            errEl.classList.add('hidden');
            errEl.textContent = '';

            if(!nome){
                errEl.textContent = 'Nome e obrigatorio.';
                errEl.classList.remove('hidden');
                return;
            }

            fetch("{{ route('clientes.inline.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ nome: nome, email: email, telefone: telefone })
            }).then(function(res){
                return res.json();
            }).then(function(data){
                if(data && data.id){
                    // Recarrega a pagina para atualizar o select
                    window.location.reload();
                } else {
                    errEl.textContent = (data.message || 'Erro ao criar cliente.');
                    errEl.classList.remove('hidden');
                }
            }).catch(function(){
                errEl.textContent = 'Erro de rede ao criar cliente.';
                errEl.classList.remove('hidden');
            });
        });
    }

    // Modal Servico
    var addServiceBtn = document.getElementById('addServiceBtn');
    var modalService = document.getElementById('modalAddService');

    if(addServiceBtn && modalService){
        addServiceBtn.addEventListener('click', function(){
            modalService.classList.remove('hidden');
        });

        document.getElementById('serviceCloseX').addEventListener('click', function(){ modalService.classList.add('hidden'); });
        document.getElementById('serviceCancel').addEventListener('click', function(){ modalService.classList.add('hidden'); });

        modalService.addEventListener('click', function(e){
            if(e.target === this) modalService.classList.add('hidden');
        });

        document.getElementById('serviceSave').addEventListener('click', function(){
            var name = document.getElementById('service_name').value.trim();
            var description = document.getElementById('service_description').value.trim();
            var price = document.getElementById('service_price').value.trim();
            var commission = document.getElementById('service_commission').value.trim();
            var errEl = document.getElementById('serviceInlineError');
            errEl.classList.add('hidden');
            errEl.textContent = '';

            if(!name){
                errEl.textContent = 'Nome e obrigatorio.';
                errEl.classList.remove('hidden');
                return;
            }
            if(price === '' || isNaN(parseFloat(price))){
                errEl.textContent = 'Preco e obrigatorio e deve ser um numero.';
                errEl.classList.remove('hidden');
                return;
            }

            fetch("{{ route('admin.services.inline.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ name: name, description: description, price: price, commission: commission })
            }).then(function(res){
                return res.json();
            }).then(function(data){
                if(data && data.id){
                    var sel = document.getElementById('servicoSelect');
                    if(sel){
                        var opt = document.createElement('option');
                        opt.value = data.name;
                        opt.text = data.name;
                        if(data.price) opt.setAttribute('data-price', data.price);
                        opt.selected = true;
                        sel.appendChild(opt);
                    }
                    var priceInput = document.getElementById('priceInput');
                    if(priceInput && data.price){ priceInput.value = parseFloat(data.price).toFixed(2); }
                    modalService.classList.add('hidden');
                } else {
                    errEl.textContent = (data.message || 'Erro ao criar servico.');
                    errEl.classList.remove('hidden');
                }
            }).catch(function(){
                errEl.textContent = 'Erro de rede ao criar servico.';
                errEl.classList.remove('hidden');
            });
        });
    }

    // Auto-preencher preco ao selecionar servico
    var servicoSelect = document.getElementById('servicoSelect');
    var priceInput = document.getElementById('priceInput');

    if(servicoSelect && priceInput){
        servicoSelect.addEventListener('change', function(){
            var opt = this.options[this.selectedIndex];
            if(opt && opt.dataset && opt.dataset.price){
                priceInput.value = parseFloat(opt.dataset.price || 0).toFixed(2);
            }
        });

        // Aplicar preco inicial se ja tiver servico selecionado
        if(servicoSelect.value && (priceInput.value === '' || priceInput.value === null)){
            var opt = servicoSelect.options[servicoSelect.selectedIndex];
            if(opt && opt.dataset && opt.dataset.price){
                priceInput.value = parseFloat(opt.dataset.price || 0).toFixed(2);
            }
        }
    }
});
</script>
@endpush
