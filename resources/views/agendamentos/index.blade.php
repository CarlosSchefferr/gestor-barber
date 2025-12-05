@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Agendamentos</h1>
            <p class="text-gray-600 mt-1">Gerencie todos os agendamentos</p>
        </div>
        <a href="{{ route('agendamentos.create') }}" class="bg-barber-600 text-white px-4 py-2 rounded-lg hover:bg-barber-700 transition-colors shadow-sm">
            + Novo Agendamento
        </a>
    </div>



    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <form method="GET" id="filtersForm" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <input type="hidden" name="view" id="viewInput" value="{{ request('view', 'calendar') }}">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Cliente</label>
                <select name="cliente_id" class="w-full border-gray-300 rounded-md shadow-sm focus:border-barber-500 focus:ring-barber-500">
                    <option value="">Todos os clientes</option>
                    @foreach($clientes as $cliente)
                        <option value="{{ $cliente->id }}" {{ request('cliente_id') == $cliente->id ? 'selected' : '' }}>
                            {{ $cliente->nome }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Barbeiro</label>
                <select name="barbeiro_id" class="w-full border-gray-300 rounded-md shadow-sm focus:border-barber-500 focus:ring-barber-500">
                    <option value="">Todos os barbeiros</option>
                    @if(auth()->check() && auth()->user()->isBarber())
                        <option value="{{ auth()->id() }}" {{ request('barbeiro_id') == auth()->id() ? 'selected' : '' }}>
                            {{ auth()->user()->name }}
                        </option>
                    @else
                        @foreach($barbeiros as $barbeiro)
                            <option value="{{ $barbeiro->id }}" {{ request('barbeiro_id') == $barbeiro->id ? 'selected' : '' }}>
                                {{ $barbeiro->name }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Data Início</label>
                <input type="date" name="from" value="{{ request('from') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-barber-500 focus:ring-barber-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Data Fim</label>
                <input type="date" name="to" value="{{ request('to') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-barber-500 focus:ring-barber-500">
            </div>

            <div class="col-span-full">
                <div class="mt-4 border-t pt-4 flex items-center space-x-6">
                    <label class="inline-flex items-center">
                        <input type="checkbox" id="checkboxCalendar" class="form-checkbox h-4 w-4 text-barber-600">
                        <span class="ml-2 text-sm">Calendário</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="checkbox" id="checkboxList" class="form-checkbox h-4 w-4 text-barber-600">
                        <span class="ml-2 text-sm">Listagem</span>
                    </label>
                </div>
            </div>

            <div class="col-span-full flex justify-end space-x-3">
                <button type="submit" class="bg-barber-600 text-white px-4 py-2 rounded-md hover:bg-barber-700 transition-colors">
                    Filtrar
                </button>
                <a href="{{ route('agendamentos.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition-colors">
                    Limpar
                </a>
            </div>
        </form>
    </div>

    <!-- Calendário -->
    <div id="calendarContainer" class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-6 p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Calendário</h2>
        <div id="calendar"></div>
    </div>

    <!-- Tabela de Agendamentos -->
    <div id="listContainer" class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Data/Hora
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Cliente
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Serviço
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Barbeiro
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Valor
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Ações
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($agendamentos as $agendamento)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $agendamento->starts_at->format('d/m/Y') }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ $agendamento->starts_at->format('H:i') }}
                                    @if($agendamento->ends_at)
                                        - {{ $agendamento->ends_at->format('H:i') }}
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $agendamento->cliente->nome }}</div>
                                @if($agendamento->cliente->telefone)
                                    <div class="text-sm text-gray-500">{{ $agendamento->cliente->telefone }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-barber-100 text-barber-800">
                                    {{ $agendamento->servico }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $agendamento->barbeiro->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">
                                    R$ {{ number_format($agendamento->price ?? 0, 2, ',', '.') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">
                                    <button onclick="showEventModal({{ $agendamento->id }})"
                                            class="text-blue-600 hover:text-blue-900 transition-colors text-sm">
                                        Ver
                                    </button>
                                    <a href="{{ route('agendamentos.edit', $agendamento) }}"
                                       class="text-barber-600 hover:text-barber-900 transition-colors text-sm">
                                        Editar
                                    </a>
                                    <button onclick="confirmDelete({{ $agendamento->id }})"
                                            class="text-red-600 hover:text-red-900 transition-colors text-sm">
                                        Remover
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum agendamento encontrado</h3>
                                    <p class="text-gray-500 mb-4">Comece criando um novo agendamento.</p>
                                    <a href="{{ route('agendamentos.create') }}" class="bg-barber-600 text-white px-4 py-2 rounded-md hover:bg-barber-700 transition-colors">
                                        + Novo Agendamento
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        @if($agendamentos->hasPages())
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $agendamentos->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal de Confirmação -->
<div id="confirmModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mt-4">Confirmar Exclusão</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">
                    Tem certeza que deseja excluir este agendamento? Esta ação não pode ser desfeita.
                </p>
            </div>
            <div class="items-center px-4 py-3">
                <button id="confirmDeleteBtn" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition-colors mr-2">
                    Sim, Excluir
                </button>
                <button onclick="closeModal()" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 transition-colors">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let agendamentoIdToDelete = null;

function confirmDelete(id) {
    agendamentoIdToDelete = id;
    document.getElementById('confirmModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('confirmModal').classList.add('hidden');
    agendamentoIdToDelete = null;
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (agendamentoIdToDelete) {
        // Criar form para enviar DELETE
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/agendamentos/${agendamentoIdToDelete}`;

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

// Fechar modal ao clicar fora
document.getElementById('confirmModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>
@endsection

@push('scripts')
    <script>
        (function(){
            // Ensure FullCalendar CSS is present
            function ensureCSS(href){
                if(document.querySelector('link[href="'+href+'"]')) return;
                var link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = href;
                document.head.appendChild(link);
            }

            // Dynamically load a script and call cb on load/error
            function loadScript(src, cb){
                if(document.querySelector('script[src="'+src+'"]')){
                    // already added; wait for FullCalendar
                    return cb();
                }
                var s = document.createElement('script');
                s.src = src;
                s.onload = function(){ cb(); };
                s.onerror = function(){ console.error('Falha ao carregar script', src); cb(); };
                document.body.appendChild(s);
            }

            // Load script but non-fatal on error (silent)
            function loadScriptSilent(src, cb){
                if(document.querySelector('script[src="'+src+'"]')){
                    return cb();
                }
                var s = document.createElement('script');
                s.src = src;
                s.onload = function(){ cb(); };
                s.onerror = function(){ console.warn('Falha ao carregar (silent) script', src); cb(); };
                document.body.appendChild(s);
            }

            // try multiple CDN/local sources for FullCalendar (CSS + JS)
            // Try multiple versions and CDNs. We add FullCalendar v5 UMD bundle as most reliable single-file option,
            // then v6 candidates and finally a local vendor fallback.
            var cssCandidates = [
                'https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css',
                'https://unpkg.com/fullcalendar@5.11.3/main.min.css',
                'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css',
                'https://unpkg.com/@fullcalendar/core@6.1.8/main.min.css',
                'https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.8/main.min.css',
                '/vendor/fullcalendar/main.min.css'
            ];
            // JS candidates for FullCalendar (UMD bundles where available)
            var jsCandidates = [
                'https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js',
                'https://unpkg.com/fullcalendar@5.11.3/main.min.js',
                'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js',
                '/vendor/fullcalendar/main.min.js'
            ];

            // Try loading candidates sequentially. If none load or FullCalendar isn't available,
            // fallback to the simple calendar renderer.
            function tryCandidate(i){
                if(i >= jsCandidates.length){
                    // no candidate loaded -> fallback
                    _fallbackCalendar();
                    return;
                }

                // ensure CSS for candidate index if available
                try{
                    var css = cssCandidates[i] || cssCandidates[0];
                    ensureCSS(css);
                }catch(e){}

                loadScript(jsCandidates[i], function(){
                    // give the script a tick to set globals
                    setTimeout(function(){
                        if(typeof FullCalendar !== 'undefined' || window.FullCalendar){
                            try{ _createCalendar(); }catch(e){ console.error(e); _fallbackCalendar(); }
                        } else {
                            // try next candidate
                            tryCandidate(i+1);
                        }
                    }, 50);
                });
            }
            function initCalendar(){
                tryCandidate(0);
            }

            // Fallback simple calendar renderer (no external libs)
            function _fallbackCalendar(){
                try{
                    var container = document.getElementById('calendar');
                    if(!container) return;
                    // Clear and build a simple month grid
                    container.innerHTML = '';

                    var events = {!! json_encode($calendarEvents) !!};
                    var eventsByDate = {};
                    events.forEach(function(ev){
                        try{
                            var d = new Date(ev.start);
                            var key = d.toISOString().slice(0,10);
                            eventsByDate[key] = eventsByDate[key] || [];
                            eventsByDate[key].push(ev);
                        }catch(e){}
                    });

                    var now = new Date();
                    var year = now.getFullYear();
                    var month = now.getMonth();

                    var first = new Date(year, month, 1);
                    var startDay = first.getDay(); // 0-6 (Sun-Sat)
                    var daysInMonth = new Date(year, month+1, 0).getDate();

                    var title = document.createElement('div');
                    title.className = 'mb-4 flex items-center justify-between';
                    title.innerHTML = '<h3 class="text-lg font-medium text-gray-900">' + first.toLocaleString('pt-BR', { month: 'long', year: 'numeric' }) + '</h3>';
                    container.appendChild(title);

                    var grid = document.createElement('div');
                    grid.style.display = 'grid';
                    grid.style.gridTemplateColumns = 'repeat(7, 1fr)';
                    grid.style.gap = '6px';

                    var weekdays = ['Dom','Seg','Ter','Qua','Qui','Sex','Sab'];
                    weekdays.forEach(function(w){
                        var h = document.createElement('div');
                        h.className = 'text-center text-xs font-medium text-gray-600';
                        h.textContent = w;
                        grid.appendChild(h);
                    });

                    for(var i=0;i<startDay;i++){
                        var blank = document.createElement('div');
                        blank.className = 'h-24 border rounded p-2 bg-gray-50';
                        grid.appendChild(blank);
                    }

                    for(var day=1; day<=daysInMonth; day++){
                        var date = new Date(year, month, day);
                        var iso = date.toISOString().slice(0,10);
                        var cell = document.createElement('div');
                        cell.className = 'h-24 border rounded p-2 bg-white overflow-hidden';
                        var daynum = document.createElement('div');
                        daynum.className = 'text-sm font-medium text-gray-800';
                        daynum.textContent = day;
                        cell.appendChild(daynum);

                            if(eventsByDate[iso]){
                            var list = document.createElement('div');
                            list.className = 'mt-1 text-xs text-gray-700 space-y-1 overflow-auto';
                            eventsByDate[iso].forEach(function(ev){
                                var evEl = document.createElement('div');
                                evEl.className = 'px-1 py-0.5 rounded cursor-pointer';
                                evEl.textContent = ev.title;
                                // apply event color if provided
                                try{
                                    if(ev.backgroundColor){ evEl.style.backgroundColor = ev.backgroundColor; }
                                    else if(ev.extendedProps && ev.extendedProps.color){ evEl.style.backgroundColor = ev.extendedProps.color; }
                                    else { evEl.style.backgroundColor = '#e6f4ff'; }
                                    evEl.style.color = (ev.textColor || '#ffffff');
                                }catch(e){}
                                // click event: open modal with details if id present
                                if(ev.id){
                                    evEl.addEventListener('click', function(e){
                                        e.stopPropagation();
                                        if(typeof showEventModal === 'function') showEventModal(ev.id);
                                    });
                                }
                                list.appendChild(evEl);
                            });
                            cell.appendChild(list);
                        }

                        (function(diso){
                            cell.style.cursor = 'pointer';
                            cell.addEventListener('click', function(){
                                window.location.href = "{{ route('agendamentos.create') }}" + '?date=' + diso;
                            });
                        })(iso);

                        grid.appendChild(cell);
                    }

                    container.appendChild(grid);
                }catch(e){
                    console.error('Fallback calendar failed', e);
                }
            }

            function _createCalendar(){
                var calendarEl = document.getElementById('calendar');
                if(!calendarEl) return;

                // Clear previous content
                calendarEl.innerHTML = '';

                try{
                    var calendar = new FullCalendar.Calendar(calendarEl, {
                        initialView: 'dayGridMonth',
                        height: 600,
                        locale: 'pt-br',
                        // fallback/overrides for Portuguese labels when locale file isn't loaded
                        buttonText: {
                            today: 'Hoje',
                            month: 'Mês',
                            week: 'Semana',
                            day: 'Dia',
                            list: 'Lista'
                        },
                        firstDay: 1,
                        dayHeaderFormat: { weekday: 'short' },
                        headerToolbar: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'dayGridMonth,timeGridWeek,timeGridDay'
                        },
                        dateClick: function(info) {
                            var dateStr = info.dateStr; // YYYY-MM-DD
                            var url = "{{ route('agendamentos.create') }}" + '?date=' + dateStr;
                            window.location.href = url;
                        },
                        eventClick: function(info){
                                var id = info.event.id;
                                if(id && typeof showEventModal === 'function'){
                                    showEventModal(id);
                                } else if(id){
                                    window.location.href = '/agendamentos/' + id + '/edit';
                                }
                            },
                        events: {!! json_encode($calendarEvents) !!}
                    });
                    calendar.render();
                } catch(e){
                    console.error('Erro ao inicializar FullCalendar', e);
                }
            }

                    // Make a map of appointments for modal lookup
                    var agendamentoMap = {};
                    (function(){
                        try{
                            var evs = {!! json_encode($calendarEvents) !!};
                            evs.forEach(function(e){
                                agendamentoMap[e.id] = e;
                            });
                        }catch(e){ console.warn('agendamentoMap init failed', e); }
                    })();

                    // Show event details modal populated from agendamentoMap
                    window.showEventModal = function(id){
                        try{
                            var ev = agendamentoMap[id];
                            if(!ev) {
                                // if not in map, redirect to edit
                                window.location.href = '/agendamentos/' + id + '/edit';
                                return;
                            }

                            var props = ev.extendedProps || {};

                            // create modal if not exists
                            if(!document.getElementById('eventModal')){
                                var modalHtml = `
                                <div id="eventModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-start sm:items-center justify-center">
                                    <div class="relative mt-20 sm:mt-0 mx-4 sm:mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white max-h-[80vh] overflow-y-auto">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h3 id="eventModalTitle" class="text-xl font-semibold text-gray-900">Detalhes do Serviço</h3>
                                                <p id="eventModalWhen" class="text-sm text-gray-600 mt-1"></p>
                                            </div>
                                            <div>
                                                <button id="eventModalCloseX" class="text-gray-500 hover:text-gray-700">&times;</button>
                                            </div>
                                        </div>

                                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                            <div>
                                                <p><span class="font-semibold">Cliente:</span> <span id="eventModalCliente" class="text-gray-900"></span></p>
                                                <p><span class="font-semibold">Telefone:</span> <span id="eventModalTelefone" class="text-gray-900"></span></p>
                                            </div>
                                            <div>
                                                <p><span class="font-semibold">Serviço:</span> <span id="eventModalServico" class="text-gray-900"></span></p>
                                                 <p><span class="font-semibold">Barbeiro:</span> <span id="eventModalBarbeiro" class="text-gray-900"></span></p>
                                                <p class="mt-2"><span class="font-semibold">Valor:</span> <span id="eventModalValor" class="text-gray-900"></span></p>
                                            </div>
                                        </div>

                                        <div class="mt-4">
                                            <p class="text-sm font-semibold">Observações</p>
                                            <p id="eventModalObservacoes" class="text-sm text-gray-900"></p>
                                        </div>

                                        <div class="mt-6 flex justify-end space-x-3">
                                            <button id="eventModalEdit" class="bg-barber-600 text-white px-4 py-2 rounded-md hover:bg-barber-700 transition-colors">Editar</button>
                                            <button id="eventModalDelete" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition-colors">Excluir</button>
                                            <button id="eventModalClose2" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-400 transition-colors">Fechar</button>
                                        </div>
                                    </div>
                                </div>`;
                                var wrapper = document.createElement('div');
                                wrapper.innerHTML = modalHtml;
                                document.body.appendChild(wrapper.firstElementChild);

                                // attach close handlers
                                document.getElementById('eventModalClose2').addEventListener('click', closeEventModal);
                                document.getElementById('eventModalCloseX').addEventListener('click', closeEventModal);
                                document.getElementById('eventModal').addEventListener('click', function(e){ if(e.target === this) closeEventModal(); });
                            }

                            // populate fields
                            document.getElementById('eventModalTitle').textContent = ev.title || 'Agendamento';
                            var whenText = '';
                            if(ev.start){
                                var s = new Date(ev.start);
                                whenText += s.toLocaleDateString('pt-BR') + ' ' + s.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                            }
                            if(ev.end){
                                var e = new Date(ev.end);
                                whenText += ' - ' + e.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                            }
                            document.getElementById('eventModalWhen').textContent = whenText;

                            document.getElementById('eventModalCliente').textContent = props.cliente_name || '';
                            document.getElementById('eventModalTelefone').textContent = props.cliente_phone || '';
                            document.getElementById('eventModalServico').textContent = props.servico || '';
                            document.getElementById('eventModalBarbeiro').textContent = props.barbeiro_name || '';
                            // Valor: tentar várias chaves possíveis
                            var priceVal = (props.price || props.valor || ev.price || (ev.extendedProps && ev.extendedProps.price) || 0);
                            var formattedPrice = 'R$ 0,00';
                            try{
                                formattedPrice = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(Number(priceVal));
                            }catch(e){ /* fallback já definido */ }
                            if(document.getElementById('eventModalValor')) document.getElementById('eventModalValor').textContent = formattedPrice;

                            document.getElementById('eventModalObservacoes').textContent = props.observacoes || '';

                            // edit/delete handlers
                            document.getElementById('eventModalEdit').onclick = function(){ window.location.href = '/agendamentos/' + id + '/edit'; };
                            document.getElementById('eventModalDelete').onclick = function(){ closeEventModal(); confirmDelete(id); };

                            // show modal
                            document.getElementById('eventModal').classList.remove('hidden');
                        }catch(err){
                            console.error('showEventModal error', err);
                            window.location.href = '/agendamentos/' + id + '/edit';
                        }
                    };

                    function closeEventModal(){
                        var m = document.getElementById('eventModal');
                        if(m) m.classList.add('hidden');
                    }

            if(document.readyState === 'loading'){
                document.addEventListener('DOMContentLoaded', initCalendar);
            } else {
                initCalendar();
            }

            // --- view toggle script (kept here) ---
            (function(){
                try{
                    function getViewParam(){
                        const params = new URLSearchParams(window.location.search);
                        return params.get('view') || (document.getElementById('viewInput') ? document.getElementById('viewInput').value : 'calendar');
                    }

                    function setViewParam(view){
                        try{
                            const params = new URLSearchParams(window.location.search);
                            params.set('view', view);
                            // Setting search will reload the page with the chosen view
                            window.location.search = params.toString();
                        }catch(err){ console.warn('setViewParam failed', err); }
                    }

                    const chkCal = document.getElementById('checkboxCalendar');
                    const chkList = document.getElementById('checkboxList');
                    const calendarContainer = document.getElementById('calendarContainer');
                    const listContainer = document.getElementById('listContainer');
                    const viewInput = document.getElementById('viewInput');

                    function applyView(view){
                        try{
                            if(view === 'list'){
                                if(calendarContainer) calendarContainer.style.display = 'none';
                                if(listContainer) listContainer.style.display = 'block';
                                if(chkList) chkList.checked = true;
                                if(chkCal) chkCal.checked = false;
                            } else {
                                if(calendarContainer) calendarContainer.style.display = 'block';
                                if(listContainer) listContainer.style.display = 'none';
                                if(chkCal) chkCal.checked = true;
                                if(chkList) chkList.checked = false;
                            }
                            if(viewInput) viewInput.value = view;
                        }catch(err){ console.warn('applyView failed', err); }
                    }

                    // Apply initial view after a tick to ensure elements exist
                    setTimeout(function(){
                        const initialView = getViewParam();
                        applyView(initialView);
                    }, 0);

                    if(chkCal) chkCal.addEventListener('change', function(){ if(this.checked){ setViewParam('calendar'); } else { setViewParam('list'); } });
                    if(chkList) chkList.addEventListener('change', function(){ if(this.checked){ setViewParam('list'); } else { setViewParam('calendar'); } });

                    const filtersForm = document.getElementById('filtersForm');
                    if(filtersForm){
                        filtersForm.addEventListener('submit', function(){ if(viewInput) viewInput.value = getViewParam(); });
                    }
                }catch(e){
                    console.error('View toggle init failed', e);
                    // As a fallback, try to set sensible defaults
                    try{ if(document.getElementById('calendarContainer')) document.getElementById('calendarContainer').style.display = 'block'; }catch(e){}
                    try{ if(document.getElementById('listContainer')) document.getElementById('listContainer').style.display = 'none'; }catch(e){}
                }
            })();

        })();
    </script>
@endpush
