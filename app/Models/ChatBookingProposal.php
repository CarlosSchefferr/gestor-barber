<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatBookingProposal extends Model
{
    protected $fillable = [
        'chat_session_id',
        'agenda_config_id',
        'token',
        'service_id',
        'professional_id',
        'starts_at',
        'ends_at',
        'price',
        'duration_minutes',
        'customer_name',
        'customer_email',
        'customer_phone',
        'observacoes',
        'status',
        'idempotency_key',
        'agendamento_id',
        'confirmed_at',
        'expires_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'price' => 'decimal:2',
        'duration_minutes' => 'integer',
        'confirmed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(ChatSession::class, 'chat_session_id');
    }

    public function agendaConfig(): BelongsTo
    {
        return $this->belongsTo(AgendaConfig::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function professional(): BelongsTo
    {
        return $this->belongsTo(User::class, 'professional_id');
    }

    public function agendamento(): BelongsTo
    {
        return $this->belongsTo(Agendamento::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending' && ! $this->isExpired();
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired'
            || ($this->expires_at !== null && $this->expires_at->isPast());
    }

    public function hasCustomerData(): bool
    {
        return filled($this->customer_name)
            && filled($this->customer_email)
            && filled($this->customer_phone);
    }
}
