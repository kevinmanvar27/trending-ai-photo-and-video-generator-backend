<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserSubscription;
use App\Models\User;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Carbon\Carbon;

class UserSubscriptionController extends Controller
{
    /**
     * Display a listing of subscriptions
     */
    public function index(Request $request)
    {
        $query = UserSubscription::with(['user', 'plan']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $subscriptions = $query->latest()->paginate(15);

        return view('admin.subscriptions.index', compact('subscriptions'));
    }

    /**
     * Show create subscription form
     */
    public function create()
    {
        $users = User::where('role', 'user')->get();
        $plans = SubscriptionPlan::where('is_active', true)->get();

        return view('admin.subscriptions.create', compact('users', 'plans'));
    }

    /**
     * Store a new subscription
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'subscription_plan_id' => 'required|exists:subscription_plans,id',
            'started_at' => 'required|date',
        ]);

        $plan = SubscriptionPlan::findOrFail($validated['subscription_plan_id']);
        $startDate = Carbon::parse($validated['started_at']);

        // For coin-based plans, no expiry date is needed
        // Subscription is active until coins run out
        UserSubscription::create([
            'user_id' => $validated['user_id'],
            'subscription_plan_id' => $validated['subscription_plan_id'],
            'started_at' => $startDate,
            'expires_at' => null, // No expiry for coin-based plans
            'status' => 'active',
        ]);

        return redirect()->route('admin.subscriptions.index')->with('success', 'Subscription created successfully.');
    }

    /**
     * Cancel subscription
     */
    public function cancel($id)
    {
        $subscription = UserSubscription::findOrFail($id);

        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return back()->with('success', 'Subscription cancelled successfully.');
    }

    /**
     * Renew subscription
     */
    public function renew($id)
    {
        $subscription = UserSubscription::findOrFail($id);
        $plan = $subscription->plan;

        // For coin-based plans, renewing means reactivating the subscription
        // No expiry date calculation needed
        $subscription->update([
            'expires_at' => null, // No expiry for coin-based plans
            'status' => 'active',
            'cancelled_at' => null,
        ]);

        return back()->with('success', 'Subscription renewed successfully.');
    }
}
