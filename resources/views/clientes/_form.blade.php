<div class="grid grid-cols-1 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700">Nome</label>
        <input type="text" name="nome" value="{{ old('nome') ?? ($cliente->nome ?? '') }}" class="mt-1 block w-full border-gray-300 rounded" required>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Email</label>
        <input type="email" name="email" value="{{ old('email') ?? ($cliente->email ?? '') }}" class="mt-1 block w-full border-gray-300 rounded">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Telefone</label>
        <input type="text" name="telefone" value="{{ old('telefone') ?? ($cliente->telefone ?? '') }}" class="mt-1 block w-full border-gray-300 rounded">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Observações</label>
        <textarea name="observacoes" class="mt-1 block w-full border-gray-300 rounded">{{ old('observacoes') ?? ($cliente->observacoes ?? '') }}</textarea>
    </div>
</div>
