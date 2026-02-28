<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subscription_plan_id',
        'started_at',
        'expires_at',
        'status',
        'cancelled_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Get the user that owns the subscription
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subscription plan
     */
    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    /**
     * Check if subscription is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && $this->expires_at > now();
    }

    /**
     * Check if subscription is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at <= now();
    }

    /**
     * Get days remaining
     */
    public function getDaysRemainingAttribute(): int
    {
        if ($this->isExpired()) {
            return 0;
        }
        
        return now()->diffInDays($this->expires_at);
    }
}
