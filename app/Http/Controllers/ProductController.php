<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductPriceHistory;
use App\Models\ProductStockMovement;
use App\Models\ProductUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'owner']);
    }

    public function index(Request $request)
    {
        $this->ensureDefaultUnits();

        $tab = $request->get('tab', 'produtos');
        $search = $request->get('search');
        $stock = $request->get('stock', '');
        $sort = $request->get('sort', 'name');
        $brand = $request->get('brand', '');
        $registrationType = $request->get('registration_type', '');

        $query = Product::query()->with(['unit', 'comboProducts.unit']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($brand) {
            $query->where('brand', $brand);
        }

        if ($registrationType) {
            $query->where('registration_type', $registrationType);
        }

        if ($stock === 'out') {
            $query->where('quantity', 0);
        } elseif ($stock === 'low') {
            $query->whereColumn('quantity', '<=', 'minimum_stock')->where('quantity', '>', 0);
        } elseif ($stock === 'ok') {
            $query->whereColumn('quantity', '>', 'minimum_stock');
        }

        match ($sort) {
            'price' => $query->orderBy('price'),
            'quantity' => $query->orderByDesc('quantity'),
            'brand' => $query->orderBy('brand')->orderBy('name'),
            default => $query->orderBy('name'),
        };

        $products = $query->paginate(20)->withQueryString();
        $units = ProductUnit::orderBy('name')->get();
        $activeUnits = $units->where('active', true)->values();
        $brands = Product::whereNotNull('brand')->where('brand', '!=', '')->distinct()->orderBy('brand')->pluck('brand');
        $stockProducts = Product::with('unit')->orderBy('name')->paginate(20, ['*'], 'stock_page')->withQueryString();
        $comboCatalog = Product::where('registration_type', 'product')->where('active', true)->with('unit')->orderBy('name')->get();
        $estoqueBaixo = Product::whereColumn('quantity', '<=', 'minimum_stock')->where('quantity', '>', 0)->count();
        $semEstoque = Product::where('quantity', 0)->count();

        return view('products.index', compact(
            'products',
            'units',
            'activeUnits',
            'brands',
            'stockProducts',
            'comboCatalog',
            'estoqueBaixo',
            'semEstoque',
            'tab'
        ));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        DB::transaction(function () use ($request, $data) {
            $product = Product::create($this->productPayload($request, $data));
            $this->syncComboItems($product, $data['combo_products'] ?? []);
            $this->recordPriceHistory($product, 'sale', (float) $product->price);
        });

        return redirect()->route('admin.products.index')->with('success', 'Produto criado com sucesso.');
    }

    public function show(Product $product)
    {
        $product->load(['unit', 'comboProducts.unit', 'stockMovements' => fn ($query) => $query->latest(), 'priceHistories' => fn ($query) => $query->latest()]);

        if (request()->expectsJson()) {
            return response()->json($this->productJson($product));
        }

        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validatedData($request, $product);
        $oldPrice = (float) $product->price;

        DB::transaction(function () use ($request, $product, $data, $oldPrice) {
            $product->update($this->productPayload($request, $data, $product));
            $this->syncComboItems($product, $data['combo_products'] ?? []);

            if ((float) $product->price !== $oldPrice) {
                $this->recordPriceHistory($product, 'sale', (float) $product->price);
            }
        });

        return redirect()->route('admin.products.index')->with('success', 'Produto atualizado.');
    }

    public function destroy(Product $product)
    {
        $product->update(['active' => false]);

        return redirect()->route('admin.products.index')->with('success', 'Produto desativado.');
    }

    public function toggleStatus(Product $product)
    {
        $product->update(['active' => ! $product->active]);

        return redirect()->route('admin.products.index')->with('success', 'Status do produto atualizado.');
    }

    public function adjustStock(Request $request)
    {
        $data = $request->validate([
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'required|exists:products,id',
            'movement_type' => ['required', Rule::in(['in', 'out'])],
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($data) {
            Product::whereIn('id', $data['product_ids'])->lockForUpdate()->get()->each(function (Product $product) use ($data) {
                $before = $product->quantity;
                $after = $data['movement_type'] === 'in'
                    ? $before + (int) $data['quantity']
                    : max(0, $before - (int) $data['quantity']);

                $product->update(['quantity' => $after]);

                ProductStockMovement::create([
                    'product_id' => $product->id,
                    'type' => $data['movement_type'],
                    'quantity' => (int) $data['quantity'],
                    'stock_before' => $before,
                    'stock_after' => $after,
                    'origin_type' => 'stock_adjustment',
                    'reason' => $data['reason'],
                    'created_by' => auth()->id(),
                ]);
            });
        });

        return redirect()->route('admin.products.index', ['tab' => 'estoque'])->with('success', 'Estoque ajustado com sucesso.');
    }

    public function bulkAction(Request $request)
    {
        $data = $request->validate([
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'required|exists:products,id',
            'action' => ['required', Rule::in(['activate', 'deactivate'])],
        ]);

        Product::whereIn('id', $data['product_ids'])->update([
            'active' => $data['action'] === 'activate',
        ]);

        $message = $data['action'] === 'activate'
            ? 'Produtos ativados com sucesso.'
            : 'Produtos excluídos com sucesso.';

        return redirect()->route('admin.products.index', ['tab' => 'estoque'])->with('success', $message);
    }

    public function storeUnit(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:80|unique:product_units,name',
            'abbreviation' => 'nullable|string|max:20',
        ]);

        ProductUnit::create($data + ['active' => true]);

        return redirect()->route('admin.products.index', ['tab' => 'unidades'])->with('success', 'Unidade de medida criada.');
    }

    public function toggleUnit(ProductUnit $unit)
    {
        $unit->update(['active' => ! $unit->active]);

        return redirect()->route('admin.products.index', ['tab' => 'unidades'])->with('success', 'Status da unidade atualizado.');
    }

    private function validatedData(Request $request, ?Product $product = null): array
    {
        return $request->validate([
            'description' => 'required|string|max:255',
            'brand' => 'required|string|max:120',
            'product_unit_id' => 'required_without:new_unit_name|nullable|exists:product_units,id',
            'new_unit_name' => 'required_without:product_unit_id|nullable|string|max:80',
            'new_unit_abbreviation' => 'nullable|string|max:20',
            'registration_type' => ['required', Rule::in(['product', 'combo'])],
            'usage_type' => ['required', Rule::in(['barbershop', 'sale', 'both'])],
            'price' => 'required|numeric|min:0',
            'commission_percentage' => 'required|numeric|min:0|max:100',
            'quantity' => 'nullable|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
            'barcode' => ['nullable', 'string', 'max:80', Rule::unique('products', 'barcode')->ignore($product)],
            'combo_products' => 'nullable|array',
            'combo_products.*' => 'integer|exists:products,id',
        ]);
    }

    private function productPayload(Request $request, array $data, ?Product $product = null): array
    {
        $unitId = $this->resolveUnitId($data);

        $payload = [
            'name' => $data['description'],
            'description' => $data['description'],
            'brand' => $data['brand'],
            'product_unit_id' => $unitId,
            'registration_type' => $data['registration_type'],
            'usage_type' => $data['usage_type'],
            'price' => (float) $data['price'],
            'commission_percentage' => (float) $data['commission_percentage'],
            'quantity' => $data['registration_type'] === 'combo' ? 0 : (int) ($data['quantity'] ?? $product?->quantity ?? 0),
            'minimum_stock' => (int) $data['minimum_stock'],
            'barcode' => $data['barcode'] ?? null,
            'active' => true,
        ];

        if ($request->hasFile('image')) {
            if ($product?->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }

            $payload['image_path'] = $request->file('image')->store('products', 'public');
        }

        return $payload;
    }

    private function resolveUnitId(array $data): int
    {
        if (! empty($data['new_unit_name'])) {
            return ProductUnit::firstOrCreate(
                ['name' => trim($data['new_unit_name'])],
                ['abbreviation' => $data['new_unit_abbreviation'] ?? null, 'active' => true]
            )->id;
        }

        return (int) ($data['product_unit_id'] ?? 0);
    }

    private function syncComboItems(Product $product, array $items): void
    {
        if ($product->registration_type !== 'combo') {
            $product->comboItems()->delete();
            return;
        }

        $ids = collect($items)->map(fn ($id) => (int) $id)->filter(fn ($id) => $id !== $product->id)->unique()->values();
        $product->comboItems()->delete();
        $ids->each(fn ($id) => $product->comboItems()->create(['product_id' => $id]));
    }

    private function recordPriceHistory(Product $product, string $type, float $value): void
    {
        ProductPriceHistory::create([
            'product_id' => $product->id,
            'type' => $type,
            'value' => $value,
            'created_by' => auth()->id(),
        ]);
    }

    private function ensureDefaultUnits(): void
    {
        collect([
            ['name' => 'Unidade', 'abbreviation' => 'un'],
            ['name' => 'Mililitro', 'abbreviation' => 'ml'],
            ['name' => 'Litro', 'abbreviation' => 'l'],
            ['name' => 'Grama', 'abbreviation' => 'g'],
            ['name' => 'Quilograma', 'abbreviation' => 'kg'],
        ])->each(fn ($unit) => ProductUnit::firstOrCreate(['name' => $unit['name']], $unit + ['active' => true]));
    }

    private function productJson(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'brand' => $product->brand,
            'product_unit_id' => $product->product_unit_id,
            'registration_type' => $product->registration_type,
            'usage_type' => $product->usage_type,
            'price' => $product->price,
            'commission_percentage' => $product->commission_percentage,
            'quantity' => $product->quantity,
            'minimum_stock' => $product->minimum_stock,
            'barcode' => $product->barcode,
            'active' => $product->active,
            'unit' => $product->unit?->abbreviation ?: $product->unit?->name ?: 'un',
            'combo_products' => $product->comboProducts->pluck('id')->values(),
            'stock_movements' => $product->stockMovements->map(fn ($movement) => [
                'date' => $movement->created_at?->format('d/m/Y H:i'),
                'type' => $movement->type,
                'quantity' => $movement->quantity,
                'origin' => $movement->origin_type === 'appointment' && $movement->origin_id
                    ? 'Atendimento número ' . $movement->origin_id
                    : 'Ajuste de estoque',
                'reason' => $movement->reason,
            ]),
            'price_histories' => $product->priceHistories->map(fn ($history) => [
                'type' => $history->type,
                'value' => $history->value,
                'date' => $history->created_at?->format('d/m/Y H:i'),
            ]),
        ];
    }
}
