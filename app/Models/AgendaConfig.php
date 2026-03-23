<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgendaConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nome_barbearia',
        'descricao',
        'telefone',
        'endereco',
        'horario_inicio',
        'horario_fim',
        'intervalo_slots',
        'dias_atendimento',
        'ativa',
        'public_token',
    ];

    protected $casts = [
        'dias_atendimento' => 'array',
        'ativa' => 'boolean',
    ];

    /**
     * Relação com User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relação com AgendaImagem
     */
    public function imagens()
    {
        return $this->hasMany(AgendaImagem::class)->orderBy('ordem');
    }

    /**
     * Obter URL pública
     */
    public function getPublicUrl()
    {
        return route('public.agendamento.show', ['public_token' => $this->public_token]);
    }

    /**
     * Obter slots disponíveis de uma data
     */
    public function getAvailableSlots($date)
    {
        $inicio = \Carbon\Carbon::createFromFormat('H:i', $this->horario_inicio);
        $fim = \Carbon\Carbon::createFromFormat('H:i', $this->horario_fim);
        $intervalo = $this->intervalo_slots;

        $slots = [];
        $current = $inicio->copy();

        while ($current->lt($fim)) {
            $slots[] = $current->format('H:i');
            $current->addMinutes($intervalo);
        }

        return $slots;
    }
}
