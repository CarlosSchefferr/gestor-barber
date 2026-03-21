<button {{ $attributes->merge(['type' => 'submit', 'class' => 'v2-btn-danger']) }}>
    {{ $slot }}
</button>
