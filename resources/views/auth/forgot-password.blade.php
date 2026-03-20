<x-guest-layout>
    @php
        $inputClass = 'mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20';
    @endphp

    <div class="mb-8">
        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-barber-500">Recuperar acesso</p>
        <h1 class="mt-3 text-3xl font-bold leading-tight text-zinc-900">Esqueceu sua senha?</h1>
        <p class="mt-2 text-sm text-zinc-600">Informe seu e-mail e enviaremos um link para redefinição.</p>
    </div>

    @if (session('status'))
        <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3">
            <p class="text-sm font-medium text-emerald-700">{{ session('status') }}</p>
        </div>
    @endif

    <form
        x-data="{ sending: false }"
        @submit="sending = true"
        method="POST"
        action="{{ route('password.email') }}"
    >
        @csrf

        <div>
            <label for="email" class="text-sm font-semibold text-zinc-700">E-mail</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="username"
                class="{{ $inputClass }}"
                placeholder="seu@email.com"
            >
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <button
            type="submit"
            x-bind:disabled="sending"
            class="mt-7 inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-barber-500 px-4 py-3 text-sm font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-barber-600 focus:outline-none focus:ring-2 focus:ring-barber-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-80"
        >
            <svg x-show="sending" x-cloak class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
            </svg>
            <span x-text="sending ? 'Enviando link...' : 'Enviar link de redefinição'"></span>
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-zinc-600">
        <a href="{{ route('login') }}" class="font-semibold text-barber-600 transition hover:text-barber-700">Voltar para login</a>
    </p>
</x-guest-layout>
