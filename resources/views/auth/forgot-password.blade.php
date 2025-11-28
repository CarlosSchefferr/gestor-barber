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

    {{-- Form com Alpine para loading e animação --}}
    <form x-data="{ sending: false, dots: '', dotInterval: null }" x-init="$watch('sending', value => { if (value && !dotInterval) { dotInterval = setInterval(() => { dots = dots.length < 3 ? dots + '.' : ''; }, 400); } })" @submit.prevent="sending = true; $el.submit()" method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-center mt-4 relative">
            <x-primary-button :disabled="false" x-bind:disabled="sending" class="flex items-center gap-3">
                <template x-if="!sending">
                    <span>{{ __('Enviar link de redefinição de senha') }}</span>
                </template>
                <template x-if="sending">
                    <span class="flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
                        <span>Enviando link<span x-text="dots"></span></span>
                    </span>
                </template>
            </x-primary-button>


        </div>

        <div class="mt-4 text-center text-sm text-gray-600">
            <a href="{{ route('login') }}" class="text-barber-500 hover:underline font-semibold">
                {{ __('Voltar') }}
            </a>
        </div>
    </form>
</x-guest-layout>
