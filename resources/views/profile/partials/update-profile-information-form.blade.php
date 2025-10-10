<section>
    <x-barber-card>
        <div class="flex items-start gap-6">
            <div class="w-20 h-20 rounded-full overflow-hidden bg-barber-100 border-4 border-barber-500 flex items-center justify-center">
                @if($user->avatar)
                    <img src="{{ asset('storage/' . $user->avatar) }}" alt="avatar" class="w-full h-full object-cover">
                @else
                    <div class="text-2xl font-bold text-barber-700">{{ strtoupper(substr($user->name,0,1)) }}</div>
                @endif
            </div>

            <div class="flex-1">
                <h2 class="text-xl font-semibold text-gray-900">{{ $user->name }}</h2>
                <p class="text-sm text-gray-500">Atualize suas informações de perfil e foto</p>

                <form id="send-verification" method="post" action="{{ route('verification.send') }}">
                    @csrf
                </form>

                <form method="post" action="{{ route('profile.update') }}" class="mt-4 space-y-4" enctype="multipart/form-data">
                    @csrf
                    @method('patch')

                    <div>
                        <x-input-label for="avatar" value="Foto (opcional)" />
                        <input id="avatar" name="avatar" type="file" class="mt-1 block w-full" accept="image/*">
                        <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
                    </div>

                    <div>
                        <x-input-label for="name" value="Nome" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>

                    <div>
                        <x-input-label for="email" value="Email" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
                        <x-input-error class="mt-2" :messages="$errors->get('email')" />
                    </div>

                    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                        <div>
                            <p class="text-sm mt-2 text-gray-800">Seu e-mail não foi verificado.</p>
                            <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Reenviar e-mail de verificação</button>
                            @if (session('status') === 'verification-link-sent')
                                <p class="mt-2 font-medium text-sm text-green-600">Um novo link de verificação foi enviado.</p>
                            @endif
                        </div>
                    @endif

                    <div class="flex items-center gap-4 mt-2">
                        <x-primary-button>Salvar</x-primary-button>

                        @if (session('status') === 'profile-updated')
                            <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm text-gray-600">Salvo.</p>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </x-barber-card>
</section>
