@php
    $inputClass = 'mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-barber-500 focus:bg-white focus:ring-2 focus:ring-barber-500/20';
@endphp

<form id="send-verification" method="post" action="{{ route('verification.send') }}">
    @csrf
</form>

<form method="post" action="{{ route('profile.update') }}" class="space-y-6" enctype="multipart/form-data">
    @csrf
    @method('patch')

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div>
            <label for="avatar" class="text-sm font-semibold text-zinc-700">Foto de perfil</label>
            <input id="avatar" name="avatar" type="file" class="{{ $inputClass }} file:mr-4 file:rounded-xl file:border-0 file:bg-barber-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-barber-700 hover:file:bg-barber-100" accept="image/*">
            <p class="mt-2 text-xs text-zinc-500">PNG, JPG ou GIF ate 2MB</p>
            @error('avatar')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="name" class="text-sm font-semibold text-zinc-700">Nome completo <span class="text-red-500">*</span></label>
            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" class="{{ $inputClass }} @error('name') !border-red-300 @enderror">
            @error('name')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label for="email" class="text-sm font-semibold text-zinc-700">E-mail <span class="text-red-500">*</span></label>
        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="username" class="{{ $inputClass }} @error('email') !border-red-300 @enderror">
        @error('email')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4">
            <div class="flex gap-3">
                <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-amber-100">
                    <svg class="h-5 w-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-amber-800">E-mail nao verificado</h4>
                    <p class="mt-1 text-sm text-amber-700">Verifique sua caixa de entrada ou spam.</p>
                    <button form="send-verification" class="mt-2 text-sm font-semibold text-amber-800 underline hover:text-amber-900">Reenviar e-mail de verificacao</button>
                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm font-medium text-emerald-600">Um novo link foi enviado.</p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="flex justify-end pt-4">
        <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-barber-500 px-5 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-barber-600">
            Salvar alteracoes
        </button>
    </div>

    @if (session('status') === 'profile-updated')
        <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)" class="fixed bottom-6 right-6 z-50 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 shadow-lg">
            <div class="flex items-center gap-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-100">
                    <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-emerald-800">Perfil atualizado com sucesso!</span>
            </div>
        </div>
    @endif
</form>
