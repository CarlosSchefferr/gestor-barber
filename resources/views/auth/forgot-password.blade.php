<x-guest-layout>
       <div class="text-center mb-6">
            <!-- Logo -->
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="mx-auto h-20 w-auto mb-2">

        </div>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Esqueceu sua senha? Sem problemas. Basta informar seu endereço de e-mail e nós enviaremos um link para redefinir sua senha.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-center mt-4">
            <x-primary-button>
                {{ __('Enviar link de redefinição de senha') }}
            </x-primary-button>
        </div>
          <div class="mt-4 text-center text-sm text-gray-600">
                    <a href="{{ route('login') }}" class="text-barber-500 hover:underline font-semibold">
                        {{ __('Voltar') }}
                    </a>
                </div>
    </form>
</x-guest-layout>
