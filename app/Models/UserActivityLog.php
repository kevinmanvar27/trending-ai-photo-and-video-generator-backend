<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_start',
        'session_end',
        'duration',
        'ip_address',
        'user_agent',
        'device_type',
    ];

    protected $casts = [
        'session_start' => 'datetime',
        'session_end' => 'datetime',
    ];

    /**
     * Get the user that owns the activity log
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get formatted duration
     */
    public function getFormattedDurationAttribute(): string
    {
        $seconds = $this->duration;
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;
        
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    }
}
