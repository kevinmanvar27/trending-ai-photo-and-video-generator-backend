@extends('admin.layout')

@section('title', 'Dashboard')
@section('header', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Stats Cards -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                <i class="fas fa-users text-white text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-500">Total Users</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_users'] }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                <i class="fas fa-user-check text-white text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-500">Active Users (7 days)</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $stats['active_users'] }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-red-500 rounded-md p-3">
                <i class="fas fa-user-slash text-white text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-500">Suspended Users</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $stats['suspended_users'] }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                <i class="fas fa-dollar-sign text-white text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-500">Total Revenue</p>
                <p class="text-2xl font-semibold text-gray-900">${{ number_format($stats['total_revenue'], 2) }}</p>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Users -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold">Recent Users</h3>
        </div>
        <div class="p-6">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-2">Name</th>
                        <th class="text-left py-2">Email</th>
                        <th class="text-left py-2">Joined</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentUsers as $user)
                        <tr class="border-b">
                            <td class="py-2">{{ $user->name }}</td>
                            <td class="py-2">{{ $user->email }}</td>
                            <td class="py-2">{{ $user->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-4 text-gray-500">No users found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Active Subscriptions -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold">Active Subscriptions</h3>
        </div>
        <div class="p-6">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-2">User</th>
                        <th class="text-left py-2">Plan</th>
                        <th class="text-left py-2">Coins/Expires</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activeSubscriptions as $subscription)
                        <tr class="border-b">
                            <td class="py-2">{{ $subscription->user->name }}</td>
                            <td class="py-2">{{ $subscription->plan->name }}</td>
                            <td class="py-2">
                                @if($subscription->expires_at)
                                    {{ $subscription->expires_at->diffForHumans() }}
                                @else
                                    <span class="text-green-600 font-semibold">{{ number_format($subscription->remaining_coins) }}</span> coins left
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-4 text-gray-500">No active subscriptions</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
