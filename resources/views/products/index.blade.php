@extends('layouts.app')

@section('content')
@php
    $inputClass = 'mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20';
    $selectClass = $inputClass . ' appearance-none';
    $cardClass = 'rounded-3xl border border-zinc-200 bg-white/95 shadow-sm';
    $tabs = [
        ['key' => 'produtos', 'label' => 'Produtos', 'url' => route('admin.products.index')],
        ['key' => 'estoque', 'label' => 'Estoque', 'url' => route('admin.products.index', ['tab' => 'estoque'])],
        ['key' => 'unidades', 'label' => 'Unidades de medida', 'url' => route('admin.products.index', ['tab' => 'unidades'])],
    ];
    $produtosJs = $products->keyBy('id')->map(function ($product) {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'brand' => $product->brand,
            'product_unit_id' => $product->product_unit_id,
            'registration_type' => $product->registration_type,
            'usage_type' => $product->usage_type,
            'price' => $product->price,
            'commission_percentage' => $product->commission_percentage,
            'quantity' => $product->quantity,
            'minimum_stock' => $product->minimum_stock,
            'barcode' => $product->barcode,
            'combo_products' => $product->comboProducts->pluck('id')->values(),
        ];
    })->toArray();
    $comboCatalogJs = $comboCatalog->map(fn ($product) => [
        'id' => $product->id,
        'name' => $product->name,
        'price' => (float) $product->price,
        'commission_percentage' => (float) $product->commission_percentage,
    ])->values();
@endphp

