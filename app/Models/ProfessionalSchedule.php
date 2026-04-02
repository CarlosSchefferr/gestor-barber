<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfessionalSchedule extends Model
{
    use HasFactory;

    protected $table = 'professional_schedules';

    protected $fillable = [
        'user_id',
        'entry_time',
        'exit_time',
        'break_start',
        'break_end',
    ];

    protected $casts = [
        'entry_time' => 'datetime:H:i',
        'exit_time' => 'datetime:H:i',
        'break_start' => 'datetime:H:i',
        'break_end' => 'datetime:H:i',
    ];

    /**
     * User relationship
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
