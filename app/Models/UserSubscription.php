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
        'coins_used',
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
        // For coin-based system: active if status is active and has remaining coins
        if ($this->status !== 'active') {
            return false;
        }
        
        // If expires_at is set (legacy), check expiry
        if ($this->expires_at) {
            return $this->expires_at > now();
        }
        
        // For coin-based: check if coins are remaining
        return $this->getRemainingCoinsAttribute() > 0;
    }

    /**
     * Check if subscription is expired
     */
    public function isExpired(): bool
    {
        // For coin-based system: expired if no coins remaining or status is not active
        if ($this->status !== 'active') {
            return true;
        }
        
        // If expires_at is set (legacy), check expiry
        if ($this->expires_at) {
            return $this->expires_at <= now();
        }
        
        // For coin-based: check if no coins remaining
        return $this->getRemainingCoinsAttribute() <= 0;
    }

    /**
     * Get remaining coins
     */
    public function getRemainingCoinsAttribute(): int
    {
        if (!$this->plan) {
            return 0;
        }
        
        $totalCoins = $this->plan->coins ?? 0;
        $usedCoins = $this->coins_used ?? 0;
        
        return max(0, $totalCoins - $usedCoins);
    }

    /**
     * Get days remaining (for backward compatibility)
     */
    public function getDaysRemainingAttribute(): int
    {
        // For coin-based system, return coins remaining instead
        return $this->getRemainingCoinsAttribute();
    }
}
