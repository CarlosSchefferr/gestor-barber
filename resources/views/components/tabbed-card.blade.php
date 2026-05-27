@props([
    'eyebrow' => null,
    'title',
    'tabs' => [],
    'active' => null,
])

<div {{ $attributes->merge(['class' => 'mb-8 overflow-hidden rounded-3xl border border-zinc-200 bg-white shadow-sm']) }}>
    <div class="px-6 py-7 sm:px-8">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
            <div>
                @if($eyebrow)
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500">{{ $eyebrow }}</p>
                @endif
                <h1 class="mt-2 text-3xl font-bold leading-tight text-zinc-900 sm:text-4xl">{{ $title }}</h1>
            </div>

            @isset($actions)
                <div class="flex flex-wrap items-center gap-3">
                    {{ $actions }}
                </div>
            @endisset
        </div>
    </div>

    <div class="border-t border-zinc-200 px-4 pb-4 sm:px-6">
        <div class="flex flex-col gap-2 rounded-2xl bg-zinc-50 p-2 sm:flex-row">
            @foreach($tabs as $tab)
                <a
                    href="{{ $tab['url'] }}"
                    class="inline-flex flex-1 items-center justify-center rounded-xl border px-4 py-2.5 text-sm font-semibold transition {{ $active === $tab['key'] ? 'border-barber-500 bg-barber-50 text-barber-600 shadow-sm' : 'border-transparent bg-white text-zinc-500 hover:border-zinc-300 hover:text-zinc-700' }}"
                >
                    {{ $tab['label'] }}
                </a>
            @endforeach
        </div>
    </div>
</div>
