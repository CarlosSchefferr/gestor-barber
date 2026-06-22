<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatToolCall extends Model
{
    protected $fillable = [
        'chat_session_id',
        'tool',
        'arguments',
        'status',
        'duration_ms',
    ];

    protected $casts = [
        'arguments' => 'array',
        'duration_ms' => 'integer',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(ChatSession::class, 'chat_session_id');
    }
}
