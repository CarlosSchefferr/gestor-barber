<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agendamento extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'barbeiro_id',
        'user_id',
        'starts_at',
        'ends_at',
        'status',
        'servico',
        'price',
        'observacoes',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'price' => 'decimal:2',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function barbeiro()
    {
        return $this->belongsTo(User::class, 'barbeiro_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
