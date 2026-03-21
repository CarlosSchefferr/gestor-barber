@props(['active' => false])

@php
    $base = 'flex w-full items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition';
    $state = $active
    ? 'bg-zinc-900 text-white'
        : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900';
@endphp

<a {{ $attributes->merge(['class' => $base . ' ' . $state]) }}>
    {{ $slot }}
</a>
