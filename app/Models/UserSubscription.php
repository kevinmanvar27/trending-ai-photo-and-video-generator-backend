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
        'coins_used',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'coins_used' => 'integer',
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

    /**
     * Get remaining coins
     */
    public function getRemainingCoinsAttribute(): int
    {
        if (!$this->isActive()) {
            return 0;
        }
        
        $totalCoins = $this->plan->coins ?? 0;
        return max(0, $totalCoins - $this->coins_used);
    }

    /**
     * Use coins from subscription
     */
    public function useCoins(int $amount): bool
    {
        if ($this->remaining_coins < $amount) {
            return false;
        }

        $this->increment('coins_used', $amount);
        return true;
    }

    /**
     * Check if user has enough coins
     */
    public function hasEnoughCoins(int $amount): bool
    {
        return $this->remaining_coins >= $amount;
    }
}
