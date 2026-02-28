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

        // Calculate expiry date based on plan duration
        $expiryDate = match($plan->duration_type) {
            'daily' => $startDate->copy()->addDays($plan->duration_value),
            'weekly' => $startDate->copy()->addWeeks($plan->duration_value),
            'monthly' => $startDate->copy()->addMonths($plan->duration_value),
            'yearly' => $startDate->copy()->addYears($plan->duration_value),
        };

        UserSubscription::create([
            'user_id' => $validated['user_id'],
            'subscription_plan_id' => $validated['subscription_plan_id'],
            'started_at' => $startDate,
            'expires_at' => $expiryDate,
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

        // Calculate new expiry date
        $expiryDate = match($plan->duration_type) {
            'daily' => now()->addDays($plan->duration_value),
            'weekly' => now()->addWeeks($plan->duration_value),
            'monthly' => now()->addMonths($plan->duration_value),
            'yearly' => now()->addYears($plan->duration_value),
        };

        $subscription->update([
            'expires_at' => $expiryDate,
            'status' => 'active',
            'cancelled_at' => null,
        ]);

        return back()->with('success', 'Subscription renewed successfully.');
    }
}
