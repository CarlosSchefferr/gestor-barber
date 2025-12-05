<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'email',
        'telefone',
        'observacoes',
        'active',
        'last_appointment_at',
    ];

    protected $casts = [
        'last_appointment_at' => 'datetime',
        'active' => 'boolean',
    ];

    public function agendamentos()
    {
        return $this->hasMany(Agendamento::class);
    }
}
