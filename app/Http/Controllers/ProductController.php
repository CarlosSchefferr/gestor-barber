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
        $products = Product::orderBy('name')->paginate(20);
        return view('products.index', compact('products'));
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
