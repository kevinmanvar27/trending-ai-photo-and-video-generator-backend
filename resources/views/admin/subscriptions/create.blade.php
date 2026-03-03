@extends('admin.layout')

@section('title', 'Create Subscription')
@section('header', 'Create Subscription')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('admin.subscriptions.store') }}" method="POST">
            @csrf
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="user_id">
                    User
                </label>
                <select name="user_id" id="user_id" required
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('user_id') border-red-500 @enderror">
                    <option value="">Select User</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
                @error('user_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="subscription_plan_id">
                    Subscription Plan
                </label>
                <select name="subscription_plan_id" id="subscription_plan_id" required
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('subscription_plan_id') border-red-500 @enderror">
                    <option value="">Select Plan</option>
                    @foreach($plans as $plan)
                        <option value="{{ $plan->id }}" {{ old('subscription_plan_id') == $plan->id ? 'selected' : '' }}>
                            {{ $plan->name }} - ${{ number_format($plan->price, 2) }} ({{ number_format($plan->coins) }} Coins)
                        </option>
                    @endforeach
                </select>
                @error('subscription_plan_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="started_at">
                    Start Date
                </label>
                <input type="date" name="started_at" id="started_at" value="{{ old('started_at', date('Y-m-d')) }}" required
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('started_at') border-red-500 @enderror">
                @error('started_at')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between">
                <a href="{{ route('admin.subscriptions.index') }}" class="text-gray-600 hover:text-gray-800">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Create Subscription
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
