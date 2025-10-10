<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700">Cliente</label>
        <select name="cliente_id" class="mt-1 block w-full border-gray-300 rounded">
            @foreach($clientes as $c)
                <option value="{{ $c->id }}" {{ (old('cliente_id') ?? ($agendamento->cliente_id ?? '')) == $c->id ? 'selected' : '' }}>{{ $c->nome }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Barbeiro</label>
        <select name="barbeiro_id" class="mt-1 block w-full border-gray-300 rounded">
            @foreach($barbeiros as $b)
                <option value="{{ $b->id }}" {{ (old('barbeiro_id') ?? ($agendamento->barbeiro_id ?? '')) == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Data e hora início</label>
        <input type="datetime-local" name="starts_at" value="{{ old('starts_at') ?? (isset($agendamento) ? $agendamento->starts_at->format('Y-m-d\TH:i') : '') }}" class="mt-1 block w-full border-gray-300 rounded">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Data e hora fim</label>
        <input type="datetime-local" name="ends_at" value="{{ old('ends_at') ?? (isset($agendamento) && $agendamento->ends_at ? $agendamento->ends_at->format('Y-m-d\TH:i') : '') }}" class="mt-1 block w-full border-gray-300 rounded">
    </div>

    <div class="col-span-2">
        <label class="block text-sm font-medium text-gray-700">Serviço</label>
        <input type="text" name="servico" value="{{ old('servico') ?? ($agendamento->servico ?? '') }}" class="mt-1 block w-full border-gray-300 rounded">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Preço</label>
        <input type="number" step="0.01" name="price" value="{{ old('price') ?? ($agendamento->price ?? '') }}" class="mt-1 block w-full border-gray-300 rounded">
    </div>

    <div class="col-span-2">
        <label class="block text-sm font-medium text-gray-700">Observações</label>
        <textarea name="observacoes" class="mt-1 block w-full border-gray-300 rounded">{{ old('observacoes') ?? ($agendamento->observacoes ?? '') }}</textarea>
    </div>
</div>
