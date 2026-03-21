@php
    $inputClass = 'mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:border-red-500 focus:bg-white focus:ring-2 focus:ring-red-500/20';
@endphp

<div class="max-w-xl">
    <p class="text-sm text-zinc-600 mb-6">
        Ao excluir sua conta, todos os dados serao permanentemente removidos. Esta acao nao pode ser desfeita.
    </p>

    <div class="rounded-2xl border border-red-200 bg-red-50 p-4 mb-6">
        <div class="flex gap-3">
            <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-red-100">
                <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
            <div>
                <h4 class="text-sm font-semibold text-red-800">Esta acao ira:</h4>
                <ul class="mt-2 space-y-1 text-sm text-red-700">
                    <li class="flex items-center gap-2">
                        <span class="h-1 w-1 rounded-full bg-red-400"></span>
                        Excluir permanentemente sua conta
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="h-1 w-1 rounded-full bg-red-400"></span>
                        Remover todos os seus dados pessoais
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="h-1 w-1 rounded-full bg-red-400"></span>
                        Cancelar todos os agendamentos futuros
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="inline-flex items-center justify-center rounded-2xl bg-red-600 px-5 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-red-700"
    >
        Excluir conta
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <div class="p-6 sm:p-8">
            <div class="flex flex-col items-center text-center">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-red-100">
                    <svg class="h-7 w-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>

                <h2 class="mt-5 text-xl font-bold text-zinc-900">
                    Confirmar exclusao da conta
                </h2>

                <p class="mt-2 text-sm text-zinc-500">
                    Esta acao nao pode ser desfeita. Todos os seus dados serao permanentemente removidos.
                </p>
            </div>

            <form method="post" action="{{ route('profile.destroy') }}" class="mt-6">
                @csrf
                @method('delete')

                <div class="mb-6">
                    <label for="password" class="text-sm font-semibold text-zinc-700">Digite sua senha para confirmar</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        class="{{ $inputClass }} @error('password', 'userDeletion') !border-red-300 @enderror"
                        placeholder="Sua senha atual"
                        required
                    />
                    @error('password', 'userDeletion')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" x-on:click="$dispatch('close')" class="inline-flex items-center justify-center rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-xs font-bold uppercase tracking-[0.08em] text-zinc-700 transition hover:bg-zinc-100">
                        Cancelar
                    </button>
                    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-red-600 px-4 py-3 text-xs font-bold uppercase tracking-[0.08em] text-white shadow-sm transition hover:bg-red-700">
                        Sim, excluir conta
                    </button>
                </div>
            </form>
        </div>
    </x-modal>
</div>
