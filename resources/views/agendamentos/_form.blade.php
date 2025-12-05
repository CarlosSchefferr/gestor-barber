<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700">Cliente</label>
        <div class="flex items-center space-x-2">
            <select name="cliente_id" class="mt-1 block flex-1 border-gray-300 rounded">
                @foreach($clientes as $c)
                    <option value="{{ $c->id }}" {{ (old('cliente_id') ?? ($agendamento->cliente_id ?? '')) == $c->id ? 'selected' : '' }}>{{ $c->nome }}</option>
                @endforeach
            </select>
            <button type="button" id="addClienteBtnInline" class="inline-flex items-center px-3 py-2 rounded-md bg-barber-600 text-white hover:bg-barber-700">+</button>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Barbeiro</label>
        @if(auth()->check() && auth()->user()->isBarber())
            <select name="barbeiro_id" class="mt-1 block w-full border-gray-300 rounded">
                <option value="{{ auth()->id() }}" selected>{{ auth()->user()->name }}</option>
            </select>
        @else
            <select name="barbeiro_id" class="mt-1 block w-full border-gray-300 rounded">
                @foreach($barbeiros as $b)
                    <option value="{{ $b->id }}" {{ (old('barbeiro_id') ?? ($agendamento->barbeiro_id ?? '')) == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                @endforeach
            </select>
        @endif
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Data e hora início</label>
        <input type="datetime-local" name="starts_at" value="{{ old('starts_at', request('date') ? request('date')."T09:00" : (isset($agendamento) ? $agendamento->starts_at->format('Y-m-d\\TH:i') : '')) }}" class="mt-1 block w-full border-gray-300 rounded">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Data e hora fim</label>
        <input type="datetime-local" name="ends_at" value="{{ old('ends_at') ?? (isset($agendamento) && $agendamento->ends_at ? $agendamento->ends_at->format('Y-m-d\TH:i') : '') }}" class="mt-1 block w-full border-gray-300 rounded">
    </div>

    <div class="col-span-2">
        <label class="block text-sm font-medium text-gray-700">Serviço</label>
        <div class="flex items-center space-x-2">
            <select name="servico" class="mt-1 block flex-1 w-full border-gray-300 rounded">
                <option value="">Selecione um serviço</option>
                @if(isset($services))
                    @foreach($services as $s)
                        <option value="{{ $s->name }}" data-price="{{ $s->price ?? '0.00' }}" {{ (old('servico') ?? ($agendamento->servico ?? '')) == $s->name ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                @endif
            </select>
            @if(auth()->check() && auth()->user()->isOwner())
                <button type="button" id="addServiceBtnInline" class="inline-flex items-center px-3 py-2 rounded-md bg-barber-600 text-white hover:bg-barber-700">+</button>
            @endif
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Preço</label>
        <input type="number" step="0.01" name="price" value="{{ old('price') ?? ($agendamento->price ?? '') }}" class="mt-1 block w-full border-gray-300 rounded">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Cor (para calendário)</label>
        <input type="color" name="color" value="{{ old('color') ?? ($agendamento->color ?? '#3b82f6') }}" class="mt-1 h-10 w-16 p-0 border rounded">
    </div>

    <div class="col-span-2">
        <label class="block text-sm font-medium text-gray-700">Observações</label>
        <textarea name="observacoes" class="mt-1 block w-full border-gray-300 rounded">{{ old('observacoes') ?? ($agendamento->observacoes ?? '') }}</textarea>
    </div>
</div>

@push('scripts')
    <script>
        // wire up inline buttons in partials
        document.addEventListener('DOMContentLoaded', function(){
            var addClienteBtnInline = document.getElementById('addClienteBtnInline');
            if(addClienteBtnInline){
                addClienteBtnInline.addEventListener('click', function(){
                    // reuse modal from create view if present
                    var ev = new Event('click');
                    var mainBtn = document.getElementById('addClienteBtn');
                    if(mainBtn){ mainBtn.dispatchEvent(ev); return; }
                    // otherwise open a simple prompt fallback
                    var nome = prompt('Nome do cliente');
                    if(!nome) return;
                    fetch("{{ route('clientes.inline.store') }}",{
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ nome: nome })
                    }).then(r=>r.json()).then(function(data){
                        if(data && data.id){
                            var sel = document.querySelector('select[name="cliente_id"]');
                            if(sel){ var opt = document.createElement('option'); opt.value = data.id; opt.text = data.nome; opt.selected = true; sel.appendChild(opt); }
                        } else alert('Erro ao criar cliente');
                    }).catch(function(){ alert('Erro ao criar cliente'); });
                });
            }

            var addServiceBtnInline = document.getElementById('addServiceBtnInline');
            if(addServiceBtnInline){
                addServiceBtnInline.addEventListener('click', function(){
                    var ev = new Event('click');
                    var mainBtn = document.getElementById('addServiceBtn');
                    if(mainBtn){ mainBtn.dispatchEvent(ev); return; }
                    var nome = prompt('Nome do serviço');
                    if(!nome) return;
                    // fallback minimal create: send defaults for price/commission
                    fetch("{{ route('admin.services.inline.store') }}",{
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ name: nome, price: 0.00, commission: 0.00 })
                    }).then(r=>r.json()).then(function(data){
                        if(data && data.id){
                            var sel = document.querySelector('select[name="servico"]');
                            if(sel){
                                var opt = document.createElement('option');
                                opt.value = data.name;
                                opt.text = data.name;
                                if(data.price) opt.setAttribute('data-price', data.price);
                                opt.selected = true;
                                sel.appendChild(opt);
                            }
                            // set appointment price if returned
                            if(data && data.price){
                                var priceInput = document.querySelector('input[name="price"]');
                                if(priceInput){ priceInput.value = parseFloat(data.price).toFixed(2); }
                            }
                        } else alert('Erro ao criar serviço');
                    }).catch(function(){ alert('Erro ao criar serviço'); });
                });
            }

            // when service select changes, set price input
            var servicoSel = document.querySelector('select[name="servico"]');
            var priceInput = document.querySelector('input[name="price"]');
            function applyServicePrice(){
                if(!servicoSel || !priceInput) return;
                var opt = servicoSel.options[servicoSel.selectedIndex];
                if(opt && opt.dataset && opt.dataset.price){
                    priceInput.value = parseFloat(opt.dataset.price || 0).toFixed(2);
                }
            }
            if(servicoSel){
                servicoSel.addEventListener('change', applyServicePrice);
                // apply on load only if price field is empty
                if(priceInput && (priceInput.value === '' || priceInput.value === null)){
                    applyServicePrice();
                }
            }
        });
    </script>
@endpush
