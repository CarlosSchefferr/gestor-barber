const dataNode = document.getElementById('products-page-data');
const pageData = dataNode ? JSON.parse(dataNode.textContent || '{}') : {};
const products = pageData.products || {};
const comboCatalog = pageData.comboCatalog || [];
const productBaseUrl = pageData.routes?.productBase || '/admin/products';

const currency = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });

function openModal(id) {
    document.getElementById(id)?.classList.remove('hidden');
}

function closeModal(id) {
    document.getElementById(id)?.classList.add('hidden');
}

function showNotification(message, type = 'success') {
    const container = document.createElement('div');
    const isError = type === 'error';
    container.className = 'fixed right-4 top-4 z-[100] max-w-md';
    container.innerHTML = `
        <div class="rounded-2xl border ${isError ? 'border-red-200 bg-red-50' : 'border-emerald-200 bg-emerald-50'} px-4 py-3 shadow-lg">
            <p class="text-sm font-medium ${isError ? 'text-red-700' : 'text-emerald-700'}">${message}</p>
        </div>
    `;
    document.body.appendChild(container);

    setTimeout(() => container.remove(), 3000);
}

function field(form, name) {
    return form.querySelector(`[name="${name}"]`);
}

function fieldValue(form, name) {
    return field(form, name)?.value || '';
}

function setField(form, name, value) {
    const field = form.querySelector(`[name="${name}"]`);
    if (field) {
        field.value = value ?? '';
        field.dispatchEvent(new Event('input', { bubbles: true }));
        field.dispatchEvent(new Event('change', { bubbles: true }));
    }
}

function catalogProduct(id) {
    return comboCatalog.find((product) => Number(product.id) === Number(id));
}

function modalState(form) {
    if (!form._productState) {
        form._productState = { comboProducts: [] };
    }

    return form._productState;
}

