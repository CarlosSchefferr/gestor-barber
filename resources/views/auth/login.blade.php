<x-guest-layout>
    <div class="max-w-md w-full mx-auto">
        <div class="text-center mb-6">
            <!-- Logo -->
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="mx-auto h-20 w-auto mb-2">
            <p class="text-sm text-gray-600 font-bold">{{ __("Faça seu login para acessar o sistema") }}</p>
        </div>


            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email Address -->
                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div class="mt-4">
                    <x-input-label for="password" :value="__('Senha')" />
                    <x-text-input id="password" class="block mt-1 w-full"
                                    type="password"
                                    name="password"
                                    required autocomplete="current-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="block mt-4">
                    @if (Route::has('password.request'))
                            <a class="underline text-sm text-gray-600 hover:text-barber-500 rounded-md" href="{{ route('password.request') }}">
                                {{ __('Esqueceu sua senha?') }}
                            </a>
                        @endif
                </div>

                <div class="flex flex-col sm:flex-row items-center justify-center mt-6 gap-3">
                    <div class="flex gap-2">
                        <x-barber-button type="submit">
                            {{ __('Entrar') }}
                        </x-barber-button>
                    </div>
                </div>
                {{-- 
                                <div class="mt-4 text-center text-sm text-gray-600">
                                    {{ __("Ainda não tem conta?") }}
                                    <a href="{{ route('register') }}" class="text-barber-500 hover:underline font-semibold">
                                        {{ __('Registrar agora') }}
                                    </a>
                                </div>
                --}}
            </form>

    </div>
</x-guest-layout>
