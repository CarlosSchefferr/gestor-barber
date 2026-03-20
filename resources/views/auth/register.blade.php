<x-guest-layout>
    @php
        $inputClass = 'mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20';
    @endphp

    <div class="mb-8">
        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-barber-500">Novo cadastro</p>
        <h1 class="mt-3 text-3xl font-bold leading-tight text-zinc-900">Criar sua conta</h1>
        <p class="mt-2 text-sm text-zinc-600">Monte seu ambiente e comece a organizar sua barbearia agora.</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div>
            <label for="name" class="text-sm font-semibold text-zinc-700">Nome</label>
            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name') }}"
                required
                autofocus
                autocomplete="name"
                class="{{ $inputClass }}"
                placeholder="Seu nome completo"
            >
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-5">
            <label for="email" class="text-sm font-semibold text-zinc-700">E-mail</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autocomplete="username"
                class="{{ $inputClass }}"
                placeholder="seu@email.com"
            >
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2" x-data="{ showPassword: false, showConfirmation: false }">
            <div>
                <label for="password" class="text-sm font-semibold text-zinc-700">Senha</label>
                <div class="relative mt-2">
                    <input
                        :type="showPassword ? 'text' : 'password'"
                        id="password"
                        name="password"
                        required
                        autocomplete="new-password"
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

            <div>
                <label for="password_confirmation" class="text-sm font-semibold text-zinc-700">Confirmar senha</label>
                <div class="relative mt-2">
                    <input
                        :type="showConfirmation ? 'text' : 'password'"
                        id="password_confirmation"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                        class="{{ $inputClass }} pr-12"
                        placeholder="••••••••"
                    >
                    <button
                        type="button"
                        @click="showConfirmation = !showConfirmation"
                        class="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg p-1 text-zinc-400 transition hover:text-zinc-600"
                        tabindex="-1"
                        aria-label="Mostrar ou ocultar confirmação de senha"
                    >
                        <svg x-show="!showConfirmation" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg x-show="showConfirmation" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>
        </div>

        <button
            type="submit"
            class="mt-7 inline-flex w-full items-center justify-center rounded-2xl bg-barber-500 px-4 py-3 text-sm font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-barber-600 focus:outline-none focus:ring-2 focus:ring-barber-500 focus:ring-offset-2"
        >
            Criar conta
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-zinc-600">
        Já possui uma conta?
        <a href="{{ route('login') }}" class="font-semibold text-barber-600 transition hover:text-barber-700">
            Fazer login
        </a>
    </p>
</x-guest-layout>
