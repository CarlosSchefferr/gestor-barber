@props([
    'name' => '',
    'id' => null,
    'options' => [],
    'value' => '',
    'placeholder' => 'Selecione...',
    'searchable' => null,
    'searchPlaceholder' => 'Buscar...',
    'required' => false,
    'disabled' => false,
])

@php
    $componentId = $id ?? 'select-' . Str::random(8);
    $selectedLabel = '';
    foreach ($options as $optValue => $optLabel) {
        if ((string)$optValue === (string)$value) {
            $selectedLabel = $optLabel;
            break;
        }
    }
    // Auto-enable search when more than 3 options
    $showSearch = $searchable ?? (count($options) > 3);
@endphp

<div
    x-data="{
        open: false,
        search: '',
        value: '{{ $value }}',
        highlightedIndex: -1,
        repositionHandler: null,
        options: {{ Js::from(collect($options)->map(fn($label, $val) => ['value' => (string)$val, 'label' => $label])->values()) }},

        get filteredOptions() {
            if (!this.search.trim()) return this.options;
            const term = this.search.toLowerCase().trim();
            return this.options.filter(opt => opt.label.toLowerCase().includes(term));
        },

        get selectedLabel() {
            const found = this.options.find(opt => opt.value === this.value);
            return found ? found.label : '{{ $placeholder }}';
        },

        get hasSelection() {
            return this.value !== '';
        },

        toggle() {
            if (this.open) {
                this.close();
            } else {
                this.openDropdown();
            }
        },

        openDropdown() {
            this.open = true;
            this.search = '';
            this.highlightedIndex = -1;
            this.$nextTick(() => {
                this.positionDropdown();
                this.bindPositionListeners();
                if (this.$refs.searchInput) {
                    this.$refs.searchInput.focus();
                }
            });
        },

        close() {
            this.open = false;
            this.search = '';
            this.highlightedIndex = -1;
            this.unbindPositionListeners();
        },

        select(option) {
            this.value = option.value;
            this.$refs.hiddenInput.value = option.value;
            this.$refs.hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
            this.close();
            this.$refs.trigger.focus();
        },

        positionDropdown() {
            const trigger = this.$refs.trigger;
            const panel = this.$refs.panel;
            if (!trigger || !panel) return;

            const rect = trigger.getBoundingClientRect();
            const spaceBelow = window.innerHeight - rect.bottom;
            const spaceAbove = rect.top;
            const panelHeight = Math.min(panel.scrollHeight, 320);

            panel.style.position = 'fixed';
            panel.style.left = `${rect.left}px`;
            panel.style.width = `${rect.width}px`;
            panel.style.right = 'auto';
            panel.style.bottom = 'auto';
            panel.style.marginBottom = '0';
            panel.style.marginTop = '0';

            if (spaceBelow < panelHeight && spaceAbove > spaceBelow) {
                panel.style.top = `${Math.max(8, rect.top - panelHeight - 8)}px`;
            } else {
                panel.style.top = `${Math.min(window.innerHeight - panelHeight - 8, rect.bottom + 8)}px`;
            }
        },

        bindPositionListeners() {
            this.unbindPositionListeners();
            this.repositionHandler = () => this.positionDropdown();
            window.addEventListener('resize', this.repositionHandler);
            window.addEventListener('scroll', this.repositionHandler, true);
        },

        unbindPositionListeners() {
            if (!this.repositionHandler) return;
            window.removeEventListener('resize', this.repositionHandler);
            window.removeEventListener('scroll', this.repositionHandler, true);
            this.repositionHandler = null;
        },

        handleKeydown(e) {
            if (!this.open) {
                if (e.key === 'Enter' || e.key === ' ' || e.key === 'ArrowDown') {
                    e.preventDefault();
                    this.openDropdown();
                }
                return;
            }

            const options = this.filteredOptions;

            switch (e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    this.highlightedIndex = Math.min(this.highlightedIndex + 1, options.length - 1);
                    this.scrollToHighlighted();
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    this.highlightedIndex = Math.max(this.highlightedIndex - 1, 0);
                    this.scrollToHighlighted();
                    break;
                case 'Enter':
                    e.preventDefault();
                    if (this.highlightedIndex >= 0 && options[this.highlightedIndex]) {
                        this.select(options[this.highlightedIndex]);
                    }
                    break;
                case 'Escape':
                    e.preventDefault();
                    this.close();
                    this.$refs.trigger.focus();
                    break;
                case 'Tab':
                    this.close();
                    break;
            }
        },

        scrollToHighlighted() {
            this.$nextTick(() => {
                const highlighted = this.$refs.optionsList?.querySelector('.cs-option-highlighted');
                if (highlighted) {
                    highlighted.scrollIntoView({ block: 'nearest' });
                }
            });
        }
    }"
    @click.outside="close()"
    @keydown="handleKeydown"
    class="cs-wrapper relative"
    {{ $attributes->except(['class'])->merge(['class' => $attributes->get('class', '')]) }}
