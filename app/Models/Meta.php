<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meta extends Model
{
    use HasFactory;

    protected $table = 'metas';

    protected $fillable = [
        'nome',
        'descricao',
        'valor_meta',
        'valor_atual',
        'data_inicio',
        'data_limite',
        'quem_tem_acesso',
        'tipo',
        'created_by',
    ];

    protected $casts = [
        'valor_meta' => 'decimal:2',
        'valor_atual' => 'decimal:2',
        'data_inicio' => 'date',
        'data_limite' => 'date',
    ];
}
