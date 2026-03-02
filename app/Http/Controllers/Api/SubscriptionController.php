<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    /**
     * Get all subscription plans
     */
    public function plans(Request $request)
    {
        try {
            $query = SubscriptionPlan::query();

            // Filter by active status
            if ($request->has('is_active')) {
                $query->where('is_active', $request->is_active);
            }

            // Sort by price
            $sortBy = $request->get('sort_by', 'price');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            $plans = $query->get();

            return response()->json([
                'success' => true,
                'data' => $plans
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch subscription plans',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific plan
     */
    public function showPlan($id)
    {
        try {
            $plan = SubscriptionPlan::find($id);

            if (!$plan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Plan not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $plan
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch plan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new subscription plan
     */
    public function createPlan(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'duration_type' => 'required|string|in:day,week,month,year',
                'duration_value' => 'required|integer|min:1',
                'coins' => 'required|integer|min:0',
                'features' => 'nullable|array',
                'is_active' => 'nullable|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $plan = SubscriptionPlan::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Subscription plan created successfully',
                'data' => $plan
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create plan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a subscription plan
     */
    public function updatePlan(Request $request, $id)
    {
        try {
            $plan = SubscriptionPlan::find($id);

            if (!$plan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Plan not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'sometimes|required|numeric|min:0',
                'duration_type' => 'sometimes|required|string|in:day,week,month,year',
                'duration_value' => 'sometimes|required|integer|min:1',
                'coins' => 'sometimes|required|integer|min:0',
                'features' => 'nullable|array',
                'is_active' => 'nullable|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $plan->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Plan updated successfully',
                'data' => $plan
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update plan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a subscription plan
     */
    public function deletePlan($id)
    {
        try {
            $plan = SubscriptionPlan::find($id);

            if (!$plan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Plan not found'
                ], 404);
            }

            // Check if plan has active subscriptions
            $activeCount = $plan->activeSubscriptionsCount();
            if ($activeCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete plan with active subscriptions'
                ], 400);
            }

            $plan->delete();

            return response()->json([
                'success' => true,
                'message' => 'Plan deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete plan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Subscribe to a plan
     */
    public function subscribe(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'subscription_plan_id' => 'required|exists:subscription_plans,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $plan = SubscriptionPlan::find($request->subscription_plan_id);

            if (!$plan->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'This plan is not available'
                ], 400);
            }

            // Check if user already has an active subscription
            $activeSubscription = UserSubscription::where('user_id', $user->id)
                ->where('status', 'active')
                ->where('expires_at', '>', now())
                ->first();

            if ($activeSubscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have an active subscription'
                ], 400);
            }

            // Calculate expiration date
            $startDate = now();
            $expiresAt = match($plan->duration_type) {
                'day' => $startDate->copy()->addDays($plan->duration_value),
                'week' => $startDate->copy()->addWeeks($plan->duration_value),
                'month' => $startDate->copy()->addMonths($plan->duration_value),
                'year' => $startDate->copy()->addYears($plan->duration_value),
                default => $startDate->copy()->addMonth()
            };

            // Create subscription
            $subscription = UserSubscription::create([
                'user_id' => $user->id,
                'subscription_plan_id' => $plan->id,
                'started_at' => $startDate,
                'expires_at' => $expiresAt,
                'status' => 'active'
            ]);

            $subscription->load('plan');

            return response()->json([
                'success' => true,
                'message' => 'Subscription created successfully',
                'data' => $subscription
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create subscription',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's current subscription
     */
    public function mySubscription()
    {
        try {
            $user = Auth::user();
            
            $subscription = UserSubscription::where('user_id', $user->id)
                ->where('status', 'active')
                ->where('expires_at', '>', now())
                ->with('plan')
                ->first();

            if (!$subscription) {
                return response()->json([
                    'success' => true,
                    'message' => 'No active subscription',
                    'data' => null
                ], 200);
            }

            return response()->json([
                'success' => true,
                'data' => $subscription
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch subscription',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's subscription history
     */
    public function subscriptionHistory()
    {
        try {
            $user = Auth::user();
            
            $subscriptions = UserSubscription::where('user_id', $user->id)
                ->with('plan')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $subscriptions
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch subscription history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel subscription
     */
    public function cancelSubscription()
    {
        try {
            $user = Auth::user();
            
            $subscription = UserSubscription::where('user_id', $user->id)
                ->where('status', 'active')
                ->where('expires_at', '>', now())
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
                'message' => 'Subscription cancelled successfully',
                'data' => $subscription
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel subscription',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