<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <x-tabbed-card eyebrow="Estoque" title="Produtos" :tabs="$tabs" :active="$tab">
        <x-slot:actions>
            @if($tab === 'estoque')
                <button type="button" data-open-stock-adjust class="inline-flex items-center justify-center gap-2 rounded-2xl bg-barber-500 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-barber-600">
                    <i class="bi bi-plus-circle" aria-hidden="true"></i>
                    Ajustar estoque
                </button>
            @elseif($tab === 'unidades')
                <button type="button" data-open-unit-modal class="inline-flex items-center justify-center gap-2 rounded-2xl bg-barber-500 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-barber-600">
                    <i class="bi bi-plus-circle" aria-hidden="true"></i>
                    Nova unidade
                </button>
            @else
                <button type="button" data-open-product-create class="inline-flex items-center justify-center rounded-2xl bg-barber-500 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-barber-600">
                    Novo produto
                </button>
            @endif
        </x-slot:actions>
    </x-tabbed-card>

    @if(session('success'))
        <div class="fixed right-4 top-4 z-[100]" data-auto-toast>
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 shadow-lg">
                <p class="text-sm font-medium text-emerald-700">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="fixed right-4 top-4 z-[100] max-w-md rounded-2xl border border-red-200 bg-red-50 px-4 py-3 shadow-lg" data-auto-toast>
            <ul class="list-disc pl-5 text-sm text-red-700">
                @foreach($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($tab === 'produtos')
        <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="{{ $cardClass }} p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Estoque baixo</p>
                <p class="mt-3 text-3xl font-bold text-amber-600">{{ $estoqueBaixo ?? 0 }}</p>
                <p class="mt-1 text-sm text-zinc-500">Produtos abaixo do mínimo</p>
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
            <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
                <div>
                    <label class="text-sm font-semibold text-zinc-700">Nome do produto</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="{{ $inputClass }}" placeholder="Descrição do produto">
                </div>
                <div>
                    <label class="text-sm font-semibold text-zinc-700">Marca</label>
                    <x-custom-select name="brand" :options="['' => 'Todas as marcas'] + $brands->mapWithKeys(fn ($brand) => [$brand => $brand])->toArray()" :value="request('brand', '')" placeholder="Selecione a marca" />
                </div>
                <div>
                    <label class="text-sm font-semibold text-zinc-700">Tipo de cadastro</label>
                    <x-custom-select name="registration_type" :options="['' => 'Todos', 'product' => 'Produto', 'combo' => 'Combo de produtos']" :value="request('registration_type', '')" placeholder="Selecione o tipo" />
                </div>
                <div>
                    <label class="text-sm font-semibold text-zinc-700">Estoque</label>
                    <x-custom-select name="stock" :options="['' => 'Todos', 'out' => 'Sem estoque', 'low' => 'Baixo', 'ok' => 'Normal']" :value="request('stock', '')" placeholder="Selecione o estoque" />
                </div>
                <div>
                    <label class="text-sm font-semibold text-zinc-700">Ordenar por</label>
                    <x-custom-select name="sort" :options="['name' => 'Nome', 'brand' => 'Marca', 'price' => 'Preço', 'quantity' => 'Estoque']" :value="request('sort', 'name')" placeholder="Selecione a ordenação" />
                </div>
                <div class="md:col-span-2 xl:col-span-5 flex flex-wrap items-center justify-center gap-3 pt-2">
                    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-barber-500 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-barber-600">Aplicar</button>
                    <a href="{{ route('admin.products.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-zinc-300 bg-white px-6 py-3 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-100">Limpar</a>
                </div>
            </form>
        </div>

        <div class="{{ $cardClass }} overflow-hidden">
            <div class="border-b border-zinc-200 px-6 py-4">
                <h3 class="text-lg font-bold text-zinc-900">Lista de produtos</h3>
                <p class="mt-1 text-sm text-zinc-500">Produtos e combos cadastrados no estoque</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-zinc-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Produto</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Marca</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Preço</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Estoque</th>
                            <th class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wide text-zinc-500">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 bg-white">
                        @forelse($products as $product)
                            <tr class="transition hover:bg-zinc-50">
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span class="inline-flex rounded-full {{ $product->active ? 'bg-emerald-100 text-emerald-700' : 'bg-zinc-100 text-zinc-700' }} px-2.5 py-1 text-xs font-semibold">
                                        {{ $product->active ? 'Ativo' : 'Desativo' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-full bg-barber-100">
                                            @if($product->image_path)
                                                <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                                            @else
                                                <i class="bi bi-box-seam text-lg text-barber-600" aria-hidden="true"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="text-sm font-semibold text-zinc-900">{{ $product->name }}</div>
                                            <div class="mt-0.5 text-xs text-zinc-500">{{ $product->registration_type === 'combo' ? 'Combo de produtos' : 'Produto' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-zinc-700">{{ $product->brand }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-emerald-600">R$ {{ number_format($product->price ?? 0, 2, ',', '.') }}</td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    @php($unit = $product->unit?->abbreviation ?: $product->unit?->name ?: 'un')
                                    <span class="inline-flex rounded-full {{ $product->quantity <= 0 ? 'bg-red-100 text-red-700' : ($product->quantity <= $product->minimum_stock ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }} px-2.5 py-1 text-xs font-semibold">
                                        {{ $product->quantity }} {{ $unit }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button" data-edit-product="{{ $product->id }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-600 transition hover:bg-zinc-50 hover:text-barber-600" title="Editar">
                                            <i class="bi bi-pencil" aria-hidden="true"></i>
                                        </button>
                                        <form method="POST" action="{{ route('admin.products.destroy', $product) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-600 transition hover:bg-red-50 hover:text-red-600" title="Desativar">
                                                <i class="bi bi-slash-circle" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100">
                                        <i class="bi bi-box-seam text-xl text-zinc-400" aria-hidden="true"></i>
                                    </div>
                                    <h3 class="text-sm font-bold text-zinc-900">Nenhum produto encontrado</h3>
                                    <p class="mt-1 text-sm text-zinc-500">Comece cadastrando um novo produto.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($products->hasPages())
                <div class="border-t border-zinc-200 bg-white px-6 py-4">{{ $products->links() }}</div>
            @endif
        </div>
    @elseif($tab === 'estoque')
        <div class="{{ $cardClass }} overflow-hidden">
            <div class="border-b border-zinc-200 px-6 py-4">
                <h3 class="text-lg font-bold text-zinc-900">Estoque</h3>
                <p class="mt-1 text-sm text-zinc-500">Selecione produtos e registre ajustes manuais com motivo obrigatório</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-zinc-50">
                        <tr>
                            <th class="w-14 px-6 py-3 text-left"><input type="checkbox" data-stock-check-all class="h-5 w-5 rounded-md border-zinc-300 text-barber-500 focus:ring-barber-500"></th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Código</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Produto</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Estoque</th>
                            <th class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wide text-zinc-500">Ação</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 bg-white">
                        @foreach($stockProducts as $product)
                            <tr class="transition hover:bg-zinc-50">
                                <td class="px-6 py-4"><input type="checkbox" data-stock-product value="{{ $product->id }}" class="h-5 w-5 rounded-md border-zinc-300 text-barber-500 focus:ring-barber-500"></td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-zinc-700">#{{ $product->id }}</td>
                                <td class="px-6 py-4 text-sm font-semibold text-zinc-900">{{ $product->name }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-700">{{ $product->quantity }} {{ $product->unit?->abbreviation ?: $product->unit?->name }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-center">
                                    <button type="button" data-product-details="{{ $product->id }}" class="inline-flex items-center justify-center rounded-2xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-100">Detalhes</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($stockProducts->hasPages())
                <div class="border-t border-zinc-200 bg-white px-6 py-4">{{ $stockProducts->links() }}</div>
            @endif
        </div>
    @else
        <div class="{{ $cardClass }} overflow-hidden">
            <div class="border-b border-zinc-200 px-6 py-4">
                <h3 class="text-lg font-bold text-zinc-900">Unidades de medida</h3>
                <p class="mt-1 text-sm text-zinc-500">Unidades disponíveis para cadastro de produtos</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-zinc-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Unidade</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Abreviação</th>
                            <th class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wide text-zinc-500">Ação</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 bg-white">
                        @foreach($units as $unit)
                            <tr class="transition hover:bg-zinc-50">
                                <td class="px-6 py-4"><span class="inline-flex rounded-full {{ $unit->active ? 'bg-emerald-100 text-emerald-700' : 'bg-zinc-100 text-zinc-700' }} px-2.5 py-1 text-xs font-semibold">{{ $unit->active ? 'Ativo' : 'Desativo' }}</span></td>
                                <td class="px-6 py-4 text-sm font-semibold text-zinc-900">{{ $unit->name }}</td>
                                <td class="px-6 py-4 text-sm text-zinc-600">{{ $unit->abbreviation ?: '-' }}</td>
                                <td class="px-6 py-4 text-center">
                                    <form method="POST" action="{{ route('admin.products.units.toggle', $unit) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="inline-flex items-center justify-center rounded-2xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-100">{{ $unit->active ? 'Desativar' : 'Ativar' }}</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

@include('products.partials.product-modal', ['mode' => 'create', 'inputClass' => $inputClass, 'selectClass' => $selectClass, 'activeUnits' => $activeUnits, 'comboCatalog' => $comboCatalog])
@include('products.partials.product-modal', ['mode' => 'edit', 'inputClass' => $inputClass, 'selectClass' => $selectClass, 'activeUnits' => $activeUnits, 'comboCatalog' => $comboCatalog])

<div id="modalAjusteEstoque" class="fixed inset-0 z-50 hidden h-full w-full overflow-y-auto bg-zinc-900/60 p-4 backdrop-blur-[2px]">
    <div class="relative top-16 mx-auto w-full max-w-xl rounded-3xl border border-zinc-200 bg-white p-6 shadow-xl sm:p-8">
        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-barber-500">Estoque</p>
        <h3 class="mt-2 text-2xl font-bold text-zinc-900">Ajustar estoque</h3>
        <form action="{{ route('admin.products.adjust-stock') }}" method="POST" class="mt-6 space-y-4">
            @csrf
            <div data-stock-selected-inputs></div>
            <div>
                <label class="text-sm font-semibold text-zinc-700">Movimentação <span class="text-red-500">*</span></label>
                <x-custom-select
                    name="movement_type"
                    :options="['in' => 'Entrada', 'out' => 'Saída']"
                    value="in"
                    placeholder="Selecione"
                    required
                />
            </div>
            <div>
                <label class="text-sm font-semibold text-zinc-700">Quantidade <span class="text-red-500">*</span></label>
                <input type="number" name="quantity" min="1" required class="{{ $inputClass }}" value="1">
            </div>
            <div>
                <label class="text-sm font-semibold text-zinc-700">Motivo do ajuste <span class="text-red-500">*</span></label>
                <textarea name="reason" rows="3" required class="{{ $inputClass }} resize-none" placeholder="Informe o motivo"></textarea>
            </div>
            <div class="flex justify-center gap-3 pt-3">
                <button type="button" data-close-modal="modalAjusteEstoque" class="inline-flex items-center justify-center rounded-2xl border border-zinc-300 bg-white px-5 py-3 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700 transition hover:bg-zinc-100">Cancelar</button>
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-barber-500 px-5 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-barber-600">Salvar ajuste</button>
            </div>
        </form>
    </div>
</div>

<div id="modalUnidade" class="fixed inset-0 z-50 hidden h-full w-full overflow-y-auto bg-zinc-900/60 p-4 backdrop-blur-[2px]">
    <div class="relative top-16 mx-auto w-full max-w-xl rounded-3xl border border-zinc-200 bg-white p-6 shadow-xl sm:p-8">
        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-barber-500">Unidades</p>
        <h3 class="mt-2 text-2xl font-bold text-zinc-900">Nova unidade de medida</h3>
        <form action="{{ route('admin.products.units.store') }}" method="POST" class="mt-6 space-y-4">
            @csrf
            <div>
                <label class="text-sm font-semibold text-zinc-700">Nome <span class="text-red-500">*</span></label>
                <input type="text" name="name" required class="{{ $inputClass }}" placeholder="Ex: Caixa">
            </div>
            <div>
                <label class="text-sm font-semibold text-zinc-700">Abreviação</label>
                <input type="text" name="abbreviation" class="{{ $inputClass }}" placeholder="Ex: cx">
            </div>
            <div class="flex justify-center gap-3 pt-3">
                <button type="button" data-close-modal="modalUnidade" class="inline-flex items-center justify-center rounded-2xl border border-zinc-300 bg-white px-5 py-3 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700 transition hover:bg-zinc-100">Cancelar</button>
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-barber-500 px-5 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-barber-600">Salvar unidade</button>
            </div>
        </form>
    </div>
</div>

<div id="modalDetalhesProduto" class="fixed inset-0 z-50 hidden h-full w-full overflow-y-auto bg-zinc-900/60 p-4 backdrop-blur-[2px]">
    <div class="relative top-10 mx-auto mb-10 w-full max-w-5xl rounded-3xl border border-zinc-200 bg-white p-6 shadow-xl sm:p-8">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-barber-500">Histórico</p>
                <h3 data-details-title class="mt-2 text-2xl font-bold text-zinc-900">Produto</h3>
            </div>
            <button type="button" data-close-modal="modalDetalhesProduto" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-600 transition hover:bg-zinc-50">
                <i class="bi bi-x-lg" aria-hidden="true"></i>
            </button>
        </div>
        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            <div class="{{ $cardClass }} overflow-hidden">
                <div class="border-b border-zinc-200 px-5 py-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h4 class="text-lg font-bold text-zinc-900">Movimentações do estoque</h4>
                        <div class="flex gap-2">
                            <button type="button" data-movement-filter="in" class="rounded-2xl border border-zinc-300 px-3 py-2 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700">Entradas</button>
                            <button type="button" data-movement-filter="out" class="rounded-2xl border border-zinc-300 px-3 py-2 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700">Saídas</button>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-zinc-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Data</th>
                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Movimentação</th>
                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Origem</th>
                            </tr>
                        </thead>
                        <tbody data-movements-list class="divide-y divide-zinc-100 bg-white"></tbody>
                    </table>
                </div>
            </div>
            <div class="{{ $cardClass }} overflow-hidden">
                <div class="border-b border-zinc-200 px-5 py-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h4 class="text-lg font-bold text-zinc-900">Histórico de valores</h4>
                        <div class="flex gap-2">
                            <button type="button" data-price-filter="sale" class="rounded-2xl border border-zinc-300 px-3 py-2 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700">Preço de venda</button>
                            <button type="button" data-price-filter="purchase" class="rounded-2xl border border-zinc-300 px-3 py-2 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700">Preço de compra</button>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-zinc-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Valor</th>
                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Data da atualização</th>
                            </tr>
                        </thead>
                        <tbody data-prices-list class="divide-y divide-zinc-100 bg-white"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div data-bulk-dropup class="fixed inset-x-0 bottom-0 z-40 hidden border-t border-zinc-200 bg-white/95 px-4 py-4 shadow-[0_-18px_45px_rgba(15,23,42,0.12)] backdrop-blur sm:px-6 lg:px-8">
    <div class="mx-auto flex max-w-7xl flex-col items-center gap-4 text-center">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500">Ações em massa</p>
            <p class="mt-1 text-sm font-semibold text-zinc-900"><span data-selected-count>0</span> produto(s) selecionado(s)</p>
        </div>
        <div class="flex flex-wrap items-center justify-center gap-3">
            <button type="button" data-bulk-adjust class="inline-flex min-w-36 items-center justify-center rounded-2xl bg-barber-500 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-barber-600">
                Ajustar estoque
            </button>
            <form method="POST" action="{{ route('admin.products.bulk-action') }}" data-bulk-form class="contents">
                @csrf
                <div data-bulk-selected-inputs></div>
                <button type="submit" name="action" value="activate" class="inline-flex min-w-28 items-center justify-center rounded-2xl border border-zinc-300 bg-white px-5 py-3 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-100">
                    Ativar
                </button>
                <button type="submit" name="action" value="deactivate" class="inline-flex min-w-28 items-center justify-center rounded-2xl bg-red-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700">
                    Excluir
                </button>
            </form>
            <button type="button" data-clear-selection class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-zinc-300 bg-white text-zinc-600 transition hover:bg-zinc-100" title="Limpar seleção">
                <i class="bi bi-x-lg" aria-hidden="true"></i>
            </button>
        </div>
    </div>
</div>

<script type="application/json" id="products-page-data">
{!! json_encode([
    'products' => $produtosJs,
    'comboCatalog' => $comboCatalogJs,
    'routes' => [
        'productBase' => url('/admin/products'),
    ],
], JSON_UNESCAPED_UNICODE) !!}
</script>
@endsection

@push('scripts')
    @vite('resources/js/products.js')
@endpush