function renderCombo(form) {
    const state = modalState(form);
    const list = form.querySelector('[data-combo-list]');
    const priceInput = form.querySelector('[name="price"]');
    const commissionInput = form.querySelector('[name="commission_percentage"]');
    const comboValue = form.querySelector('[data-combo-value]');
    const comboCommission = form.querySelector('[data-combo-commission]');

    if (!list) {
        return;
    }

    list.innerHTML = '';

    if (!state.comboProducts.length) {
        list.innerHTML = '<tr><td colspan="4" class="px-5 py-8 text-center text-sm text-zinc-500">Nenhum produto adicionado ao combo.</td></tr>';
    } else {
        state.comboProducts.forEach((id) => {
            const product = catalogProduct(id);
            if (!product) {
                return;
            }

            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="px-5 py-4 text-sm font-semibold text-zinc-900">${product.name}</td>
                <td class="px-5 py-4 text-sm text-emerald-600">${currency.format(Number(product.price || 0))}</td>
                <td class="px-5 py-4 text-sm text-zinc-700">${Number(product.commission_percentage || 0).toFixed(2)}%</td>
                <td class="px-5 py-4 text-right">
                    <button type="button" class="inline-flex items-center justify-center rounded-xl border border-zinc-200 bg-white px-3 py-2 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700 transition hover:bg-red-50 hover:text-red-600" data-remove-combo="${product.id}">Remover</button>
                </td>
            `;
            list.appendChild(row);
        });
    }

    const selectedProducts = state.comboProducts.map(catalogProduct).filter(Boolean);
    const total = selectedProducts.reduce((sum, product) => sum + Number(product.price || 0), 0);
    const averageCommission = selectedProducts.length
        ? selectedProducts.reduce((sum, product) => sum + Number(product.commission_percentage || 0), 0) / selectedProducts.length
        : 0;

    if (comboValue && (!comboValue.dataset.edited || comboValue.value === '0')) {
        comboValue.value = total.toFixed(2);
    }

    if (comboCommission && (!comboCommission.dataset.edited || comboCommission.value === '0')) {
        comboCommission.value = averageCommission.toFixed(2);
    }

    if (priceInput && comboValue) {
        priceInput.value = Number(comboValue.value || 0).toFixed(2);
    }

    if (commissionInput && comboCommission) {
        commissionInput.value = Number(comboCommission.value || 0).toFixed(2);
    }
}

function syncRegistration(form) {
    const type = fieldValue(form, 'registration_type');
    const comboArea = form.querySelector('[data-combo-area]');
    comboArea?.classList.toggle('hidden', type !== 'combo');

    if (type === 'combo') {
        renderCombo(form);
    }
}

function syncUnitFields(form) {
    const select = form.querySelector('[data-unit-select]');
    const value = fieldValue(form, 'product_unit_id');
    const fields = form.querySelector('[data-new-unit-fields]');
    const suffix = form.querySelector('[data-unit-suffix]');
    const selectedText = select?.querySelector('.cs-trigger-text')?.textContent || 'un';

    fields?.classList.toggle('hidden', value !== 'new');

    if (suffix) {
        const match = selectedText.match(/\(([^)]+)\)/);
        suffix.textContent = value === 'new' ? 'nova' : (match?.[1] || selectedText.split(' ')[0] || 'un');
    }
}

function setProductTab(key) {
    const [mode] = key.split(':');

    document.querySelectorAll(`[data-product-tab^="${mode}:"]`).forEach((button) => {
        const active = button.dataset.productTab === key;
        button.classList.toggle('border-barber-500', active);
        button.classList.toggle('bg-barber-50', active);
        button.classList.toggle('text-barber-600', active);
        button.classList.toggle('font-semibold', active);
        button.classList.toggle('border-zinc-300', !active);
        button.classList.toggle('bg-white', !active);
        button.classList.toggle('text-zinc-500', !active);
    });

    document.querySelectorAll(`[data-product-tab-panel^="${mode}:"]`).forEach((panel) => {
        panel.classList.toggle('hidden', panel.dataset.productTabPanel !== key);
    });
}

function prepareForm(form) {
    form.querySelectorAll('input[name="combo_products[]"]').forEach((input) => input.remove());

    if (fieldValue(form, 'registration_type') === 'combo') {
        modalState(form).comboProducts.forEach((id) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'combo_products[]';
            input.value = id;
            form.appendChild(input);
        });
    }

    if (fieldValue(form, 'product_unit_id') === 'new') {
        field(form, 'product_unit_id').disabled = true;
    }
}

function bindProductForm(form) {
    if (!form || form.dataset.bound === '1') {
        return;
    }

    form.dataset.bound = '1';
    syncRegistration(form);
    syncUnitFields(form);
    renderFormMovements(form, { stock_movements: [], unit: 'un' }, 'in');
    renderFormPrices(form, { price_histories: [] }, 'sale');

    field(form, 'registration_type')?.addEventListener('change', () => syncRegistration(form));
    field(form, 'product_unit_id')?.addEventListener('change', () => syncUnitFields(form));

    form.querySelector('[data-add-combo-product]')?.addEventListener('click', () => {
        const id = Number(fieldValue(form, `_combo_product_${form.id === 'formEditarProduto' ? 'edit' : 'create'}`) || 0);

        if (!id || modalState(form).comboProducts.includes(id)) {
            return;
        }

        modalState(form).comboProducts.push(id);
        setField(form, `_combo_product_${form.id === 'formEditarProduto' ? 'edit' : 'create'}`, '');
        renderCombo(form);
    });

    form.querySelector('[data-combo-list]')?.addEventListener('click', (event) => {
        const button = event.target.closest('[data-remove-combo]');
        if (!button) {
            return;
        }

        modalState(form).comboProducts = modalState(form).comboProducts.filter((id) => Number(id) !== Number(button.dataset.removeCombo));
        renderCombo(form);
    });

    form.querySelector('[data-combo-value]')?.addEventListener('input', (event) => {
        event.target.dataset.edited = '1';
        setField(form, 'price', event.target.value);
    });

    form.querySelector('[data-combo-commission]')?.addEventListener('input', (event) => {
        event.target.dataset.edited = '1';
        setField(form, 'commission_percentage', event.target.value);
    });

    form.addEventListener('submit', () => prepareForm(form));
}

function resetCreateForm() {
    const form = document.getElementById('formNovoProduto');
    form?.reset();
    if (form) {
        field(form, 'product_unit_id').disabled = false;
        modalState(form).comboProducts = [];
        syncRegistration(form);
        syncUnitFields(form);
    }
}

function renderFormMovements(form, product, filter = 'in') {
    const list = form.querySelector('[data-form-movements-list]');
    const unit = product.unit || 'un';
    const rows = (product.stock_movements || []).filter((movement) => movement.type === filter);

    if (!list) {
        return;
    }

    list.innerHTML = rows.length ? rows.map((movement) => `
        <tr>
            <td class="px-5 py-4 text-sm text-zinc-700">${movement.date || '-'}</td>
            <td class="px-5 py-4 text-sm font-semibold ${movement.type === 'in' ? 'text-emerald-600' : 'text-red-600'}">${movement.type === 'in' ? '+' : '-'}${movement.quantity} ${unit}</td>
            <td class="px-5 py-4 text-sm text-zinc-700">${movement.origin || '-'}</td>
        </tr>
    `).join('') : '<tr><td colspan="3" class="px-5 py-8 text-center text-sm text-zinc-500">Nenhuma movimentação encontrada.</td></tr>';
}

function renderFormPrices(form, product, filter = 'sale') {
    const list = form.querySelector('[data-form-prices-list]');
    const rows = (product.price_histories || []).filter((history) => history.type === filter);

    if (!list) {
        return;
    }

    list.innerHTML = rows.length ? rows.map((history) => `
        <tr>
            <td class="px-5 py-4 text-sm font-semibold text-emerald-600">${currency.format(Number(history.value || 0))}</td>
            <td class="px-5 py-4 text-sm text-zinc-700">${history.date || '-'}</td>
        </tr>
    `).join('') : '<tr><td colspan="2" class="px-5 py-8 text-center text-sm text-zinc-500">Nenhum histórico encontrado.</td></tr>';
}

async function openEditProduct(id) {
    const product = products[id];
    const form = document.getElementById('formEditarProduto');

    if (!product || !form) {
        return;
    }

    form.action = `${productBaseUrl}/${id}`;
    field(form, 'product_unit_id').disabled = false;
    form.reset();
    modalState(form).comboProducts = (product.combo_products || []).map(Number);

    setField(form, 'description', product.description);
    setField(form, 'brand', product.brand);
    setField(form, 'product_unit_id', product.product_unit_id);
    setField(form, 'registration_type', product.registration_type || 'product');
    setField(form, 'usage_type', product.usage_type || 'barbershop');
    setField(form, 'price', Number(product.price || 0).toFixed(2));
    setField(form, 'commission_percentage', Number(product.commission_percentage || 0).toFixed(2));
    setField(form, 'minimum_stock', product.minimum_stock || 0);
    setField(form, 'barcode', product.barcode);

    const comboValue = form.querySelector('[data-combo-value]');
    const comboCommission = form.querySelector('[data-combo-commission]');
    if (comboValue) {
        comboValue.value = Number(product.price || 0).toFixed(2);
        comboValue.dataset.edited = '1';
    }
    if (comboCommission) {
        comboCommission.value = Number(product.commission_percentage || 0).toFixed(2);
        comboCommission.dataset.edited = '1';
    }

    syncRegistration(form);
    syncUnitFields(form);
    setProductTab('edit:dados');
    openModal('modalEditarProduto');

    const response = await fetch(`${productBaseUrl}/${id}`, { headers: { Accept: 'application/json' } });
    const productDetails = await response.json();

    form.querySelectorAll('[data-form-movement-filter]').forEach((button) => {
        button.onclick = () => renderFormMovements(form, productDetails, button.dataset.formMovementFilter);
    });
    form.querySelectorAll('[data-form-price-filter]').forEach((button) => {
        button.onclick = () => renderFormPrices(form, productDetails, button.dataset.formPriceFilter);
    });

    renderFormMovements(form, productDetails, 'in');
    renderFormPrices(form, productDetails, 'sale');
}

function selectedStockProducts() {
    return Array.from(document.querySelectorAll('[data-stock-product]:checked')).map((input) => input.value);
}

function openStockAdjust() {
    const selected = selectedStockProducts();

    if (!selected.length) {
        showNotification('Selecione pelo menos um produto para ajustar o estoque.', 'error');
        return;
    }

    const holder = document.querySelector('[data-stock-selected-inputs]');
    if (holder) {
        holder.innerHTML = '';
        selected.forEach((id) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'product_ids[]';
            input.value = id;
            holder.appendChild(input);
        });
    }

    openModal('modalAjusteEstoque');
}

function renderMovements(product, filter = 'in') {
    const list = document.querySelector('[data-movements-list]');
    const unit = product.unit || 'un';
    const rows = (product.stock_movements || []).filter((movement) => movement.type === filter);

    if (!list) {
        return;
    }

    list.innerHTML = rows.length ? rows.map((movement) => `
        <tr>
            <td class="px-5 py-4 text-sm text-zinc-700">${movement.date || '-'}</td>
            <td class="px-5 py-4 text-sm font-semibold ${movement.type === 'in' ? 'text-emerald-600' : 'text-red-600'}">${movement.type === 'in' ? '+' : '-'}${movement.quantity} ${unit}</td>
            <td class="px-5 py-4 text-sm text-zinc-700">${movement.origin || '-'}</td>
        </tr>
    `).join('') : '<tr><td colspan="3" class="px-5 py-8 text-center text-sm text-zinc-500">Nenhuma movimentação encontrada.</td></tr>';
}

function renderPrices(product, filter = 'sale') {
    const list = document.querySelector('[data-prices-list]');
    const rows = (product.price_histories || []).filter((history) => history.type === filter);

    if (!list) {
        return;
    }

    list.innerHTML = rows.length ? rows.map((history) => `
        <tr>
            <td class="px-5 py-4 text-sm font-semibold text-emerald-600">${currency.format(Number(history.value || 0))}</td>
            <td class="px-5 py-4 text-sm text-zinc-700">${history.date || '-'}</td>
        </tr>
    `).join('') : '<tr><td colspan="2" class="px-5 py-8 text-center text-sm text-zinc-500">Nenhum histórico encontrado.</td></tr>';
}

async function openDetails(id) {
    const response = await fetch(`${productBaseUrl}/${id}`, { headers: { Accept: 'application/json' } });
    const product = await response.json();

    document.querySelector('[data-details-title]').textContent = product.name || 'Produto';
    document.querySelectorAll('[data-movement-filter]').forEach((button) => {
        button.onclick = () => renderMovements(product, button.dataset.movementFilter);
    });
    document.querySelectorAll('[data-price-filter]').forEach((button) => {
        button.onclick = () => renderPrices(product, button.dataset.priceFilter);
    });

    renderMovements(product, 'in');
    renderPrices(product, 'sale');
    openModal('modalDetalhesProduto');
}

document.querySelectorAll('form[id^="form"][id$="Produto"]').forEach(bindProductForm);

document.querySelector('[data-open-product-create]')?.addEventListener('click', () => {
    resetCreateForm();
    setProductTab('create:dados');
    openModal('modalNovoProduto');
});

document.querySelector('[data-open-stock-adjust]')?.addEventListener('click', openStockAdjust);
document.querySelector('[data-open-unit-modal]')?.addEventListener('click', () => openModal('modalUnidade'));

document.querySelectorAll('[data-edit-product]').forEach((button) => {
    button.addEventListener('click', () => openEditProduct(button.dataset.editProduct));
});

document.querySelectorAll('[data-product-details]').forEach((button) => {
    button.addEventListener('click', () => openDetails(button.dataset.productDetails));
});

document.querySelectorAll('[data-close-modal]').forEach((button) => {
    button.addEventListener('click', () => closeModal(button.dataset.closeModal));
});

document.querySelectorAll('[data-product-tab]').forEach((button) => {
    button.addEventListener('click', () => setProductTab(button.dataset.productTab));
});

document.querySelector('[data-stock-check-all]')?.addEventListener('change', (event) => {
    document.querySelectorAll('[data-stock-product]').forEach((input) => {
        input.checked = event.target.checked;
    });
    updateBulkDropup();
});

function fillSelectedInputs(container, selected) {
    if (!container) {
        return;
    }

    container.innerHTML = '';
    selected.forEach((id) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'product_ids[]';
        input.value = id;
        container.appendChild(input);
    });
}

function updateBulkDropup() {
    const selected = selectedStockProducts();
    const dropup = document.querySelector('[data-bulk-dropup]');
    const count = document.querySelector('[data-selected-count]');

    dropup?.classList.toggle('hidden', selected.length === 0);
    if (count) {
        count.textContent = selected.length;
    }

    fillSelectedInputs(document.querySelector('[data-bulk-selected-inputs]'), selected);
}

document.querySelectorAll('[data-stock-product]').forEach((input) => {
    input.addEventListener('change', updateBulkDropup);
});

document.querySelector('[data-bulk-adjust]')?.addEventListener('click', openStockAdjust);

document.querySelector('[data-clear-selection]')?.addEventListener('click', () => {
    document.querySelectorAll('[data-stock-product], [data-stock-check-all]').forEach((input) => {
        input.checked = false;
    });
    updateBulkDropup();
});

document.querySelectorAll('[data-image-button]').forEach((button) => {
    button.addEventListener('click', () => button.closest('form')?.querySelector('[data-image-input]')?.click());
});

document.querySelectorAll('[data-image-input]').forEach((input) => {
    input.addEventListener('change', () => {
        const file = input.files?.[0];
        const container = input.closest('div');
        const preview = container?.querySelector('[data-image-preview]');
        const label = container?.querySelector('[data-image-name]');

        if (!file || !preview) {
            return;
        }

        const reader = new FileReader();
        reader.onload = (event) => {
            preview.innerHTML = `<img src="${event.target.result}" alt="Preview" class="h-full w-full object-cover">`;
        };
        reader.readAsDataURL(file);

        if (label) {
            label.textContent = file.name;
        }
    });
});

document.querySelectorAll('[data-auto-toast]').forEach((toast) => {
    setTimeout(() => toast.remove(), 3000);
});

['modalNovoProduto', 'modalEditarProduto', 'modalAjusteEstoque', 'modalUnidade', 'modalDetalhesProduto'].forEach((id) => {
    document.getElementById(id)?.addEventListener('click', (event) => {
        if (event.target.id === id) {
            closeModal(id);
        }
    });
});
