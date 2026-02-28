@extends('admin.layout')

@section('title', 'Subscriptions')
@section('header', 'User Subscriptions')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <div class="flex items-center space-x-4">
            <form action="{{ route('admin.subscriptions.index') }}" method="GET" class="flex space-x-2">
                <select name="status" class="border rounded px-3 py-2">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
                <button type="submit" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Filter
                </button>
            </form>
        </div>
        <a href="{{ route('admin.subscriptions.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            <i class="fas fa-plus mr-1"></i> Add Subscription
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Started</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expires</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($subscriptions as $subscription)
                    <tr>
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.users.show', $subscription->user->id) }}" class="text-blue-600 hover:text-blue-800">
                                {{ $subscription->user->name }}
                            </a>
                        </td>
                        <td class="px-6 py-4">{{ $subscription->plan->name }}</td>
                        <td class="px-6 py-4">${{ number_format($subscription->plan->price, 2) }}</td>
                        <td class="px-6 py-4">{{ $subscription->started_at->format('M d, Y') }}</td>
                        <td class="px-6 py-4">
                            {{ $subscription->expires_at->format('M d, Y') }}
                            @if($subscription->isActive())
                                <span class="text-xs text-gray-500">({{ $subscription->days_remaining }} days left)</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($subscription->status === 'active' && !$subscription->isExpired())
                                <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-800">Active</span>
                            @elseif($subscription->status === 'cancelled')
                                <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-800">Cancelled</span>
                            @else
                                <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-800">Expired</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex space-x-2">
                                @if($subscription->status === 'active' && !$subscription->isExpired())
                                    <form action="{{ route('admin.subscriptions.cancel', $subscription->id) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                        @csrf
                                        <button type="submit" class="text-red-600 hover:text-red-800">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.subscriptions.renew', $subscription->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-green-600 hover:text-green-800">
                                            <i class="fas fa-redo"></i> Renew
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">No subscriptions found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-6 py-4">
        {{ $subscriptions->links() }}
    </div>
</div>
@endsection
