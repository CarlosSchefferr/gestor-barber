@props(['active' => false])

@php
    $base = 'inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold transition';
    $state = $active
        ? 'bg-barber-500/90 text-white shadow-sm'
        : 'text-zinc-300 hover:bg-white/10 hover:text-white';
@endphp

<a {{ $attributes->merge(['class' => $base . ' ' . $state]) }}>
    {{ $slot }}
</a>
