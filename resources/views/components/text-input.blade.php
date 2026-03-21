@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'v2-control']) }}>
