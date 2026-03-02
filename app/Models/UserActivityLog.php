<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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
     * Boot the model and add event listeners
     */
    protected static function boot()
    {
        parent::boot();

        // Before saving, validate and fix session times
        static::saving(function ($log) {
            // If both session_start and session_end are set
            if ($log->session_start && $log->session_end) {
                $start = $log->session_start instanceof Carbon ? $log->session_start : Carbon::parse($log->session_start);
                $end = $log->session_end instanceof Carbon ? $log->session_end : Carbon::parse($log->session_end);
                
                // If session_end is before session_start, swap them
                if ($end->lt($start)) {
                    $temp = $log->session_start;
                    $log->session_start = $log->session_end;
                    $log->session_end = $temp;
                    
                    // Recalculate duration with corrected times
                    $log->duration = abs($log->session_end->diffInSeconds($log->session_start));
                }
                
                // Ensure duration is calculated correctly
                if ($log->duration <= 0) {
                    $log->duration = abs($log->session_end->diffInSeconds($log->session_start));
                }
            }
        });
    }

    /**
     * Get the user that owns the activity log
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get duration attribute - ensure it's always positive
     */
    public function getDurationAttribute($value): ?int
    {
        // If duration is negative or null, recalculate from session times
        if ($value === null || $value < 0) {
            if ($this->session_start && $this->session_end) {
                return abs($this->session_end->diffInSeconds($this->session_start));
            }
            return 0;
        }
        
        return abs($value);
    }

    /**
     * Get formatted duration
     */
    public function getFormattedDurationAttribute(): string
    {
        $seconds = $this->duration ?? 0;
        
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;
        
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    }
}
