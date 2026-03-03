@extends('admin.layout')

@section('title', 'User Details')
@section('header', 'User Details')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.users.index') }}" class="text-blue-600 hover:text-blue-800">
        <i class="fas fa-arrow-left mr-1"></i> Back to Users
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- User Info -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">User Information</h3>
            <div class="space-y-3">
                <div>
                    <p class="text-sm text-gray-500">Name</p>
                    <p class="font-semibold">{{ $user->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Email</p>
                    <p class="font-semibold">{{ $user->email }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Status</p>
                    @if($user->is_suspended)
                        <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-800">Suspended</span>
                        <p class="text-sm mt-1">Reason: {{ $user->suspension_reason }}</p>
                    @else
                        <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-800">Active</span>
                    @endif
                </div>
                <div>
                    <p class="text-sm text-gray-500">Total Time Spent</p>
                    <p class="font-semibold">{{ $user->formatted_time_spent }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Total Sessions</p>
                    <p class="font-semibold">{{ $totalSessions }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Avg Session Duration</p>
                    <p class="font-semibold">{{ gmdate('H:i:s', $avgSessionDuration ?? 0) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Last Activity</p>
                    <p class="font-semibold">{{ $user->last_activity_at ? $user->last_activity_at->diffForHumans() : 'Never' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Joined</p>
                    <p class="font-semibold">{{ $user->created_at->format('M d, Y') }}</p>
                </div>
            </div>

            <div class="mt-6 space-y-2">
                <a href="{{ route('admin.users.edit', $user->id) }}" class="block w-full text-center bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Edit User
                </a>
                <a href="{{ route('admin.users.activity', $user->id) }}" class="block w-full text-center bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                    View Activity
                </a>
            </div>
        </div>
    </div>

    <!-- Subscriptions -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Subscription History</h3>
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Plan</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Started</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Coins</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($user->subscriptions as $subscription)
                        <tr>
                            <td class="px-4 py-3">{{ $subscription->plan->name }}</td>
                            <td class="px-4 py-3">{{ $subscription->started_at->format('M d, Y') }}</td>
                            <td class="px-4 py-3">
                                @if($subscription->expires_at)
                                    <!-- Legacy time-based subscription -->
                                    <span class="text-sm">Expires: {{ $subscription->expires_at->format('M d, Y') }}</span>
                                @else
                                    <!-- Coin-based subscription -->
                                    <span class="font-semibold text-green-600">{{ number_format($subscription->remaining_coins) }}</span> / {{ number_format($subscription->plan->coins) }}
                                    <span class="text-xs text-gray-500 block">Used: {{ number_format($subscription->coins_used) }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($subscription->status === 'active')
                                    <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-800">Active</span>
                                @elseif($subscription->status === 'expired')
                                    <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-800">Expired</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-800">Cancelled</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-3 text-center text-gray-500">No subscriptions</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-lg shadow p-6 mt-6">
            <h3 class="text-lg font-semibold mb-4">Recent Activity</h3>
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Session Start</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Duration</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Device</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($user->activityLogs->take(10) as $activity)
                        <tr>
                            <td class="px-4 py-3">{{ $activity->session_start->format('M d, Y H:i') }}</td>
                            <td class="px-4 py-3">{{ $activity->formatted_duration }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-800">{{ ucfirst($activity->device_type) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-center text-gray-500">No activity logs</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
