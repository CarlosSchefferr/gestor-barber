@php
    $isEdit = $mode === 'edit';
    $modalId = $isEdit ? 'modalEditarProduto' : 'modalNovoProduto';
    $formId = $isEdit ? 'formEditarProduto' : 'formNovoProduto';
    $title = $isEdit ? 'Editar produto' : 'Novo produto';
    $subtitle = $isEdit ? 'Atualize os dados do produto' : 'Preencha os dados para cadastrar o produto';
@endphp

<div id="{{ $modalId }}" class="fixed inset-0 z-50 hidden h-full w-full overflow-y-auto bg-zinc-900/60 p-4 backdrop-blur-[2px]">
    <div class="relative top-6 mx-auto mb-10 w-full max-w-5xl rounded-3xl border border-zinc-200 bg-white shadow-xl">
        <div class="p-6 pb-5 sm:p-8 sm:pb-5">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-barber-500">{{ $isEdit ? 'Edição' : 'Cadastro' }}</p>
            <h3 class="mt-2 text-2xl font-bold text-zinc-900">{{ $title }}</h3>
            <p class="mt-1 text-sm text-zinc-500">{{ $subtitle }}</p>
        </div>

        <div class="flex gap-2 border-b border-zinc-200 px-6 pb-6 sm:px-8">
            <button type="button" data-product-tab="{{ $mode }}:dados" class="flex-1 rounded-xl border border-barber-500 bg-barber-50 py-2.5 text-sm font-semibold text-barber-600 transition">Dados do produto</button>
            <button type="button" data-product-tab="{{ $mode }}:movimentacoes" class="flex-1 rounded-xl border border-zinc-300 bg-white py-2.5 text-sm font-medium text-zinc-500 transition">Movimentações do estoque</button>
            <button type="button" data-product-tab="{{ $mode }}:valores" class="flex-1 rounded-xl border border-zinc-300 bg-white py-2.5 text-sm font-medium text-zinc-500 transition">Histórico de valores</button>
        </div>

        <form id="{{ $formId }}" action="{{ $isEdit ? '#' : route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="px-6 py-6 sm:px-8">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div data-product-tab-panel="{{ $mode }}:dados" class="space-y-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="text-sm font-semibold text-zinc-700">Descrição do produto <span class="text-red-500">*</span></label>
                        <input type="text" name="description" required class="{{ $inputClass }}" placeholder="Ex: Pomada modeladora">
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Marca <span class="text-red-500">*</span></label>
                        <input type="text" name="brand" required class="{{ $inputClass }}" placeholder="Ex: Don Alcides">
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Unidade de medida <span class="text-red-500">*</span></label>
                        <x-custom-select
                            name="product_unit_id"
                            :options="['' => 'Selecione'] + $activeUnits->mapWithKeys(fn ($unit) => [$unit->id => $unit->name . ($unit->abbreviation ? ' (' . $unit->abbreviation . ')' : '')])->toArray() + ['new' => 'Criar nova unidade']"
                            value=""
                            placeholder="Selecione"
                            required
                            data-unit-select
                        />
                    </div>
                    <div data-new-unit-fields class="hidden md:col-span-2">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label class="text-sm font-semibold text-zinc-700">Nova unidade</label>
                                <input type="text" name="new_unit_name" class="{{ $inputClass }}" placeholder="Ex: Pacote">
                            </div>
                            <div>
                                <label class="text-sm font-semibold text-zinc-700">Abreviação</label>
                                <input type="text" name="new_unit_abbreviation" class="{{ $inputClass }}" placeholder="Ex: pct">
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Tipo de cadastro <span class="text-red-500">*</span></label>
                        <x-custom-select
                            name="registration_type"
                            :options="['product' => 'Produto', 'combo' => 'Combo de produtos']"
                            value="product"
                            placeholder="Selecione"
                            required
                            data-registration-type
                        />
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Tipo de uso <span class="text-red-500">*</span></label>
                        <x-custom-select
                            name="usage_type"
                            :options="['barbershop' => 'Uso da barbearia', 'sale' => 'Venda', 'both' => 'Para uso e venda']"
                            value="barbershop"
                            placeholder="Selecione"
                            required
                        />
                    </div>
                </div>

                <div data-combo-area class="hidden space-y-4 rounded-3xl border border-zinc-200 bg-zinc-50 p-5">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="rounded-3xl border border-zinc-200 bg-white p-5 shadow-sm">
                            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Valor do combo</p>
                            <input type="number" name="combo_value_preview" step="0.01" min="0" data-combo-value class="{{ $inputClass }}" value="0">
                        </div>
                        <div class="rounded-3xl border border-zinc-200 bg-white p-5 shadow-sm">
                            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Comissão do combo</p>
                            <input type="number" name="combo_commission_preview" step="0.01" min="0" max="100" data-combo-commission class="{{ $inputClass }}" value="0">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-[1fr_auto]">
                        <x-custom-select
                            name="_combo_product_{{ $mode }}"
                            :options="['' => 'Selecione um produto'] + $comboCatalog->pluck('name', 'id')->toArray()"
                            value=""
                            placeholder="Selecione um produto"
                            data-combo-select
                        />
                        <button type="button" data-add-combo-product class="inline-flex items-center justify-center rounded-2xl bg-zinc-900 px-5 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white transition hover:bg-zinc-800">Adicionar</button>
                    </div>
                    <div class="overflow-x-auto rounded-2xl border border-zinc-200 bg-white">
                        <table class="min-w-full">
                            <thead class="bg-zinc-50">
                                <tr>
                                    <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Produto</th>
                                    <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Valor</th>
                                    <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-zinc-500">Comissão</th>
                                    <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wide text-zinc-500">Ação</th>
                                </tr>
                            </thead>
                            <tbody data-combo-list class="divide-y divide-zinc-100"></tbody>
                        </table>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Valor <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="pointer-events-none absolute left-4 top-1/2 mt-1 -translate-y-1/2 text-sm font-semibold text-zinc-500">R$</span>
                            <input type="number" step="0.01" min="0" name="price" required class="{{ $inputClass }} pl-11" value="0">
                        </div>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Comissão <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="number" step="0.01" min="0" max="100" name="commission_percentage" required class="{{ $inputClass }} pr-10" value="0">
                            <span class="pointer-events-none absolute right-4 top-1/2 mt-1 -translate-y-1/2 text-sm font-semibold text-zinc-500">%</span>
                        </div>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Estoque mínimo <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="number" min="0" name="minimum_stock" required class="{{ $inputClass }} pr-16" value="0">
                            <span data-unit-suffix class="pointer-events-none absolute right-4 top-1/2 mt-1 -translate-y-1/2 text-sm font-semibold text-zinc-500">un</span>
                        </div>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-zinc-700">Código de barras</label>
                        <input type="text" name="barcode" class="{{ $inputClass }}" placeholder="Opcional">
                    </div>
                    <div class="md:col-span-2">
                        <div class="flex flex-col items-center rounded-2xl border border-zinc-200 bg-zinc-50 p-5">
                            <div data-image-preview class="mb-3 flex h-24 w-24 items-center justify-center overflow-hidden rounded-lg border-2 border-white bg-zinc-200 shadow-lg">
                                <i class="bi bi-image text-3xl text-zinc-400" aria-hidden="true"></i>
                            </div>
                            <label class="mb-2 text-sm font-semibold text-zinc-700">Imagem do produto</label>
                            <input type="file" name="image" accept="image/png,image/jpeg,image/webp" class="hidden" data-image-input>
                            <button type="button" data-image-button class="inline-flex items-center gap-2 rounded-xl border border-zinc-300 bg-white px-4 py-2 text-xs font-semibold text-zinc-700 transition hover:bg-zinc-100">
                                <i class="bi bi-image" aria-hidden="true"></i>
                                Escolher imagem
                            </button>
                            <p data-image-name class="mt-2 text-xs text-zinc-400">Opcional • JPG, PNG ou WebP até 2MB</p>
                        </div>
                    </div>
                </div>
            </div>

            <div data-product-tab-panel="{{ $mode }}:movimentacoes" class="hidden">
                <div class="rounded-3xl border border-zinc-200 bg-white shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-zinc-200 px-5 py-4">
                        <h4 class="text-lg font-bold text-zinc-900">Movimentações do estoque</h4>
                        <div class="flex gap-2">
                            <button type="button" data-form-movement-filter="in" class="rounded-2xl border border-zinc-300 px-3 py-2 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700">Entradas</button>
                            <button type="button" data-form-movement-filter="out" class="rounded-2xl border border-zinc-300 px-3 py-2 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700">Saídas</button>
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
                            <tbody data-form-movements-list class="divide-y divide-zinc-100 bg-white"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div data-product-tab-panel="{{ $mode }}:valores" class="hidden">
                <div class="rounded-3xl border border-zinc-200 bg-white shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-zinc-200 px-5 py-4">
                        <h4 class="text-lg font-bold text-zinc-900">Histórico de valores</h4>
                        <div class="flex gap-2">
                            <button type="button" data-form-price-filter="sale" class="rounded-2xl border border-zinc-300 px-3 py-2 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700">Preço de venda</button>
                            <button type="button" data-form-price-filter="purchase" class="rounded-2xl border border-zinc-300 px-3 py-2 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700">Preço de compra</button>
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
                            <tbody data-form-prices-list class="divide-y divide-zinc-100 bg-white"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-center gap-3 border-t border-zinc-200 pt-6">
                <button type="button" data-close-modal="{{ $modalId }}" class="inline-flex items-center justify-center rounded-2xl border border-zinc-300 bg-white px-5 py-3 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700 transition hover:bg-zinc-100">Cancelar</button>
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-barber-500 px-5 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-barber-600">Salvar produto</button>
            </div>
        </form>
    </div>
</div>
