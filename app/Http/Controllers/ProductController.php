<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','owner']);
    }

    public function index()
    {
        $search = request('search');
        $stock = request('stock', '');
        $sort = request('sort', 'name');

        $query = Product::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($stock === 'out') {
            $query->where('quantity', 0);
        } elseif ($stock === 'low') {
            $query->whereBetween('quantity', [1, 5]);
        } elseif ($stock === 'ok') {
            $query->where('quantity', '>', 5);
        }

        if ($sort === 'price') {
            $query->orderBy('price');
        } elseif ($sort === 'quantity') {
            $query->orderByDesc('quantity');
        } else {
            $query->orderBy('name');
        }

        $products = $query->paginate(20)->withQueryString();

        $totalProdutos = Product::count();
        $itensEmEstoque = (int) Product::sum('quantity');
        $estoqueBaixo = Product::whereBetween('quantity', [1, 5])->count();
        $semEstoque = Product::where('quantity', 0)->count();

        return view('products.index', compact(
            'products',
            'totalProdutos',
            'itensEmEstoque',
            'estoqueBaixo',
            'semEstoque'
        ));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'quantity' => 'required|integer|min:0',
        ]);

        $data['price'] = (float) $data['price'];

        Product::create($data);

        return redirect()->route('admin.products.index')->with('success', 'Produto criado com sucesso.');
    }

    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'quantity' => 'required|integer|min:0',
        ]);

        $data['price'] = (float) $data['price'];

        $product->update($data);

        return redirect()->route('admin.products.index')->with('success', 'Produto atualizado.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Produto removido.');
    }
}
