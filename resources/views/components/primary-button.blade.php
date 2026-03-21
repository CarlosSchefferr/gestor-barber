<button {{ $attributes->merge(['type' => 'submit', 'class' => 'v2-btn-primary']) }}>
    {{ $slot }}
</button>
