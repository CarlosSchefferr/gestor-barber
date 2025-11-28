<!-- products index -->
@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Produtos</h1>
            <p class="text-gray-600 mt-1">Lista de produtos</p>
        </div>
        <a href="{{ route('admin.products.create') }}" class="bg-barber-600 text-white px-4 py-2 rounded-lg hover:bg-barber-700 transition-colors shadow-sm">+ Novo Produto</a>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Preço</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantidade</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($products as $product)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $product->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ Str::limit($product->description ?? '-', 80) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">R$ {{ number_format($product->price,2,',','.') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $product->quantity }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <a href="{{ route('admin.products.edit', $product) }}" class="text-barber-600 hover:underline mr-3">Editar</a>
                        <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="inline-block" onsubmit="return confirm('Remover este produto?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600">Remover</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
