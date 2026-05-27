<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'commission',
        'duration',
        'active',
        'type',
        'return_alert_days',
        'observations',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'commission' => 'decimal:2',
        'active' => 'boolean',
    ];

    public function comboServices()
    {
        return $this->belongsToMany(Service::class, 'combo_services', 'combo_id', 'service_id')->withTimestamps();
    }
}
