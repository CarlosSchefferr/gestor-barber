<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'data_nascimento',
        'email',
        'telefone',
        'cep',
        'bairro',
        'observacoes',
        'foto',
        'active',
        'last_appointment_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'data_nascimento' => 'date',
        'last_appointment_at' => 'datetime',
        'active' => 'boolean',
    ];

    public function agendamentos()
    {
        return $this->hasMany(Agendamento::class);
    }

    /**
     * Último agendamento (relation helper)
     */
    public function lastAgendamento()
    {
        return $this->hasOne(Agendamento::class)->latestOfMany('starts_at');
    }

    /**
     * Usuário que criou o registro
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Usuário que atualizou o registro
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Dias desde o último atendimento
     */
    public function getDaysSinceLastAppointmentAttribute()
    {
        if (!$this->last_appointment_at) {
            return null;
        }
        return now()->diffInDays($this->last_appointment_at);
    }

    /**
     * Total de atendimentos
     */
    public function getTotalAppointmentsAttribute()
    {
        return $this->agendamentos()->where('status', 'atendido')->count();
    }

    /**
     * Receita total do cliente
     */
    public function getTotalRevenueAttribute()
    {
        return $this->agendamentos()
            ->where('status', 'atendido')
            ->sum('price');
    }
}
