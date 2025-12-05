@props(['href' => '#', 'title' => '', 'color' => 'bg-gray-50', 'textColor' => 'text-gray-700'])

<a {{ $attributes->merge(['href' => $href, 'class' => "icon-action inline-flex items-center justify-center w-10 h-10 rounded-lg {$color} hover:opacity-95 focus:outline-none", 'data-tooltip' => $title, 'aria-label' => $title]) }}>
    {{ $slot }}
</a>
