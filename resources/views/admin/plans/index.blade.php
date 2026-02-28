@extends('admin.layout')

@section('title', 'Subscription Plans')
@section('header', 'Subscription Plans')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <h3 class="text-lg font-semibold">All Plans</h3>
        <a href="{{ route('admin.plans.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            <i class="fas fa-plus mr-1"></i> Add Plan
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
        @forelse($plans as $plan)
            <div class="border rounded-lg p-6 {{ $plan->is_active ? 'border-green-500' : 'border-gray-300' }}">
                <div class="flex justify-between items-start mb-4">
                    <h4 class="text-xl font-bold">{{ $plan->name }}</h4>
                    @if($plan->is_active)
                        <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-800">Active</span>
                    @else
                        <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-800">Inactive</span>
                    @endif
                </div>

                <div class="mb-4">
                    <p class="text-3xl font-bold">${{ number_format($plan->price, 2) }}</p>
                    <p class="text-sm text-gray-600">{{ $plan->formatted_duration }}</p>
                </div>

                @if($plan->description)
                    <p class="text-gray-600 mb-4">{{ $plan->description }}</p>
                @endif

                @if($plan->features && is_array($plan->features) && count($plan->features) > 0)
                    <ul class="mb-4 space-y-2">
                        @foreach($plan->features as $feature)
                            <li class="flex items-center text-sm">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                {{ $feature }}
                            </li>
                        @endforeach
                    </ul>
                @endif

                <div class="mb-4">
                    <p class="text-sm text-gray-600">
                        Active Subscriptions: <span class="font-semibold">{{ $plan->subscriptions_count }}</span>
                    </p>
                </div>

                <div class="flex space-x-2">
                    <a href="{{ route('admin.plans.edit', $plan->id) }}" class="flex-1 text-center bg-blue-500 text-white px-3 py-2 rounded hover:bg-blue-600 text-sm">
                        Edit
                    </a>
                    <form action="{{ route('admin.plans.toggle-status', $plan->id) }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full bg-yellow-500 text-white px-3 py-2 rounded hover:bg-yellow-600 text-sm">
                            {{ $plan->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                    <form action="{{ route('admin.plans.destroy', $plan->id) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-500 text-white px-3 py-2 rounded hover:bg-red-600 text-sm">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-3 text-center py-8 text-gray-500">
                No subscription plans found
            </div>
        @endforelse
    </div>
</div>
@endsection
