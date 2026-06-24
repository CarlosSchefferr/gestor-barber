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
        'slug',
        'logo',
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
     * Resolve a agenda pública por slug amigável OU public_token (compatível
     * com links antigos baseados em UUID).
     */
    public function scopeForPublicIdentifier($query, string $identifier)
    {
        return $query->where(function ($q) use ($identifier) {
            $q->where('slug', $identifier)->orWhere('public_token', $identifier);
        });
    }

    /**
     * Relação com AgendaImagem
     */
    public function imagens()
    {
        return $this->hasMany(AgendaImagem::class)->orderBy('ordem');
    }

    /**
     * Obter URL pública (usa slug amigável, com fallback para o token)
     */
    public function getPublicUrl()
    {
        return route('public.agendamento.show', ['public_token' => $this->slug ?: $this->public_token]);
    }

    /**
     * URL do logo da barbearia (avatar do chat), se houver.
     */
    public function getLogoUrl(): ?string
    {
        return $this->logo ? asset('storage/'.$this->logo) : null;
    }

    /**
     * Gera um slug único a partir de um texto, ignorando o próprio registro.
     */
    public static function generateUniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = \Illuminate\Support\Str::slug($base) ?: 'barbearia';
        $original = $slug;
        $i = 2;

        while (static::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $original.'-'.$i;
            $i++;
        }

        return $slug;
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
