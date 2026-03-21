import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

function initCustomSelects() {
	const selectors = document.querySelectorAll('.v2-main select:not([multiple]):not([size]):not([data-native-select])');

	selectors.forEach((select) => {
		if (select.dataset.v2Enhanced === '1') {
			return;
		}

		const allOptions = Array.from(select.options || []).map((option, index) => ({
			value: option.value,
			label: (option.textContent || '').trim(),
			disabled: option.disabled,
			index,
		}));

		if (!allOptions.length) {
			return;
		}

		select.dataset.v2Enhanced = '1';

		const originalName = select.getAttribute('name') || '';
		const isRequired = select.hasAttribute('required');
		const searchEnabled = allOptions.filter((opt) => !opt.disabled).length > 3;

		select.removeAttribute('name');
		select.removeAttribute('required');
		select.classList.add('v2-select-native');

		const wrapper = document.createElement('div');
		wrapper.className = 'v2-select';
		wrapper.dataset.required = isRequired ? '1' : '0';

		select.parentNode.insertBefore(wrapper, select);
		wrapper.appendChild(select);

		const hiddenInput = document.createElement('input');
		hiddenInput.type = 'hidden';
		hiddenInput.name = originalName;
		hiddenInput.value = select.value || '';
		wrapper.appendChild(hiddenInput);

		const trigger = document.createElement('button');
		trigger.type = 'button';
		trigger.className = 'v2-select-trigger';
		trigger.setAttribute('aria-haspopup', 'listbox');
		trigger.setAttribute('aria-expanded', 'false');
		wrapper.appendChild(trigger);

		const triggerLabel = document.createElement('span');
		triggerLabel.className = 'v2-select-trigger-label';
		trigger.appendChild(triggerLabel);

		const triggerIcon = document.createElement('span');
		triggerIcon.className = 'v2-select-trigger-icon';
		triggerIcon.innerHTML = '<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>';
		trigger.appendChild(triggerIcon);

		const panel = document.createElement('div');
		panel.className = 'v2-select-panel hidden';
		panel.setAttribute('role', 'listbox');
		wrapper.appendChild(panel);

		let searchInput = null;
		if (searchEnabled) {
			const searchWrap = document.createElement('div');
			searchWrap.className = 'v2-select-search-wrap';
			searchInput = document.createElement('input');
			searchInput.type = 'text';
			searchInput.className = 'v2-select-search';
			searchInput.placeholder = 'Pesquisar...';
			searchInput.setAttribute('aria-label', 'Pesquisar opcoes');
			searchWrap.appendChild(searchInput);
			panel.appendChild(searchWrap);
		}

		const optionsWrap = document.createElement('div');
		optionsWrap.className = 'v2-select-options';
		panel.appendChild(optionsWrap);

		let filteredOptions = [...allOptions];
		let highlightedIndex = -1;

		const selectedOption = () => {
			const selected = allOptions.find((option) => option.value === hiddenInput.value);
			if (selected) {
				return selected;
			}
			return allOptions[0] || { value: '', label: 'Selecionar' };
		};

		const updateTriggerLabel = () => {
			triggerLabel.textContent = selectedOption().label || 'Selecionar';
		};

		const closeDropdown = () => {
			panel.classList.add('hidden');
			trigger.classList.remove('is-open');
			trigger.setAttribute('aria-expanded', 'false');
		};

		const openDropdown = () => {
			document.querySelectorAll('.v2-select-panel:not(.hidden)').forEach((openPanel) => {
				if (openPanel !== panel) {
					openPanel.classList.add('hidden');
					openPanel.parentElement?.querySelector('.v2-select-trigger')?.classList.remove('is-open');
					openPanel.parentElement?.querySelector('.v2-select-trigger')?.setAttribute('aria-expanded', 'false');
				}
			});

			panel.classList.remove('hidden');
			trigger.classList.add('is-open');
			trigger.setAttribute('aria-expanded', 'true');

			if (searchInput) {
				searchInput.focus();
			}
		};

		const syncNativeSelect = (value) => {
			select.value = value;
			hiddenInput.value = value;
			select.dispatchEvent(new Event('change', { bubbles: true }));
			wrapper.classList.remove('is-invalid');
			updateTriggerLabel();
		};

		const renderOptions = (query = '') => {
			const needle = query.trim().toLowerCase();
			filteredOptions = allOptions.filter((option) => {
				if (!needle) {
					return true;
				}
				return option.label.toLowerCase().includes(needle);
			});

			highlightedIndex = filteredOptions.findIndex((option) => option.value === hiddenInput.value);
			optionsWrap.innerHTML = '';

			if (!filteredOptions.length) {
				const empty = document.createElement('div');
				empty.className = 'v2-select-empty';
				empty.textContent = 'Nenhum resultado encontrado';
				optionsWrap.appendChild(empty);
				return;
			}

			filteredOptions.forEach((option, position) => {
				const btn = document.createElement('button');
				btn.type = 'button';
				btn.className = 'v2-select-option';
				btn.textContent = option.label;
				btn.setAttribute('role', 'option');
				btn.dataset.value = option.value;

				if (option.disabled) {
					btn.disabled = true;
					btn.classList.add('is-disabled');
				}

				if (option.value === hiddenInput.value) {
					btn.classList.add('is-selected');
				}

				if (position === highlightedIndex) {
					btn.classList.add('is-highlighted');
				}

				btn.addEventListener('click', () => {
					if (option.disabled) {
						return;
					}
					syncNativeSelect(option.value);
					closeDropdown();
				});

				optionsWrap.appendChild(btn);
			});
		};

		const moveHighlight = (direction) => {
			if (!filteredOptions.length) {
				return;
			}

			let next = highlightedIndex;
			do {
				next = (next + direction + filteredOptions.length) % filteredOptions.length;
			} while (filteredOptions[next]?.disabled && next !== highlightedIndex);

			highlightedIndex = next;
			const allRendered = optionsWrap.querySelectorAll('.v2-select-option');
			allRendered.forEach((node, idx) => {
				node.classList.toggle('is-highlighted', idx === highlightedIndex);
			});

			const active = allRendered[highlightedIndex];
			if (active) {
				active.scrollIntoView({ block: 'nearest' });
			}
		};

		trigger.addEventListener('click', () => {
			if (panel.classList.contains('hidden')) {
				renderOptions(searchInput ? searchInput.value : '');
				openDropdown();
			} else {
				closeDropdown();
			}
		});

		trigger.addEventListener('keydown', (event) => {
			if (event.key === 'ArrowDown' || event.key === 'Enter' || event.key === ' ') {
				event.preventDefault();
				renderOptions(searchInput ? searchInput.value : '');
				openDropdown();
			}
		});

		panel.addEventListener('keydown', (event) => {
			if (event.key === 'Escape') {
				event.preventDefault();
				closeDropdown();
				trigger.focus();
				return;
			}

			if (event.key === 'ArrowDown') {
				event.preventDefault();
				moveHighlight(1);
				return;
			}

			if (event.key === 'ArrowUp') {
				event.preventDefault();
				moveHighlight(-1);
				return;
			}

			if (event.key === 'Enter') {
				event.preventDefault();
				const active = filteredOptions[highlightedIndex];
				if (active && !active.disabled) {
					syncNativeSelect(active.value);
					closeDropdown();
					trigger.focus();
				}
			}
		});

		if (searchInput) {
			searchInput.addEventListener('input', () => {
				renderOptions(searchInput.value);
			});

			searchInput.addEventListener('keydown', (event) => {
				if (event.key === 'Escape') {
					event.preventDefault();
					if (searchInput.value) {
						searchInput.value = '';
						renderOptions('');
					} else {
						closeDropdown();
						trigger.focus();
					}
				}
			});
		}

		document.addEventListener('click', (event) => {
			if (!wrapper.contains(event.target)) {
				closeDropdown();
			}
		});

		const parentForm = select.closest('form');
		if (parentForm && !parentForm.dataset.v2SelectValidationBound) {
			parentForm.dataset.v2SelectValidationBound = '1';
			parentForm.addEventListener('submit', (event) => {
				const requiredSelects = parentForm.querySelectorAll('.v2-select[data-required="1"]');
				let firstInvalid = null;

				requiredSelects.forEach((node) => {
					const input = node.querySelector('input[type="hidden"]');
					if (!input) {
						return;
					}
					const valid = String(input.value || '').trim() !== '';
					node.classList.toggle('is-invalid', !valid);
					if (!valid && !firstInvalid) {
						firstInvalid = node;
					}
				});

				if (firstInvalid) {
					event.preventDefault();
					const invalidTrigger = firstInvalid.querySelector('.v2-select-trigger');
					invalidTrigger?.focus();
				}
			});
		}

		select.addEventListener('change', () => {
			hiddenInput.value = select.value;
			updateTriggerLabel();
			renderOptions(searchInput ? searchInput.value : '');
		});

		updateTriggerLabel();
		renderOptions('');
	});
}

if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', initCustomSelects);
} else {
	initCustomSelects();
}

document.addEventListener('v2:refresh-selects', initCustomSelects);
