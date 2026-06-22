<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatUsage extends Model
{
    protected $fillable = [
        'chat_session_id',
        'agenda_config_id',
        'usage_date',
        'model',
        'input_tokens',
        'cached_tokens',
        'output_tokens',
        'tool_calls',
        'latency_ms',
        'status',
        'response_id',
    ];

    protected $casts = [
        'usage_date' => 'date',
        'input_tokens' => 'integer',
        'cached_tokens' => 'integer',
        'output_tokens' => 'integer',
        'tool_calls' => 'integer',
        'latency_ms' => 'integer',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(ChatSession::class, 'chat_session_id');
    }
}
