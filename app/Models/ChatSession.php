<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatSession extends Model
{
    protected $fillable = [
        'agenda_config_id',
        'session_token',
        'status',
        'state',
        'locale',
        'ip_hash',
        'message_count',
        'last_activity_at',
        'expires_at',
    ];

    protected $casts = [
        'state' => 'array',
        'message_count' => 'integer',
        'last_activity_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function agendaConfig(): BelongsTo
    {
        return $this->belongsTo(AgendaConfig::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function proposals(): HasMany
    {
        return $this->hasMany(ChatBookingProposal::class);
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired'
            || ($this->expires_at !== null && $this->expires_at->isPast());
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && ! $this->isExpired();
    }
}
