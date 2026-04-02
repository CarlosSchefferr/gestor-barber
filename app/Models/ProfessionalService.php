<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfessionalService extends Model
{
    use HasFactory;

    protected $table = 'professional_services';

    protected $fillable = [
        'user_id',
        'service_id',
        'time_minutes',
        'price',
        'commission_percentage',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'commission_percentage' => 'decimal:2',
    ];

    /**
     * User relationship
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Service relationship
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
