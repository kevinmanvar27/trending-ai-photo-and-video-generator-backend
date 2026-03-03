<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    /**
     * Get user's active subscription
     */
    public function mySubscription(Request $request)
    {
        try {
            $user = $request->user();
            
            // Get active subscription with plan details
            $subscription = UserSubscription::where('user_id', $user->id)
                ->where('status', 'active')
                ->with('plan')
                ->first();

            if (!$subscription) {
                return response()->json([
                    'success' => true,
                    'data' => null,
                    'message' => 'No active subscription found'
                ]);
            }

            // Check if subscription is actually active
            if (!$subscription->isActive()) {
                $subscription->update(['status' => 'expired']);
                
                return response()->json([
                    'success' => true,
                    'data' => null,
                    'message' => 'No active subscription found'
                ]);
            }

            // Calculate days remaining (for time-based) or use coins remaining
            $daysRemaining = 0;
            if ($subscription->expires_at) {
                $daysRemaining = max(0, now()->diffInDays($subscription->expires_at, false));
            }

            $remainingCoins = $subscription->remaining_coins;

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $subscription->id,
                    'user_id' => $subscription->user_id,
                    'plan' => [
                        'id' => $subscription->plan->id,
                        'name' => $subscription->plan->name,
                        'description' => $subscription->plan->description,
                        'price' => $subscription->plan->price,
                        'coins' => $subscription->plan->coins,
                        'features' => $subscription->plan->features,
                    ],
                    'status' => $subscription->status,
                    'started_at' => $subscription->started_at,
                    'expires_at' => $subscription->expires_at,
                    'days_remaining' => $daysRemaining,
                    'coins_used' => $subscription->coins_used,
                    'remaining_coins' => $remainingCoins,
                ],
                'message' => 'Subscription retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching subscription: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve subscription'
            ], 500);
        }
    }

    /**
     * Get all available subscription plans
     */
    public function plans(Request $request)
    {
        try {
            $plans = SubscriptionPlan::where('is_active', true)
                ->orderBy('price', 'asc')
                ->get()
                ->map(function ($plan) {
                    return [
                        'id' => $plan->id,
                        'name' => $plan->name,
                        'description' => $plan->description,
                        'price' => $plan->price,
                        'coins' => $plan->coins,
                        'features' => $plan->features,
                        'is_active' => $plan->is_active,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $plans,
                'message' => 'Subscription plans retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching plans: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve subscription plans'
            ], 500);
        }
    }

    /**
     * Subscribe to a plan
     */
    public function subscribe(Request $request)
    {
        try {
            $request->validate([
                'subscription_plan_id' => 'required|exists:subscription_plans,id',
                'payment_method' => 'required|string',
                'payment_token' => 'required|string',
            ]);

            $user = $request->user();
            $plan = SubscriptionPlan::findOrFail($request->subscription_plan_id);

            if (!$plan->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'This subscription plan is not available'
                ], 400);
            }

            DB::beginTransaction();

            try {
                // Cancel any existing active subscriptions
                UserSubscription::where('user_id', $user->id)
                    ->where('status', 'active')
                    ->update([
                        'status' => 'cancelled',
                        'cancelled_at' => now()
                    ]);

                // Create new subscription
                $subscription = UserSubscription::create([
                    'user_id' => $user->id,
                    'subscription_plan_id' => $plan->id,
                    'started_at' => now(),
                    'expires_at' => null, // Coin-based system, no expiry
                    'status' => 'active',
                    'coins_used' => 0,
                ]);

                DB::commit();

                // Load plan relationship
                $subscription->load('plan');

                return response()->json([
                    'success' => true,
                    'data' => [
                        'subscription' => [
                            'id' => $subscription->id,
                            'status' => $subscription->status,
                            'plan' => [
                                'id' => $plan->id,
                                'name' => $plan->name,
                                'coins' => $plan->coins,
                            ],
                            'expires_at' => $subscription->expires_at,
                            'remaining_coins' => $subscription->remaining_coins,
                        ]
                    ],
                    'message' => 'Subscription activated successfully'
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Error subscribing: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to activate subscription'
            ], 500);
        }
    }

    /**
     * Cancel subscription
     */
    public function cancel(Request $request)
    {
        try {
            $user = $request->user();
            
            $subscription = UserSubscription::where('user_id', $user->id)
                ->where('status', 'active')
                ->first();

            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active subscription found'
                ], 404);
            }

            $subscription->update([
                'status' => 'cancelled',
                'cancelled_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Subscription cancelled successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error cancelling subscription: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel subscription'
            ], 500);
        }
    }
}
