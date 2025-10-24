<div class="max-w-2xl">
    <div class="mb-6">
        <h3 class="text-lg font-medium text-gray-900">Excluir Conta</h3>
        <p class="mt-1 text-sm text-gray-600">
            Ao excluir sua conta, todos os dados serão permanentemente removidos. Esta ação não pode ser desfeita.
        </p>
    </div>

    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">Atenção</h3>
                <div class="mt-2 text-sm text-red-700">
                    <p>Esta ação irá:</p>
                    <ul class="list-disc list-inside mt-1 space-y-1">
                        <li>Excluir permanentemente sua conta</li>
                        <li>Remover todos os seus dados pessoais</li>
                        <li>Cancelar todos os agendamentos futuros</li>
                        <li>Remover seu acesso ao sistema</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition-colors shadow-sm"
    >
        Excluir Conta
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <div class="p-6">
            <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-red-100 rounded-full">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
            
            <h2 class="text-xl font-semibold text-gray-900 text-center mb-4">
                Tem certeza que deseja excluir sua conta?
            </h2>

            <p class="text-sm text-gray-600 text-center mb-6">
                Esta ação não pode ser desfeita. Todos os seus dados serão permanentemente removidos.
            </p>

            <form method="post" action="{{ route('profile.destroy') }}">
                @csrf
                @method('delete')

                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Digite sua senha para confirmar</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-red-500 focus:ring-red-500 @error('password', 'userDeletion') border-red-300 @enderror"
                        placeholder="Sua senha atual"
                        required
                    />
                    @error('password', 'userDeletion')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" x-on:click="$dispatch('close')" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors shadow-sm">
                        Sim, Excluir Conta
                    </button>
                </div>
            </form>
        </div>
    </x-modal>
</div>
