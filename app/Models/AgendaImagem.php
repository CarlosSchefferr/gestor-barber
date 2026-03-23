<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgendaImagem extends Model
{
    use HasFactory;

    protected $table = 'agenda_imagens';

    protected $fillable = [
        'agenda_config_id',
        'caminho_imagem',
        'ordem',
    ];

    /**
     * Relação com AgendaConfig
     */
    public function agendaConfig()
    {
        return $this->belongsTo(AgendaConfig::class);
    }
}
