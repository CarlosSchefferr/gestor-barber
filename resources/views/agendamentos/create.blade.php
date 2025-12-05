@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Novo Agendamento</h1>
            <p class="text-gray-600 mt-1">Crie um novo agendamento para um cliente</p>
        </div>
        <a href="{{ route('agendamentos.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition-colors shadow-sm">
            ← Voltar
        </a>
    </div>

    <div class="max-w-4xl mx-auto">
        <form action="{{ route('agendamentos.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Informações do Cliente e Barbeiro -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    Informações Básicas
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Cliente <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center space-x-2">
                            <select name="cliente_id" required class="flex-1 border-gray-300 rounded-md shadow-sm focus:border-barber-500 focus:ring-barber-500 @error('cliente_id') border-red-300 @enderror">
                                <option value="">Selecione um cliente</option>
                                @foreach($clientes as $cliente)
                                    <option value="{{ $cliente->id }}" {{ old('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                        {{ $cliente->nome }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" id="addClienteBtn" title="Adicionar cliente" class="inline-flex items-center px-3 py-2 rounded-md bg-barber-600 text-white hover:bg-barber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-barber-500">+
                            </button>
                        </div>
                        @error('cliente_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Barbeiro <span class="text-red-500">*</span>
                        </label>
                        @if(auth()->check() && auth()->user()->isBarber())
                            <select name="barbeiro_id" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-barber-500 focus:ring-barber-500 @error('barbeiro_id') border-red-300 @enderror">
                                <option value="{{ auth()->id() }}" selected>{{ auth()->user()->name }}</option>
                            </select>
                        @else
                            <select name="barbeiro_id" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-barber-500 focus:ring-barber-500 @error('barbeiro_id') border-red-300 @enderror">
                                <option value="">Selecione um barbeiro</option>
                                @foreach($barbeiros as $barbeiro)
                                    <option value="{{ $barbeiro->id }}" {{ old('barbeiro_id') == $barbeiro->id ? 'selected' : '' }}>
                                        {{ $barbeiro->name }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                        @error('barbeiro_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Data e Hora -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    Data e Horário
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Data e Hora de Início <span class="text-red-500">*</span>
                        </label>
                           <input type="datetime-local" name="starts_at" required
                               value="{{ old('starts_at', request('date') ? request('date')."T09:00" : '') }}"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-barber-500 focus:ring-barber-500 @error('starts_at') border-red-300 @enderror">
                        @error('starts_at')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Data e Hora de Fim
                        </label>
                        <input type="datetime-local" name="ends_at"
                               value="{{ old('ends_at') }}"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-barber-500 focus:ring-barber-500 @error('ends_at') border-red-300 @enderror">
                        @error('ends_at')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Opcional - deixe em branco se não souber a duração</p>
                    </div>
                </div>
            </div>

            <!-- Serviço e Preço -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    Serviço e Valor
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Serviço <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center space-x-2">
                            <select name="servico" required class="flex-1 border-gray-300 rounded-md shadow-sm focus:border-barber-500 focus:ring-barber-500 @error('servico') border-red-300 @enderror">
                                <option value="">Selecione um serviço</option>
                                @foreach($services as $service)
                                    <option value="{{ $service->name }}" {{ old('servico') == $service->name ? 'selected' : '' }}>{{ $service->name }}</option>
                                @endforeach
                            </select>
                            @if(auth()->check() && auth()->user()->isOwner())
                                <button type="button" id="addServiceBtn" title="Adicionar serviço" class="inline-flex items-center px-3 py-2 rounded-md bg-barber-600 text-white hover:bg-barber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-barber-500">+
                                </button>
                            @endif
                        </div>
                        @error('servico')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Preço (R$)
                        </label>
                        <input type="number" step="0.01" name="price"
                               value="{{ old('price') }}"
                               placeholder="0,00"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-barber-500 focus:ring-barber-500 @error('price') border-red-300 @enderror">
                        @error('price')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Observações -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    Observações
                </h3>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Observações Adicionais
                    </label>
                    <textarea name="observacoes" rows="4"
                              placeholder="Informações adicionais sobre o agendamento..."
                              class="w-full border-gray-300 rounded-md shadow-sm focus:border-barber-500 focus:ring-barber-500 @error('observacoes') border-red-300 @enderror">{{ old('observacoes') }}</textarea>
                    @error('observacoes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Botões de Ação -->
            <div class="flex justify-end space-x-4 pt-6">
                <a href="{{ route('agendamentos.index') }}"
                   class="bg-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-400 transition-colors">
                    Cancelar
                </a>
                <button type="submit"
                        class="bg-barber-600 text-white px-6 py-3 rounded-lg hover:bg-barber-700 transition-colors shadow-sm">
                    Salvar Agendamento
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        // Inline create cliente
        document.addEventListener('DOMContentLoaded', function(){
            var addClienteBtn = document.getElementById('addClienteBtn');
            if(addClienteBtn){
                addClienteBtn.addEventListener('click', function(){
                    // build modal
                        if(!document.getElementById('modalAddCliente')){
                        var modal = document.createElement('div');
                        modal.id = 'modalAddCliente';
                        modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center';
                        modal.innerHTML = `
                            <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4">
                                <div class="flex items-center justify-between px-6 py-4 border-b">
                                    <h3 class="text-lg font-semibold text-gray-900">Novo Cliente</h3>
                                    <button id="clienteCloseX" class="text-gray-400 hover:text-gray-600">&times;</button>
                                </div>
                                <div class="px-6 py-4">
                                    <div id="clienteInlineError" class="text-sm text-red-600 mb-2"></div>
                                    <div class="space-y-3">
                                        <input id="cliente_nome" class="w-full border border-gray-300 rounded px-3 py-2" placeholder="Nome" />
                                        <input id="cliente_email" class="w-full border border-gray-300 rounded px-3 py-2" placeholder="Email (opcional)" />
                                        <input id="cliente_telefone" class="w-full border border-gray-300 rounded px-3 py-2" placeholder="Telefone (opcional)" />
                                    </div>
                                </div>
                                <div class="px-6 py-4 border-t flex justify-end space-x-3">
                                    <button id="clienteCancel" class="px-4 py-2 rounded-md bg-gray-300 text-gray-800 hover:bg-gray-400">Cancelar</button>
                                    <button id="clienteSave" class="px-4 py-2 rounded-md bg-barber-600 text-white hover:bg-barber-700">Salvar</button>
                                </div>
                            </div>`;
                        document.body.appendChild(modal);

                        document.getElementById('clienteCloseX').addEventListener('click', function(){ document.getElementById('modalAddCliente').classList.add('hidden'); });
                        document.getElementById('clienteCancel').addEventListener('click', function(){ document.getElementById('modalAddCliente').classList.add('hidden'); });
                        document.getElementById('clienteSave').addEventListener('click', function(){
                            var nome = document.getElementById('cliente_nome').value.trim();
                            var email = document.getElementById('cliente_email').value.trim();
                            var telefone = document.getElementById('cliente_telefone').value.trim();
                            var errEl = document.getElementById('clienteInlineError');
                            errEl.textContent = '';
                            if(!nome){ errEl.textContent = 'Nome é obrigatório.'; return; }

                            fetch("{{ route('clientes.inline.store') }}",{
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
                                    // add to select
                                    var sel = document.querySelector('select[name="cliente_id"]');
                                    if(sel){
                                        var opt = document.createElement('option');
                                        opt.value = data.id;
                                        opt.text = data.nome;
                                        opt.selected = true;
                                        sel.appendChild(opt);
                                    }
                                    document.getElementById('modalAddCliente').classList.add('hidden');
                                } else {
                                    errEl.textContent = (data.message || 'Erro ao criar cliente.');
                                }
                            }).catch(function(){ errEl.textContent = 'Erro de rede ao criar cliente.'; });
                        });
                    }
                    document.getElementById('modalAddCliente').classList.remove('hidden');
                });
            }

            // Inline create service (only for owners)
            var addServiceBtn = document.getElementById('addServiceBtn');
            if(addServiceBtn){
                addServiceBtn.addEventListener('click', function(){
                        if(!document.getElementById('modalAddService')){
                        var modal = document.createElement('div');
                        modal.id = 'modalAddService';
                        modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center';
                        modal.innerHTML = `
                            <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4">
                                <div class="flex items-center justify-between px-6 py-4 border-b">
                                    <h3 class="text-lg font-semibold text-gray-900">Novo Serviço</h3>
                                    <button id="serviceCloseX" class="text-gray-400 hover:text-gray-600">&times;</button>
                                </div>
                                <div class="px-6 py-4">
                                    <div id="serviceInlineError" class="text-sm text-red-600 mb-2"></div>
                                    <div class="space-y-3">
                                        <input id="service_name" class="w-full border border-gray-300 rounded px-3 py-2" placeholder="Nome do serviço" />
                                        <textarea id="service_description" class="w-full border border-gray-300 rounded px-3 py-2" placeholder="Descrição (opcional)"></textarea>
                                    </div>
                                </div>
                                <div class="px-6 py-4 border-t flex justify-end space-x-3">
                                    <button id="serviceCancel" class="px-4 py-2 rounded-md bg-gray-300 text-gray-800 hover:bg-gray-400">Cancelar</button>
                                    <button id="serviceSave" class="px-4 py-2 rounded-md bg-barber-600 text-white hover:bg-barber-700">Salvar</button>
                                </div>
                            </div>`;
                        document.body.appendChild(modal);

                        document.getElementById('serviceCloseX').addEventListener('click', function(){ document.getElementById('modalAddService').classList.add('hidden'); });
                        document.getElementById('serviceCancel').addEventListener('click', function(){ document.getElementById('modalAddService').classList.add('hidden'); });
                        document.getElementById('serviceSave').addEventListener('click', function(){
                            var name = document.getElementById('service_name').value.trim();
                            var description = document.getElementById('service_description').value.trim();
                            var errEl = document.getElementById('serviceInlineError');
                            errEl.textContent = '';
                            if(!name){ errEl.textContent = 'Nome é obrigatório.'; return; }

                            fetch("{{ route('admin.services.inline.store') }}",{
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ name: name, description: description })
                            }).then(function(res){
                                return res.json();
                            }).then(function(data){
                                if(data && data.id){
                                    var sel = document.querySelector('select[name="servico"]');
                                    if(sel){
                                        var opt = document.createElement('option');
                                        opt.value = data.name;
                                        opt.text = data.name;
                                        opt.selected = true;
                                        sel.appendChild(opt);
                                    }
                                    document.getElementById('modalAddService').classList.add('hidden');
                                } else {
                                    errEl.textContent = (data.message || 'Erro ao criar serviço.');
                                }
                            }).catch(function(){ errEl.textContent = 'Erro de rede ao criar serviço.'; });
                        });
                    }
                    document.getElementById('modalAddService').classList.remove('hidden');
                });
            }
        });
    </script>
@endpush
