@props([
    'name' => '',
    'id' => null,
    'value' => '',
    'placeholder' => '0',
    'required' => false,
    'disabled' => false,
    'size' => 'default',
    'min' => '1',
    'step' => '1',
])

@php
    $componentId = $id ?? 'duration-' . Str::random(8);
    $sizeClasses = match($size) {
        'sm' => 'py-2 text-sm',
        default => 'py-3',
    };
@endphp

<div class="w-full rounded-2xl border border-zinc-200 bg-zinc-50 shadow-sm transition focus-within:border-barber-500 focus-within:bg-white focus-within:ring-2 focus-within:ring-barber-500/20 flex items-center overflow-hidden">
    <input
        type="number"
        name="{{ $name }}"
        id="{{ $componentId }}"
        value="{{ $value }}"
        placeholder="{{ $placeholder }}"
        min="{{ $min }}"
        step="{{ $step }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        class="w-full bg-transparent pl-4 {{ $sizeClasses }} text-zinc-900 placeholder:text-zinc-400 outline-none border-none focus:ring-0"
        {{ $attributes }}
    >
    <span class="pr-4 pl-2 text-sm select-none shrink-0" style="color: #18181b;">min</span>
</div>
