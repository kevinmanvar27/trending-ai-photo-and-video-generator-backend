<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'coins',
        'features',
        'is_active',
    ];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    /**
     * Get the features attribute and ensure it's always an array
     */
    public function getFeaturesAttribute($value)
    {
        if (is_null($value)) {
            return [];
        }
        
        if (is_array($value)) {
            return $value;
        }
        
        // If it's a string, try to decode it
        $decoded = json_decode($value, true);
        
        // If decoding failed or result is not an array, return empty array
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Set the features attribute
     */
    public function setFeaturesAttribute($value)
    {
        // If it's already an array, encode it
        if (is_array($value)) {
            $this->attributes['features'] = json_encode($value);
        } else {
            $this->attributes['features'] = $value;
        }
    }

    /**
     * Get subscriptions for this plan
     */
    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class);
    }

    /**
     * Get active subscriptions count
     */
    public function activeSubscriptionsCount()
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->count();
    }

    /**
     * Get formatted coins display
     */
    public function getFormattedCoinsAttribute(): string
    {
        return number_format($this->coins) . ' Coins';
    }
}
