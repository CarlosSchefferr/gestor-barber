<div class="max-w-4xl">
    <!-- Header do Perfil -->
    <div class="bg-gradient-to-r from-barber-50 to-barber-100 rounded-lg p-6 mb-8">
        <div class="flex items-center gap-6">
            <div class="w-20 h-20 rounded-full overflow-hidden bg-white border-4 border-barber-500 flex items-center justify-center shadow-lg">
                @if($user->avatar)
                    <img src="{{ asset('storage/' . $user->avatar) }}" alt="avatar" class="w-full h-full object-cover">
                @else
                    <div class="text-2xl font-bold text-barber-700">{{ strtoupper(substr($user->name,0,1)) }}</div>
                @endif
            </div>

            <div class="flex-1">
                <h3 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h3>
                <p class="text-gray-600 mb-2">{{ $user->email }}</p>
                <div class="flex items-center gap-3">
                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full {{ $user->role === 'owner' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                        {{ $user->role === 'owner' ? 'Proprietário' : 'Barbeiro' }}
                    </span>
                    <span class="text-sm text-gray-500">
                        Membro desde {{ $user->created_at->format('M/Y') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="avatar" class="block text-sm font-medium text-gray-700 mb-2">Foto de Perfil</label>
                <input id="avatar" name="avatar" type="file" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-barber-500 focus:ring-barber-500" accept="image/*">
                <p class="mt-1 text-sm text-gray-500">PNG, JPG ou GIF até 2MB</p>
                @error('avatar')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nome Completo <span class="text-red-500">*</span></label>
                <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-barber-500 focus:ring-barber-500 @error('name') border-red-300 @enderror">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">E-mail <span class="text-red-500">*</span></label>
            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="username" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-barber-500 focus:ring-barber-500 @error('email') border-red-300 @enderror">
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">E-mail não verificado</h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <p>Seu e-mail não foi verificado. Verifique sua caixa de entrada ou spam.</p>
                            <button form="send-verification" class="mt-2 text-sm font-medium text-yellow-800 hover:text-yellow-900 underline">Reenviar e-mail de verificação</button>
                        </div>
                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 font-medium text-sm text-green-600">Um novo link de verificação foi enviado.</p>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <div class="flex justify-end">
            <button type="submit" class="bg-barber-600 text-white px-6 py-3 rounded-lg hover:bg-barber-700 transition-colors shadow-sm">
                Salvar Alterações
            </button>
        </div>

        @if (session('status') === 'profile-updated')
            <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)" class="fixed top-20 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg shadow-lg z-50">
                Perfil atualizado com sucesso!
            </div>
        @endif
    </form>
</div>
