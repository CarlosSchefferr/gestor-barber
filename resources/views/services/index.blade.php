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
            'price' => $service->price,
            'commission' => $service->commission,
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

<div id="modalNovoServico" class="fixed inset-0 z-50 hidden pointer-events-none h-full w-full overflow-y-auto bg-zinc-900/60 backdrop-blur-[2px]">
    <div class="relative top-10 mx-auto mb-10 w-full max-w-2xl rounded-3xl border border-zinc-200 bg-white p-6 shadow-xl sm:p-8">
        <div class="mb-6">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-barber-500">Cadastro</p>
            <h3 class="mt-2 text-2xl font-bold text-zinc-900">Novo serviço</h3>
            <p class="mt-1 text-sm text-zinc-500">Preencha os dados para cadastrar o serviço</p>
        </div>
        <form action="{{ route('admin.services.store') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="text-sm font-semibold text-zinc-700">Nome <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required class="{{ $inputClass }}" placeholder="Ex: Corte + barba">
                </div>
                <div class="sm:col-span-2">
                    <label class="text-sm font-semibold text-zinc-700">Descrição</label>
                    <textarea name="description" rows="3" class="{{ $inputClass }} resize-none" placeholder="Descreva o serviço..."></textarea>
                </div>
                <div>
                    <label class="text-sm font-semibold text-zinc-700">Preço (R$) <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" min="0" name="price" required class="{{ $inputClass }}" placeholder="0,00">
                </div>
                <div>
                    <label class="text-sm font-semibold text-zinc-700">Comissão (R$)</label>
                    <input type="number" step="0.01" min="0" name="commission" class="{{ $inputClass }}" placeholder="0,00">
                </div>
            </div>
            <div class="flex justify-center gap-3 pt-3">
                <button type="button" onclick="fecharModalNovoServico()" class="inline-flex items-center justify-center rounded-2xl border border-zinc-300 bg-white px-5 py-3 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700 transition hover:bg-zinc-100">Cancelar</button>
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-barber-500 px-5 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-barber-600">Salvar serviço</button>
            </div>
        </form>
    </div>
</div>

<div id="modalEditarServico" class="fixed inset-0 z-50 hidden pointer-events-none h-full w-full overflow-y-auto bg-zinc-900/60 backdrop-blur-[2px]">
    <div class="relative top-10 mx-auto mb-10 w-full max-w-2xl rounded-3xl border border-zinc-200 bg-white p-6 shadow-xl sm:p-8">
        <div class="mb-6">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-barber-500">Edição</p>
            <h3 class="mt-2 text-2xl font-bold text-zinc-900">Editar serviço</h3>
            <p class="mt-1 text-sm text-zinc-500">Atualize os dados do serviço</p>
        </div>
        <form id="formEditarServico" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="text-sm font-semibold text-zinc-700">Nome <span class="text-red-500">*</span></label>
                    <input id="editarServicoNome" type="text" name="name" required class="{{ $inputClass }}">
                </div>
                <div class="sm:col-span-2">
                    <label class="text-sm font-semibold text-zinc-700">Descrição</label>
                    <textarea id="editarServicoDescricao" name="description" rows="3" class="{{ $inputClass }} resize-none"></textarea>
                </div>
                <div>
                    <label class="text-sm font-semibold text-zinc-700">Preço (R$) <span class="text-red-500">*</span></label>
                    <input id="editarServicoPreco" type="number" step="0.01" min="0" name="price" required class="{{ $inputClass }}">
                </div>
                <div>
                    <label class="text-sm font-semibold text-zinc-700">Comissão (R$)</label>
                    <input id="editarServicoComissao" type="number" step="0.01" min="0" name="commission" class="{{ $inputClass }}">
                </div>
            </div>
            <div class="flex justify-center gap-3 pt-3">
                <button type="button" onclick="fecharModalEditarServico()" class="inline-flex items-center justify-center rounded-2xl border border-zinc-300 bg-white px-5 py-3 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700 transition hover:bg-zinc-100">Cancelar</button>
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-barber-500 px-5 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-barber-600">Salvar alterações</button>
            </div>
        </form>
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
                    Cancelar
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

function abrirModalNovoServico() {
    document.getElementById('modalNovoServico').classList.remove('hidden', 'pointer-events-none');
}

function fecharModalNovoServico() {
    document.getElementById('modalNovoServico').classList.add('hidden', 'pointer-events-none');
}

function abrirModalEditarServico(id) {
    const servico = servicos[id];
    if (!servico) return;

    const form = document.getElementById('formEditarServico');
    form.action = `/admin/services/${id}`;
    document.getElementById('editarServicoNome').value = servico.name ?? '';
    document.getElementById('editarServicoDescricao').value = servico.description ?? '';
    document.getElementById('editarServicoPreco').value = Number(servico.price ?? 0).toFixed(2);
    document.getElementById('editarServicoComissao').value = Number(servico.commission ?? 0).toFixed(2);

    document.getElementById('modalEditarServico').classList.remove('hidden', 'pointer-events-none');
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
