<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'brand',
        'product_unit_id',
        'registration_type',
        'usage_type',
        'price',
        'commission_percentage',
        'quantity',
        'minimum_stock',
        'image_path',
        'barcode',
        'active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'commission_percentage' => 'decimal:2',
        'quantity' => 'integer',
        'minimum_stock' => 'integer',
        'active' => 'boolean',
    ];

    public function unit()
    {
        return $this->belongsTo(ProductUnit::class, 'product_unit_id');
    }

    public function comboItems()
    {
        return $this->hasMany(ProductComboItem::class, 'combo_product_id');
    }

    public function comboProducts()
    {
        return $this->belongsToMany(Product::class, 'product_combo_items', 'combo_product_id', 'product_id');
    }

    public function stockMovements()
    {
        return $this->hasMany(ProductStockMovement::class);
    }

    public function priceHistories()
    {
        return $this->hasMany(ProductPriceHistory::class);
    }

    public function scopeSellable($query)
    {
        return $query->whereIn('usage_type', ['sale', 'both'])->where('active', true);
    }
}
