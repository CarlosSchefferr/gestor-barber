@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full rounded-xl bg-zinc-900 px-3 py-2 text-start text-sm font-semibold text-white transition'
            : 'block w-full rounded-xl px-3 py-2 text-start text-sm font-semibold text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
