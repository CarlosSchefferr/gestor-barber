<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductComboItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'combo_product_id',
        'product_id',
    ];

    public function combo()
    {
        return $this->belongsTo(Product::class, 'combo_product_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
