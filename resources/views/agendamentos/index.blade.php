@extends('layouts.app')

@section('content')
@php
    $inputClass = 'mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20';
    $cardClass = 'rounded-3xl border border-zinc-200 bg-white/95 shadow-sm';
@endphp

<div id="agenda-page" x-data="{
    viewMode: localStorage.getItem('agendaView') || 'calendar',
    showFilters: false,
    calendarView: localStorage.getItem('calendarViewMode') || 'month',
    currentDate: new Date()
}" x-init="$watch('viewMode', v => localStorage.setItem('agendaView', v)); $watch('calendarView', v => localStorage.setItem('calendarViewMode', v));" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    <!-- Header -->
    <div class="mb-6 rounded-3xl border border-zinc-200 bg-white px-6 py-6 shadow-sm sm:px-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500">Agenda</p>
                <h1 class="mt-1 text-2xl font-bold leading-tight text-zinc-900 sm:text-3xl">Agendamentos</h1>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <!-- Toggle Filtros -->
                <button @click="showFilters = !showFilters" class="inline-flex items-center gap-2 rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm font-medium text-zinc-600 transition hover:bg-zinc-50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    <span x-text="showFilters ? 'Ocultar filtros' : 'Filtros'"></span>
                </button>

                <!-- Toggle Vista -->
                <div class="inline-flex rounded-xl border border-zinc-200 bg-white p-1">
                    <button @click="viewMode = 'calendar'" :class="viewMode === 'calendar' ? 'bg-barber-500 text-white' : 'text-zinc-600 hover:bg-zinc-50'" class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium transition">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Calendario
                    </button>
                    <button @click="viewMode = 'list'" :class="viewMode === 'list' ? 'bg-barber-500 text-white' : 'text-zinc-600 hover:bg-zinc-50'" class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium transition">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                        </svg>
                        Lista
                    </button>
                </div>

                <!-- Botao Novo -->
                <a href="{{ route('agendamentos.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-barber-500 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-barber-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Novo
                </a>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div x-show="showFilters" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" class="{{ $cardClass }} mb-6 p-5" style="display: none;">
        <form method="GET" id="filtersForm" class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            <input type="hidden" name="view" id="viewInput" value="{{ request('view', 'calendar') }}">

            <div>
                <label class="text-sm font-semibold text-zinc-700">Cliente</label>
                <x-custom-select name="cliente_id" :options="collect(['' => 'Todos os clientes'])->merge($clientes->pluck('nome', 'id'))->toArray()" :value="request('cliente_id', '')" placeholder="Selecione o cliente" />
            </div>

            <div>
                <label class="text-sm font-semibold text-zinc-700">Barbeiro</label>
                @if(auth()->check() && auth()->user()->isBarber())
                    <x-custom-select name="barbeiro_id" :options="['' => 'Todos os barbeiros', auth()->id() => auth()->user()->name]" :value="request('barbeiro_id', '')" placeholder="Selecione o barbeiro" />
                @else
                    <x-custom-select name="barbeiro_id" :options="collect(['' => 'Todos os barbeiros'])->merge($barbeiros->pluck('name', 'id'))->toArray()" :value="request('barbeiro_id', '')" placeholder="Selecione o barbeiro" />
                @endif
            </div>

            <div>
                <label class="text-sm font-semibold text-zinc-700">Data inicio</label>
                <input type="date" name="from" value="{{ request('from') }}" class="{{ $inputClass }}">
            </div>

            <div>
                <label class="text-sm font-semibold text-zinc-700">Data fim</label>
                <input type="date" name="to" value="{{ request('to') }}" class="{{ $inputClass }}">
            </div>

            <div class="lg:col-span-4 flex flex-wrap items-center justify-end gap-2 pt-2 border-t border-zinc-100">
                <a href="{{ route('agendamentos.index') }}" class="inline-flex items-center rounded-xl border border-zinc-200 bg-white px-4 py-2 text-sm font-medium text-zinc-600 transition hover:bg-zinc-50">
                    Limpar
                </a>
                <button type="submit" class="inline-flex items-center rounded-xl bg-barber-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-barber-600">
                    Aplicar filtros
                </button>
            </div>
        </form>
    </div>

    <!-- Calendario -->
    <div x-show="viewMode === 'calendar'" x-transition class="{{ $cardClass }} mb-6 overflow-hidden">
        <!-- Toolbar do Calendario -->
        <div class="flex flex-col gap-3 border-b border-zinc-100 bg-zinc-50/60 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Modo</span>
                <div class="inline-flex rounded-xl border border-zinc-200 bg-white p-0.5">
                    <button type="button" onclick="setCalendarView('day')" data-calendar-view-btn="day" class="rounded-lg px-3 py-1.5 text-xs font-medium text-zinc-600 transition hover:bg-zinc-50 data-[active=true]:bg-barber-500 data-[active=true]:text-white">
                        Dia
                    </button>
                    <button type="button" onclick="setCalendarView('week')" data-calendar-view-btn="week" class="rounded-lg px-3 py-1.5 text-xs font-medium text-zinc-600 transition hover:bg-zinc-50 data-[active=true]:bg-barber-500 data-[active=true]:text-white">
                        Semana
                    </button>
                    <button type="button" onclick="setCalendarView('month')" data-calendar-view-btn="month" class="rounded-lg px-3 py-1.5 text-xs font-medium text-zinc-600 transition hover:bg-zinc-50 data-[active=true]:bg-barber-500 data-[active=true]:text-white">
                        Mes
                    </button>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" onclick="shiftCalendar(-1)" class="inline-flex items-center gap-1 rounded-xl border border-zinc-200 bg-white px-3 py-1.5 text-sm font-medium text-zinc-600 transition hover:bg-zinc-50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Anterior
                </button>
                <button type="button" onclick="goToToday()" class="inline-flex items-center rounded-xl bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white transition hover:bg-zinc-800">
                    Hoje
                </button>
                <button type="button" onclick="shiftCalendar(1)" class="inline-flex items-center gap-1 rounded-xl border border-zinc-200 bg-white px-3 py-1.5 text-sm font-medium text-zinc-600 transition hover:bg-zinc-50">
                    Proximo
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>
        </div>
        <div id="custom-calendar" class="min-h-[400px] bg-white p-4 sm:p-5"></div>
    </div>

    <!-- Tabela de Agendamentos -->
    <div x-show="viewMode === 'list'" x-transition class="{{ $cardClass }} overflow-hidden">
        <div class="border-b border-zinc-200 px-6 py-4">
            <h3 class="text-lg font-bold text-zinc-900">Lista de agendamentos</h3>
            <p class="mt-1 text-sm text-zinc-500">Todos os agendamentos do periodo</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-zinc-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Data/Hora</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Cliente</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Servico</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Barbeiro</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Valor</th>
                        <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wide text-zinc-500">Acoes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 bg-white">
                    @forelse($agendamentos as $agendamento)
                        <tr class="transition hover:bg-zinc-50">
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="text-sm font-semibold text-zinc-900">
                                    {{ $agendamento->starts_at->format('d/m/Y') }}
                                </div>
                                <div class="text-sm text-zinc-500">
                                    {{ $agendamento->starts_at->format('H:i') }}
                                    @if($agendamento->ends_at)
                                        - {{ $agendamento->ends_at->format('H:i') }}
                                    @endif
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-barber-100">
                                        <span class="text-xs font-bold text-barber-700">{{ strtoupper(substr($agendamento->cliente->nome, 0, 2)) }}</span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-zinc-900">{{ $agendamento->cliente->nome }}</div>
                                        @if($agendamento->cliente->telefone)
                                            <div class="text-xs text-zinc-500">{{ $agendamento->cliente->telefone }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="inline-flex rounded-full bg-barber-100 px-2.5 py-1 text-xs font-semibold text-barber-700">
                                    {{ $agendamento->servico }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-700">
                                {{ $agendamento->barbeiro->name }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-bold text-emerald-600">
                                R$ {{ number_format($agendamento->price ?? 0, 2, ',', '.') }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" onclick="showEventModal({{ $agendamento->id }})" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-600 transition hover:bg-blue-50 hover:text-blue-600" title="Ver detalhes">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                    <a href="{{ route('agendamentos.edit', $agendamento) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-600 transition hover:bg-zinc-50 hover:text-barber-600" title="Editar">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5h6M4 21l4-4 9-9a2.828 2.828 0 10-4-4L4 13v8z"></path>
                                        </svg>
                                    </a>
                                    <button type="button" onclick="confirmDelete({{ $agendamento->id }})" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-600 transition hover:bg-red-50 hover:text-red-600" title="Remover">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100">
                                    <svg class="h-6 w-6 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-sm font-bold text-zinc-900">Nenhum agendamento encontrado</h3>
                                <p class="mt-1 text-sm text-zinc-500">Comece criando um novo agendamento.</p>
                                <a href="{{ route('agendamentos.create') }}" class="mt-4 inline-flex items-center justify-center rounded-2xl bg-barber-500 px-4 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-barber-600">
                                    Novo agendamento
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($agendamentos->hasPages())
            <div class="border-t border-zinc-200 bg-white px-6 py-4">
                {{ $agendamentos->links() }}
            </div>
        @endif
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
            <h3 class="mt-5 text-xl font-bold text-zinc-900">Confirmar exclusao</h3>
            <p class="mt-2 text-sm text-zinc-500">Tem certeza que deseja excluir este agendamento?</p>
            <div class="mt-6 flex justify-center gap-3">
                <button onclick="closeModal()" class="inline-flex items-center justify-center rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700 transition hover:bg-zinc-100">
                    Cancelar
                </button>
                <button id="confirmDeleteBtn" class="inline-flex items-center justify-center rounded-2xl bg-red-600 px-4 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-red-700">
                    Sim, excluir
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Calendar Grid Styles */
.gb-cal-grid {
    display: grid;
    grid-template-columns: repeat(7, minmax(0, 1fr));
    gap: 4px;
}
.gb-cal-day {
    min-height: 100px;
    border-radius: 12px;
    border: 1px solid rgba(228, 228, 231, 0.8);
    padding: 8px;
    transition: all 0.15s ease;
}
.gb-cal-day:hover {
    border-color: #c96f1f;
    box-shadow: 0 0 0 1px rgba(201, 111, 31, 0.15);
}
.gb-cal-day-today {
    border-color: #22c55e;
    box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.2);
}
.gb-cal-day-other {
    opacity: 0.4;
}
.gb-cal-day-number {
    font-weight: 700;
    font-size: 0.875rem;
    color: #18181b;
}
.gb-cal-day-today .gb-cal-day-number {
    background: #22c55e;
    color: white;
    width: 26px;
    height: 26px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
}
.gb-cal-event {
    margin-top: 4px;
    padding: 4px 8px;
    border-radius: 8px;
    font-size: 0.7rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.1s ease;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.gb-cal-event:hover {
    transform: scale(1.02);
    opacity: 0.9;
}
.gb-cal-event-barber {
    background: linear-gradient(135deg, #c96f1f 0%, #db934c 100%);
    color: white;
}
/* Week View */
.gb-cal-week-grid {
    display: grid;
    grid-template-columns: 60px repeat(7, minmax(0, 1fr));
    border-radius: 12px;
    border: 1px solid rgba(228, 228, 231, 0.8);
    overflow: hidden;
}
.gb-cal-week-header {
    padding: 8px;
    background: #fafafa;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    text-align: center;
    border-bottom: 1px solid rgba(228, 228, 231, 0.8);
}
.gb-cal-week-hour {
    padding: 4px 8px;
    font-size: 0.7rem;
    color: #71717a;
    border-right: 1px solid rgba(228, 228, 231, 0.6);
    border-bottom: 1px solid rgba(228, 228, 231, 0.4);
    background: #fafafa;
}
.gb-cal-week-cell {
    min-height: 48px;
    border-right: 1px solid rgba(228, 228, 231, 0.4);
    border-bottom: 1px solid rgba(228, 228, 231, 0.4);
    padding: 2px;
    cursor: pointer;
    transition: background-color 0.15s ease;
}
.gb-cal-week-cell:hover {
    background-color: rgba(201, 111, 31, 0.06);
}
/* Day View */
.gb-cal-day-view {
    border-radius: 12px;
    border: 1px solid rgba(228, 228, 231, 0.8);
    overflow: hidden;
}
.gb-cal-day-hour-row {
    display: grid;
    grid-template-columns: 70px 1fr;
    border-bottom: 1px solid rgba(228, 228, 231, 0.4);
}
.gb-cal-day-hour-label {
    padding: 12px;
    font-size: 0.75rem;
    font-weight: 500;
    color: #71717a;
    background: #fafafa;
    border-right: 1px solid rgba(228, 228, 231, 0.6);
}
.gb-cal-day-hour-content {
    min-height: 60px;
    padding: 4px 8px;
    cursor: pointer;
    transition: background-color 0.15s ease;
}
.gb-cal-day-hour-content:hover {
    background-color: rgba(201, 111, 31, 0.06);
}
</style>

<script>
let agendamentoIdToDelete = null;
let calendarEvents = {!! json_encode($calendarEvents) !!};
let calendarView = localStorage.getItem('calendarViewMode') || 'month';
let calendarCurrentDate = new Date();

function pad2(n) { return String(n).padStart(2, '0'); }

function formatDateKey(date) {
    const d = new Date(date);
    return `${d.getFullYear()}-${pad2(d.getMonth() + 1)}-${pad2(d.getDate())}`;
}

function groupEventsByDate(events) {
    const map = {};
    events.forEach(e => {
        const key = formatDateKey(e.start);
        if (!map[key]) map[key] = [];
        map[key].push(e);
    });
    return map;
}

function groupEventsByDateAndHour(events) {
    const map = {};
    events.forEach(e => {
        const d = new Date(e.start);
        const dateKey = formatDateKey(e.start);
        const hour = d.getHours();
        if (!map[dateKey]) map[dateKey] = {};
        if (!map[dateKey][hour]) map[dateKey][hour] = [];
        map[dateKey][hour].push(e);
    });
    return map;
}

function setCalendarView(view) {
    calendarView = view;
    try { localStorage.setItem('calendarViewMode', view); } catch(e) {}
    document.querySelectorAll('[data-calendar-view-btn]').forEach(btn => {
        btn.setAttribute('data-active', btn.getAttribute('data-calendar-view-btn') === view ? 'true' : 'false');
    });
    renderCalendar();
}

function shiftCalendar(direction) {
    const d = new Date(calendarCurrentDate);
    if (calendarView === 'day') d.setDate(d.getDate() + direction);
    else if (calendarView === 'week') d.setDate(d.getDate() + direction * 7);
    else d.setMonth(d.getMonth() + direction);
    calendarCurrentDate = d;
    renderCalendar();
}

function goToToday() {
    calendarCurrentDate = new Date();
    renderCalendar();
}

function renderCalendar() {
    const container = document.getElementById('custom-calendar');
    if (!container) return;

    document.querySelectorAll('[data-calendar-view-btn]').forEach(btn => {
        btn.setAttribute('data-active', btn.getAttribute('data-calendar-view-btn') === calendarView ? 'true' : 'false');
    });

    if (calendarView === 'day') renderDayView(container);
    else if (calendarView === 'week') renderWeekView(container);
    else renderMonthView(container);
}

function renderMonthView(container) {
    const base = new Date(calendarCurrentDate);
    const year = base.getFullYear();
    const month = base.getMonth();
    const firstOfMonth = new Date(year, month, 1);
    const startDay = (firstOfMonth.getDay() || 7) - 1; // Segunda = 0
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const eventsByDate = groupEventsByDate(calendarEvents);
    const weekdays = ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab', 'Dom'];
    const todayKey = formatDateKey(new Date());
    const monthNames = ['Janeiro', 'Fevereiro', 'Marco', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];

    let html = `
        <div class="mb-4">
            <h3 class="text-xl font-bold text-zinc-900">${monthNames[month]} ${year}</h3>
            <p class="text-sm text-zinc-500">Visualizacao mensal</p>
        </div>
        <div class="grid grid-cols-7 gap-1 mb-2">
            ${weekdays.map(d => `<div class="text-center text-xs font-bold uppercase tracking-wide text-zinc-400 py-2">${d}</div>`).join('')}
        </div>
        <div class="gb-cal-grid">
    `;

    const totalCells = Math.ceil((startDay + daysInMonth) / 7) * 7;
    for (let i = 0; i < totalCells; i++) {
        const dayNumber = i - startDay + 1;
        const inMonth = dayNumber >= 1 && dayNumber <= daysInMonth;
        let cellDate = null;
        if (inMonth) cellDate = new Date(year, month, dayNumber);
        const dateKey = cellDate ? formatDateKey(cellDate) : null;
        const events = dateKey && eventsByDate[dateKey] ? eventsByDate[dateKey] : [];
        const isToday = dateKey === todayKey;
        const otherClass = inMonth ? '' : ' gb-cal-day-other';
        const todayClass = isToday ? ' gb-cal-day-today' : '';

        html += `<div class="gb-cal-day${todayClass}${otherClass}" onclick="${inMonth ? `openNewAppointment('${dateKey}')` : ''}">`;
        if (inMonth) {
            html += `<div class="flex items-center justify-between mb-1">
                <span class="gb-cal-day-number">${dayNumber}</span>
                ${events.length ? `<span class="text-[0.65rem] text-zinc-400 font-medium">${events.length}</span>` : ''}
            </div>`;
            events.slice(0, 3).forEach(e => {
                const time = new Date(e.start).toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
                html += `<div class="gb-cal-event gb-cal-event-barber" onclick="event.stopPropagation(); showEventModal(${e.id})" title="${e.title}">${time} ${e.title}</div>`;
            });
            if (events.length > 3) {
                html += `<div class="mt-1 text-[0.65rem] text-zinc-500 font-medium">+${events.length - 3} mais</div>`;
            }
        }
        html += '</div>';
    }
    html += '</div>';
    container.innerHTML = html;
}

function renderWeekView(container) {
    const base = new Date(calendarCurrentDate);
    const day = base.getDay();
    const diff = (day === 0 ? -6 : 1 - day);
    const start = new Date(base);
    start.setDate(base.getDate() + diff);
    const weekdays = ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab', 'Dom'];
    const hourStart = 7, hourEnd = 21;
    const eventsByDateHour = groupEventsByDateAndHour(calendarEvents);
    const todayKey = formatDateKey(new Date());

    let headerDates = [];
    for (let i = 0; i < 7; i++) {
        const d = new Date(start);
        d.setDate(start.getDate() + i);
        headerDates.push(d);
    }

    let html = `
        <div class="mb-4">
            <h3 class="text-xl font-bold text-zinc-900">Semana de ${headerDates[0].toLocaleDateString('pt-BR', { day: '2-digit', month: 'short' })} a ${headerDates[6].toLocaleDateString('pt-BR', { day: '2-digit', month: 'short', year: 'numeric' })}</h3>
            <p class="text-sm text-zinc-500">Visualizacao semanal (${pad2(hourStart)}:00 - ${pad2(hourEnd)}:00)</p>
        </div>
        <div class="gb-cal-week-grid">
            <div class="gb-cal-week-header"></div>
    `;

    headerDates.forEach((d, idx) => {
        const key = formatDateKey(d);
        const isToday = key === todayKey;
        html += `<div class="gb-cal-week-header ${isToday ? 'bg-emerald-50 text-emerald-700' : ''}">${weekdays[idx]}<br><span class="text-xs font-normal">${d.getDate()}/${d.getMonth() + 1}</span></div>`;
    });

    for (let h = hourStart; h <= hourEnd; h++) {
        html += `<div class="gb-cal-week-hour">${pad2(h)}:00</div>`;
        headerDates.forEach(d => {
            const dateKey = formatDateKey(d);
            const eventsInSlot = (eventsByDateHour[dateKey] && eventsByDateHour[dateKey][h]) ? eventsByDateHour[dateKey][h] : [];
            html += `<div class="gb-cal-week-cell" onclick="openNewAppointment('${dateKey}', ${h})">`;
            eventsInSlot.forEach(e => {
                const patient = e.extendedProps?.cliente_name || e.title || 'Cliente';
                html += `<div class="gb-cal-event gb-cal-event-barber" onclick="event.stopPropagation(); showEventModal(${e.id})">${patient}</div>`;
            });
            html += '</div>';
        });
    }
    html += '</div>';
    container.innerHTML = html;
}

function renderDayView(container) {
    const base = new Date(calendarCurrentDate);
    const dateKey = formatDateKey(base);
    const eventsByDateHour = groupEventsByDateAndHour(calendarEvents);
    const hourStart = 7, hourEnd = 21;
    const dayNames = ['Domingo', 'Segunda-feira', 'Terca-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sabado'];

    let html = `
        <div class="mb-4">
            <h3 class="text-xl font-bold text-zinc-900">${dayNames[base.getDay()]}, ${base.toLocaleDateString('pt-BR', { day: '2-digit', month: 'long', year: 'numeric' })}</h3>
            <p class="text-sm text-zinc-500">Visualizacao diaria</p>
        </div>
        <div class="gb-cal-day-view">
    `;

    for (let h = hourStart; h <= hourEnd; h++) {
        const eventsInSlot = (eventsByDateHour[dateKey] && eventsByDateHour[dateKey][h]) ? eventsByDateHour[dateKey][h] : [];
        html += `<div class="gb-cal-day-hour-row">
            <div class="gb-cal-day-hour-label">${pad2(h)}:00</div>
            <div class="gb-cal-day-hour-content" onclick="openNewAppointment('${dateKey}', ${h})">`;
        eventsInSlot.forEach(e => {
            const patient = e.extendedProps?.cliente_name || e.title || 'Cliente';
            const time = new Date(e.start).toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
            const servico = e.extendedProps?.servico || '';
            html += `<div class="flex items-center gap-3 rounded-xl bg-gradient-to-r from-barber-500 to-barber-600 p-3 text-white mb-2 cursor-pointer transition hover:shadow-lg" onclick="event.stopPropagation(); showEventModal(${e.id})">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/20">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                </div>
                <div class="flex-1">
                    <div class="font-semibold">${patient}</div>
                    <div class="text-sm text-white/80">${time} • ${servico}</div>
                </div>
                <svg class="h-5 w-5 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </div>`;
        });
        html += '</div></div>';
    }
    html += '</div>';
    container.innerHTML = html;
}

function openNewAppointment(dateStr, hour) {
    let url = "{{ route('agendamentos.create') }}?date=" + dateStr;
    if (hour !== undefined) url += "&hour=" + hour;
    window.location.href = url;
}

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
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/agendamentos/${agendamentoIdToDelete}`;
        form.innerHTML = `<input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="{{ csrf_token() }}">`;
        document.body.appendChild(form);
        form.submit();
    }
});

document.getElementById('confirmModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

// Event Modal
var agendamentoMap = {};
calendarEvents.forEach(e => { agendamentoMap[e.id] = e; });

window.showEventModal = function(id) {
    try {
        var ev = agendamentoMap[id];
        if (!ev) { window.location.href = '/agendamentos/' + id + '/edit'; return; }
        var props = ev.extendedProps || {};

        if (!document.getElementById('eventModal')) {
            var modalHtml = `
            <div id="eventModal" class="fixed inset-0 z-50 hidden h-full w-full overflow-y-auto bg-zinc-900/60 backdrop-blur-[2px]">
                <div class="relative top-10 mx-auto w-full max-w-lg rounded-3xl border border-zinc-200 bg-white p-6 shadow-xl sm:p-8">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-barber-500">Detalhes do agendamento</p>
                            <h3 id="eventModalTitle" class="mt-2 text-xl font-bold text-zinc-900">Agendamento</h3>
                        </div>
                        <button id="eventModalCloseX" class="rounded-xl p-2 text-zinc-400 transition hover:bg-zinc-100 hover:text-zinc-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="mt-5 space-y-4">
                        <div class="flex items-center gap-4 rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-barber-100">
                                <svg class="h-6 w-6 text-barber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase text-zinc-500">Cliente</p>
                                <p id="eventModalCliente" class="text-sm font-medium text-zinc-900"></p>
                                <p id="eventModalTelefone" class="text-xs text-zinc-500"></p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                                <p class="text-xs font-semibold uppercase text-zinc-500">Data/Hora</p>
                                <p id="eventModalWhen" class="mt-1 text-sm font-medium text-zinc-900"></p>
                            </div>
                            <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                                <p class="text-xs font-semibold uppercase text-zinc-500">Barbeiro</p>
                                <p id="eventModalBarbeiro" class="mt-1 text-sm font-medium text-zinc-900"></p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                                <p class="text-xs font-semibold uppercase text-zinc-500">Servico</p>
                                <p id="eventModalServico" class="mt-1 text-sm font-medium text-zinc-900"></p>
                            </div>
                            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                                <p class="text-xs font-semibold uppercase text-emerald-600">Valor</p>
                                <p id="eventModalValor" class="mt-1 text-lg font-bold text-emerald-600"></p>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                            <p class="text-xs font-semibold uppercase text-zinc-500">Observacoes</p>
                            <p id="eventModalObservacoes" class="mt-1 text-sm text-zinc-700"></p>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-2">
                        <button id="eventModalClose2" class="inline-flex items-center justify-center rounded-xl border border-zinc-200 bg-white px-4 py-2 text-sm font-medium text-zinc-600 transition hover:bg-zinc-50">Fechar</button>
                        <button id="eventModalEdit" class="inline-flex items-center justify-center rounded-xl bg-barber-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-barber-600">Editar</button>
                        <button id="eventModalDelete" class="inline-flex items-center justify-center rounded-xl bg-red-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-red-700">Excluir</button>
                    </div>
                </div>
            </div>`;
            var wrapper = document.createElement('div');
            wrapper.innerHTML = modalHtml;
            document.body.appendChild(wrapper.firstElementChild);
            document.getElementById('eventModalClose2').addEventListener('click', closeEventModal);
            document.getElementById('eventModalCloseX').addEventListener('click', closeEventModal);
            document.getElementById('eventModal').addEventListener('click', function(e) { if (e.target === this) closeEventModal(); });
        }

        document.getElementById('eventModalTitle').textContent = ev.title || 'Agendamento';
        var whenText = '';
        if (ev.start) {
            var s = new Date(ev.start);
            whenText += s.toLocaleDateString('pt-BR') + ' as ' + s.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }
        if (ev.end) {
            var e = new Date(ev.end);
            whenText += ' - ' + e.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }
        document.getElementById('eventModalWhen').textContent = whenText;
        document.getElementById('eventModalCliente').textContent = props.cliente_name || '';
        document.getElementById('eventModalTelefone').textContent = props.cliente_phone || '';
        document.getElementById('eventModalServico').textContent = props.servico || '';
        document.getElementById('eventModalBarbeiro').textContent = props.barbeiro_name || '';

        var priceVal = (props.price || props.valor || 0);
        var formattedPrice = 'R$ 0,00';
        try { formattedPrice = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(Number(priceVal)); } catch (e) {}
        document.getElementById('eventModalValor').textContent = formattedPrice;
        document.getElementById('eventModalObservacoes').textContent = props.observacoes || 'Nenhuma observacao.';

        document.getElementById('eventModalEdit').onclick = function() { window.location.href = '/agendamentos/' + id + '/edit'; };
        document.getElementById('eventModalDelete').onclick = function() { closeEventModal(); confirmDelete(id); };
        document.getElementById('eventModal').classList.remove('hidden');
    } catch (err) {
        console.error('showEventModal error', err);
        window.location.href = '/agendamentos/' + id + '/edit';
    }
};

function closeEventModal() {
    var m = document.getElementById('eventModal');
    if (m) m.classList.add('hidden');
}

// Init
document.addEventListener('DOMContentLoaded', renderCalendar);
</script>
@endsection