>
    {{-- Hidden input for form submission --}}
    <input
        type="hidden"
        name="{{ $name }}"
        x-ref="hiddenInput"
        x-model="value"
        @input="value = $event.target.value"
        @change="value = $event.target.value"
        {{ $required ? 'required' : '' }}
    >

    {{-- Trigger button --}}
    <button
        type="button"
        x-ref="trigger"
        @click="toggle()"
        :class="{ 'cs-trigger-open': open, 'cs-trigger-disabled': {{ $disabled ? 'true' : 'false' }} }"
        class="cs-trigger"
        {{ $disabled ? 'disabled' : '' }}
        aria-haspopup="listbox"
        :aria-expanded="open"
    >
        <span class="cs-trigger-text" :class="{ 'cs-trigger-placeholder': !hasSelection }" x-text="selectedLabel"></span>
        <span class="cs-trigger-arrow">
            <svg
                class="cs-trigger-arrow-icon"
                :class="{ 'cs-trigger-arrow-rotated': open }"
                viewBox="0 0 20 20"
                fill="currentColor"
            >
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
            </svg>
        </span>
    </button>

    {{-- Dropdown panel --}}
    <div
        x-ref="panel"
        x-show="open"
        x-transition:enter="cs-enter"
        x-transition:enter-start="cs-enter-start"
        x-transition:enter-end="cs-enter-end"
        x-transition:leave="cs-leave"
        x-transition:leave-start="cs-leave-start"
        x-transition:leave-end="cs-leave-end"
        class="cs-panel"
        style="display: none;"
        role="listbox"
    >
        {{-- Search input --}}
        @if($showSearch)
        <div class="cs-search-wrapper">
            <input
                type="text"
                x-ref="searchInput"
                x-model="search"
                @keydown.stop
                class="cs-search-input"
                placeholder="{{ $searchPlaceholder }}"
                autocomplete="off"
            >
        </div>
        @endif

        {{-- Options list --}}
        <div x-ref="optionsList" class="cs-options-list">
            <template x-for="(option, index) in filteredOptions" :key="option.value">
                <button
                    type="button"
                    @click="select(option)"
                    @mouseenter="highlightedIndex = index"
                    :class="{
                        'cs-option-selected': option.value === value,
                        'cs-option-highlighted': highlightedIndex === index
                    }"
                    class="cs-option"
                    role="option"
                    :aria-selected="option.value === value"
                >
                    <span x-text="option.label" class="cs-option-text"></span>
                    <span x-show="option.value === value" class="cs-option-check">
                        <svg class="cs-option-check-icon" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </button>
            </template>

            {{-- Empty state --}}
            <div x-show="filteredOptions.length === 0" class="cs-empty">
                <svg class="cs-empty-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <span>Nenhum resultado encontrado</span>
            </div>
        </div>
    </div>
</div>
