@props(['type' => 'button', 'class' => ''])
<button {{ $attributes->merge(['type' => $type, 'class' => 'inline-flex items-center px-4 py-2 bg-barber-700 text-white rounded shadow '.$class]) }}>
    {{ $slot }}
</button>
