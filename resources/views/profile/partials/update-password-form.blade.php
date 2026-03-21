@php
    $inputClass = 'mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20';
@endphp

<div class="max-w-xl">
    <p class="text-sm text-zinc-600 mb-6">
        Mantenha sua conta segura usando uma senha forte e unica.
    </p>

    <form method="post" action="{{ route('password.update') }}" class="space-y-5">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="text-sm font-semibold text-zinc-700">Senha atual <span class="text-red-500">*</span></label>
            <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password" class="{{ $inputClass }} @error('current_password', 'updatePassword') !border-red-300 @enderror" placeholder="Digite sua senha atual">
            @error('current_password', 'updatePassword')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="update_password_password" class="text-sm font-semibold text-zinc-700">Nova senha <span class="text-red-500">*</span></label>
            <input id="update_password_password" name="password" type="password" autocomplete="new-password" class="{{ $inputClass }} @error('password', 'updatePassword') !border-red-300 @enderror" placeholder="Minimo de 8 caracteres">
            @error('password', 'updatePassword')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="update_password_password_confirmation" class="text-sm font-semibold text-zinc-700">Confirmar nova senha <span class="text-red-500">*</span></label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="{{ $inputClass }} @error('password_confirmation', 'updatePassword') !border-red-300 @enderror" placeholder="Repita a nova senha">
            @error('password_confirmation', 'updatePassword')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex justify-end pt-2">
            <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-barber-500 px-5 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-barber-600">
                Atualizar senha
            </button>
        </div>

        @if (session('status') === 'password-updated')
            <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)" class="fixed bottom-6 right-6 z-50 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 shadow-lg">
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-100">
                        <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <span class="text-sm font-semibold text-emerald-800">Senha atualizada com sucesso!</span>
                </div>
            </div>
        @endif
    </form>
</div>
