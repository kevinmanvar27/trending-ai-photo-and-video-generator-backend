<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class SubscriptionPlanController extends Controller
{
    /**
     * Display a listing of subscription plans
     */
    public function index()
    {
        $plans = SubscriptionPlan::withCount(['subscriptions' => function($query) {
            $query->where('status', 'active');
        }])->get();

        return view('admin.plans.index', compact('plans'));
    }

    /**
     * Show create plan form
     */
    public function create()
    {
        return view('admin.plans.create');
    }

    /**
     * Store a new plan
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_type' => 'required|in:daily,weekly,monthly,yearly',
            'duration_value' => 'required|integer|min:1',
            'coins' => 'required|integer|min:0',
            'features' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        SubscriptionPlan::create($validated);

        return redirect()->route('admin.plans.index')->with('success', 'Subscription plan created successfully.');
    }

    /**
     * Show edit plan form
     */
    public function edit($id)
    {
        $plan = SubscriptionPlan::findOrFail($id);
        return view('admin.plans.edit', compact('plan'));
    }

    /**
     * Update plan
     */
    public function update(Request $request, $id)
    {
        $plan = SubscriptionPlan::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_type' => 'required|in:daily,weekly,monthly,yearly',
            'duration_value' => 'required|integer|min:1',
            'coins' => 'required|integer|min:0',
            'features' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $plan->update($validated);

        return redirect()->route('admin.plans.index')->with('success', 'Subscription plan updated successfully.');
    }

    /**
     * Delete plan
     */
    public function destroy($id)
    {
        $plan = SubscriptionPlan::findOrFail($id);
        
        // Check if plan has active subscriptions
        if ($plan->activeSubscriptionsCount() > 0) {
            return back()->with('error', 'Cannot delete plan with active subscriptions.');
        }

        $plan->delete();

        return redirect()->route('admin.plans.index')->with('success', 'Subscription plan deleted successfully.');
    }

    /**
     * Toggle plan status
     */
    public function toggleStatus($id)
    {
        $plan = SubscriptionPlan::findOrFail($id);
        $plan->update(['is_active' => !$plan->is_active]);

        return back()->with('success', 'Plan status updated successfully.');
    }
}
