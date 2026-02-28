<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSubscription;
use App\Models\SubscriptionPlan;
use App\Models\UserActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Show admin dashboard
     */
    public function index()
    {
        $stats = [
            'total_users' => User::where('role', 'user')->count(),
            'active_users' => User::where('role', 'user')
                ->where('last_activity_at', '>=', now()->subDays(7))
                ->count(),
            'suspended_users' => User::where('is_suspended', true)->count(),
            'total_subscriptions' => UserSubscription::where('status', 'active')->count(),
            'total_revenue' => UserSubscription::join('subscription_plans', 'user_subscriptions.subscription_plan_id', '=', 'subscription_plans.id')
                ->where('user_subscriptions.status', 'active')
                ->sum('subscription_plans.price'),
        ];

        // Recent users
        $recentUsers = User::where('role', 'user')
            ->latest()
            ->take(5)
            ->get();

        // Active subscriptions
        $activeSubscriptions = UserSubscription::with(['user', 'plan'])
            ->where('status', 'active')
            ->latest()
            ->take(5)
            ->get();

        // User activity chart data (last 7 days)
        $activityData = UserActivityLog::select(
                DB::raw('DATE(session_start) as date'),
                DB::raw('COUNT(DISTINCT user_id) as users'),
                DB::raw('SUM(duration) as total_time')
            )
            ->where('session_start', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('admin.dashboard', compact('stats', 'recentUsers', 'activeSubscriptions', 'activityData'));
    }
}
