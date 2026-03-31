@extends('layouts.app')

@section('content')
@php
    $inputClass = 'mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20';
    $cardClass = 'rounded-3xl border border-zinc-200 bg-white/95 shadow-sm';
    $produtosJs = $products->keyBy('id')->map(function ($product) {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'quantity' => $product->quantity,
        ];
    })->toArray();
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-8 rounded-3xl border border-zinc-200 bg-white px-6 py-7 shadow-sm sm:px-8">
        <div class="flex flex-col gap-5">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500">Estoque</p>
                <h1 class="mt-2 text-3xl font-bold leading-tight text-zinc-900 sm:text-4xl">Produtos</h1>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button type="button" onclick="abrirModalNovoProduto()" class="inline-flex items-center justify-center rounded-2xl bg-barber-500 px-4 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-barber-600">
                    Novo produto
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
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Total de produtos</p>
            <p class="mt-3 text-3xl font-bold text-zinc-900">{{ $totalProdutos ?? 0 }}</p>
            <p class="mt-1 text-sm text-zinc-500">Itens cadastrados</p>
        </div>
        <div class="{{ $cardClass }} p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Itens em estoque</p>
            <p class="mt-3 text-3xl font-bold text-zinc-900">{{ $itensEmEstoque ?? 0 }}</p>
            <p class="mt-1 text-sm text-zinc-500">Soma das quantidades</p>
        </div>
        <div class="{{ $cardClass }} p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Estoque baixo</p>
            <p class="mt-3 text-3xl font-bold text-amber-600">{{ $estoqueBaixo ?? 0 }}</p>
            <p class="mt-1 text-sm text-zinc-500">Entre 1 e 5 unidades</p>
        </div>
        <div class="{{ $cardClass }} p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Sem estoque</p>
            <p class="mt-3 text-3xl font-bold text-red-600">{{ $semEstoque ?? 0 }}</p>
            <p class="mt-1 text-sm text-zinc-500">Produtos zerados</p>
        </div>
    </div>

    <div class="{{ $cardClass }} mb-8 p-6 sm:p-7">
        <div class="mb-5 flex items-center justify-between">
            <h2 class="text-lg font-bold text-zinc-900">Filtros</h2>
            <span class="rounded-full border border-zinc-200 bg-zinc-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-zinc-500">Busca avançada</span>
        </div>
        <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <label class="text-sm font-semibold text-zinc-700">Buscar produto</label>
                <input type="text" name="search" value="{{ request('search') }}" class="{{ $inputClass }}" placeholder="Nome ou descrição">
            </div>
            <div>
                <label class="text-sm font-semibold text-zinc-700">Estoque</label>
                <x-custom-select name="stock" :options="['' => 'Todos', 'out' => 'Sem estoque', 'low' => 'Baixo', 'ok' => 'Normal']" :value="request('stock', '')" placeholder="Selecione o estoque" />
            </div>
            <div>
                <label class="text-sm font-semibold text-zinc-700">Ordenar por</label>
                <x-custom-select name="sort" :options="['name' => 'Nome', 'price' => 'Preço', 'quantity' => 'Quantidade']" :value="request('sort', 'name')" placeholder="Selecione a ordenação" />
            </div>
            <div class="md:col-span-3 flex flex-wrap items-center justify-center gap-3 pt-1">
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-barber-500 px-5 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-barber-600">Aplicar filtros</button>
                <a href="{{ route('admin.products.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-zinc-300 bg-white px-5 py-3 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700 transition hover:bg-zinc-100">Limpar</a>
            </div>
        </form>
    </div>

    <!-- Tabela de Produtos -->
    <div class="{{ $cardClass }} overflow-hidden">
        <div class="border-b border-zinc-200 px-6 py-4">
            <h3 class="text-lg font-bold text-zinc-900">Lista de produtos</h3>
            <p class="mt-1 text-sm text-zinc-500">Todos os produtos cadastrados no sistema</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-zinc-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Produto</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Descrição</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Preço</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Quantidade</th>
                        <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wide text-zinc-500">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 bg-white">
                    @forelse($products as $product)
                        <tr class="transition hover:bg-zinc-50">
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-barber-100">
                                        <svg class="h-5 w-5 text-barber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-semibold text-zinc-900">{{ $product->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-zinc-600">{{ Str::limit($product->description ?? '-', 60) }}</span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="text-sm font-semibold text-emerald-600">R$ {{ number_format($product->price ?? 0, 2, ',', '.') }}</span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                @if($product->quantity <= 5)
                                    <span class="inline-flex rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-700">
                                        {{ $product->quantity }} unidades
                                    </span>
                                @elseif($product->quantity <= 15)
                                    <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                        {{ $product->quantity }} unidades
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                        {{ $product->quantity }} unidades
                                    </span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" onclick="abrirModalEditarProduto({{ $product->id }})" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-600 transition hover:bg-zinc-50 hover:text-barber-600" title="Editar">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5h6M4 21l4-4 9-9a2.828 2.828 0 10-4-4L4 13v8z"></path>
                                        </svg>
                                    </button>
                                    <button type="button" onclick="confirmDelete({{ $product->id }})" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-600 transition hover:bg-red-50 hover:text-red-600" title="Remover">
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
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                </div>
                                <h3 class="text-sm font-bold text-zinc-900">Nenhum produto encontrado</h3>
                                <p class="mt-1 text-sm text-zinc-500">Comece cadastrando um novo produto.</p>
                                <button type="button" onclick="abrirModalNovoProduto()" class="mt-4 inline-flex items-center justify-center rounded-2xl bg-barber-500 px-4 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-barber-600">
                                    Novo produto
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="border-t border-zinc-200 bg-white px-6 py-4">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>

<div id="modalNovoProduto" class="fixed inset-0 z-50 hidden pointer-events-none h-full w-full overflow-y-auto bg-zinc-900/60 backdrop-blur-[2px]">
    <div class="relative top-10 mx-auto mb-10 w-full max-w-2xl rounded-3xl border border-zinc-200 bg-white p-6 shadow-xl sm:p-8">
        <div class="mb-6">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-barber-500">Cadastro</p>
            <h3 class="mt-2 text-2xl font-bold text-zinc-900">Novo produto</h3>
            <p class="mt-1 text-sm text-zinc-500">Preencha os dados para cadastrar o produto</p>
        </div>
        <form action="{{ route('admin.products.store') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="text-sm font-semibold text-zinc-700">Nome <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required class="{{ $inputClass }}" placeholder="Ex: Pomada modeladora">
                </div>
                <div class="sm:col-span-2">
                    <label class="text-sm font-semibold text-zinc-700">Descrição</label>
                    <textarea name="description" rows="3" class="{{ $inputClass }} resize-none" placeholder="Descreva o produto..."></textarea>
                </div>
                <div>
                    <label class="text-sm font-semibold text-zinc-700">Preco (R$) <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" min="0" name="price" required class="{{ $inputClass }}" placeholder="0,00">
                </div>
                <div>
                    <label class="text-sm font-semibold text-zinc-700">Quantidade <span class="text-red-500">*</span></label>
                    <input type="number" min="0" name="quantity" required class="{{ $inputClass }}" placeholder="0">
                </div>
            </div>
            <div class="flex justify-center gap-3 pt-3">
                <button type="button" onclick="fecharModalNovoProduto()" class="inline-flex items-center justify-center rounded-2xl border border-zinc-300 bg-white px-5 py-3 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700 transition hover:bg-zinc-100">Cancelar</button>
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-barber-500 px-5 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-barber-600">Salvar produto</button>
            </div>
        </form>
    </div>
</div>

<div id="modalEditarProduto" class="fixed inset-0 z-50 hidden pointer-events-none h-full w-full overflow-y-auto bg-zinc-900/60 backdrop-blur-[2px]">
    <div class="relative top-10 mx-auto mb-10 w-full max-w-2xl rounded-3xl border border-zinc-200 bg-white p-6 shadow-xl sm:p-8">
        <div class="mb-6">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-barber-500">Edição</p>
            <h3 class="mt-2 text-2xl font-bold text-zinc-900">Editar produto</h3>
            <p class="mt-1 text-sm text-zinc-500">Atualize os dados do produto</p>
        </div>
        <form id="formEditarProduto" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="text-sm font-semibold text-zinc-700">Nome <span class="text-red-500">*</span></label>
                    <input id="editarProdutoNome" type="text" name="name" required class="{{ $inputClass }}">
                </div>
                <div class="sm:col-span-2">
                    <label class="text-sm font-semibold text-zinc-700">Descrição</label>
                    <textarea id="editarProdutoDescricao" name="description" rows="3" class="{{ $inputClass }} resize-none"></textarea>
                </div>
                <div>
                    <label class="text-sm font-semibold text-zinc-700">Preco (R$) <span class="text-red-500">*</span></label>
                    <input id="editarProdutoPreco" type="number" step="0.01" min="0" name="price" required class="{{ $inputClass }}">
                </div>
                <div>
                    <label class="text-sm font-semibold text-zinc-700">Quantidade <span class="text-red-500">*</span></label>
                    <input id="editarProdutoQuantidade" type="number" min="0" name="quantity" required class="{{ $inputClass }}">
                </div>
            </div>
            <div class="flex justify-center gap-3 pt-3">
                <button type="button" onclick="fecharModalEditarProduto()" class="inline-flex items-center justify-center rounded-2xl border border-zinc-300 bg-white px-5 py-3 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700 transition hover:bg-zinc-100">Cancelar</button>
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
                Tem certeza que deseja excluir este produto? Esta ação não pode ser desfeita.
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
let productIdToDelete = null;
const produtos = @json($produtosJs);

function abrirModalNovoProduto() {
    document.getElementById('modalNovoProduto').classList.remove('hidden', 'pointer-events-none');
}

function fecharModalNovoProduto() {
    document.getElementById('modalNovoProduto').classList.add('hidden', 'pointer-events-none');
}

function abrirModalEditarProduto(id) {
    const produto = produtos[id];
    if (!produto) return;

    const form = document.getElementById('formEditarProduto');
    form.action = `/admin/products/${id}`;
    document.getElementById('editarProdutoNome').value = produto.name ?? '';
    document.getElementById('editarProdutoDescricao').value = produto.description ?? '';
    document.getElementById('editarProdutoPreco').value = Number(produto.price ?? 0).toFixed(2);
    document.getElementById('editarProdutoQuantidade').value = produto.quantity ?? 0;

    document.getElementById('modalEditarProduto').classList.remove('hidden', 'pointer-events-none');
}

function fecharModalEditarProduto() {
    document.getElementById('modalEditarProduto').classList.add('hidden', 'pointer-events-none');
}

function confirmDelete(id) {
    productIdToDelete = id;
    document.getElementById('confirmModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('confirmModal').classList.add('hidden');
    productIdToDelete = null;
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (productIdToDelete) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/products/${productIdToDelete}`;

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

document.getElementById('modalNovoProduto').addEventListener('click', function(e) {
    if (e.target === this) fecharModalNovoProduto();
});

document.getElementById('modalEditarProduto').addEventListener('click', function(e) {
    if (e.target === this) fecharModalEditarProduto();
});
</script>
@endsection
