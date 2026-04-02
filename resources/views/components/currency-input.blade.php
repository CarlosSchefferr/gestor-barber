@props([
    'name' => '',
    'id' => null,
    'value' => '',
    'placeholder' => '0,00',
    'required' => false,
    'disabled' => false,
    'size' => 'default',
])

@php
    $componentId = $id ?? 'currency-' . Str::random(8);
    $sizeClasses = match($size) {
        'sm' => 'py-2 text-sm',
        default => 'py-3',
    };
@endphp

<div class="w-full rounded-2xl border border-zinc-200 bg-zinc-50 shadow-sm transition focus-within:border-barber-500 focus-within:bg-white focus-within:ring-2 focus-within:ring-barber-500/20 flex items-center overflow-hidden">
    <span class="pl-4 pr-2 text-sm select-none shrink-0" style="color: #18181b;">R$</span>
    <input
        type="text"
        name="{{ $name }}"
        id="{{ $componentId }}"
        value="{{ $value }}"
        inputmode="decimal"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        class="w-full bg-transparent pr-4 {{ $sizeClasses }} text-zinc-900 placeholder:text-zinc-400 outline-none border-none focus:ring-0"
        oninput="formatarMoeda(this)"
        {{ $attributes }}
    >
</div>
