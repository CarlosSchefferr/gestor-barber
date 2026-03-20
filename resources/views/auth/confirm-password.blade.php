<x-guest-layout>
    @php
        $inputClass = 'mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20';
    @endphp

    <div class="mb-8">
        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-barber-500">Área segura</p>
        <h1 class="mt-3 text-3xl font-bold leading-tight text-zinc-900">Confirmar senha</h1>
        <p class="mt-2 text-sm text-zinc-600">Para continuar, confirme sua senha atual.</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div x-data="{ showPassword: false }">
            <label for="password" class="text-sm font-semibold text-zinc-700">Senha</label>
            <div class="relative mt-2">
                <input
                    :type="showPassword ? 'text' : 'password'"
                    id="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    class="{{ $inputClass }} pr-12"
                    placeholder="••••••••"
                >
                <button
                    type="button"
                    @click="showPassword = !showPassword"
                    class="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg p-1 text-zinc-400 transition hover:text-zinc-600"
                    tabindex="-1"
                    aria-label="Mostrar ou ocultar senha"
                >
                    <svg x-show="!showPassword" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg x-show="showPassword" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <button
            type="submit"
            class="mt-7 inline-flex w-full items-center justify-center rounded-2xl bg-barber-500 px-4 py-3 text-sm font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-barber-600 focus:outline-none focus:ring-2 focus:ring-barber-500 focus:ring-offset-2"
        >
            Confirmar acesso
        </button>
    </form>
</x-guest-layout>
