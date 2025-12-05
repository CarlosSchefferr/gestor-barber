<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transacao extends Model
{
    use HasFactory;

    protected $table = 'transacoes';

    protected $fillable = [
        'descricao',
        'tipo',
        'valor',
        'data',
        'status',
    ];

    protected $casts = [
        'data' => 'date',
        'valor' => 'decimal:2',
    ];
}
