<div class="max-w-2xl">
    <div class="mb-6">
        <h3 class="text-lg font-medium text-gray-900">Atualizar Senha</h3>
        <p class="mt-1 text-sm text-gray-600">
            Mantenha sua conta segura usando uma senha forte e única.
        </p>
    </div>

    <form method="post" action="{{ route('password.update') }}" class="space-y-6">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="block text-sm font-medium text-gray-700 mb-2">Senha Atual <span class="text-red-500">*</span></label>
            <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-barber-500 focus:ring-barber-500 @error('current_password', 'updatePassword') border-red-300 @enderror">
            @error('current_password', 'updatePassword')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="update_password_password" class="block text-sm font-medium text-gray-700 mb-2">Nova Senha <span class="text-red-500">*</span></label>
            <input id="update_password_password" name="password" type="password" autocomplete="new-password" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-barber-500 focus:ring-barber-500 @error('password', 'updatePassword') border-red-300 @enderror">
            <p class="mt-1 text-sm text-gray-500">Mínimo de 8 caracteres</p>
            @error('password', 'updatePassword')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="update_password_password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirmar Nova Senha <span class="text-red-500">*</span></label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-barber-500 focus:ring-barber-500 @error('password_confirmation', 'updatePassword') border-red-300 @enderror">
            @error('password_confirmation', 'updatePassword')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-barber-600 text-white px-6 py-3 rounded-lg hover:bg-barber-700 transition-colors shadow-sm">
                Atualizar Senha
            </button>
        </div>

        @if (session('status') === 'password-updated')
            <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)" class="fixed top-20 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg shadow-lg z-50">
                Senha atualizada com sucesso!
            </div>
        @endif
    </form>
</div>
