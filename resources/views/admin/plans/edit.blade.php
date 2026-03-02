@extends('admin.layout')

@section('title', 'Edit Subscription Plan')
@section('header', 'Edit Subscription Plan')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('admin.plans.update', $plan->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="name">
                    Plan Name
                </label>
                <input type="text" name="name" id="name" value="{{ old('name', $plan->name) }}" required
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="description">
                    Description
                </label>
                <textarea name="description" id="description" rows="3"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('description') border-red-500 @enderror">{{ old('description', $plan->description) }}</textarea>
                @error('description')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="price">
                        Price ($)
                    </label>
                    <input type="number" step="0.01" name="price" id="price" value="{{ old('price', $plan->price) }}" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('price') border-red-500 @enderror">
                    @error('price')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="coins">
                        Coins
                    </label>
                    <input type="number" name="coins" id="coins" value="{{ old('coins', $plan->coins ?? 0) }}" required min="0"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('coins') border-red-500 @enderror">
                    @error('coins')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="duration_value">
                        Duration Value
                    </label>
                    <input type="number" name="duration_value" id="duration_value" value="{{ old('duration_value', $plan->duration_value) }}" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('duration_value') border-red-500 @enderror">
                    @error('duration_value')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="duration_type">
                        Duration Type
                    </label>
                    <select name="duration_type" id="duration_type" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('duration_type') border-red-500 @enderror">
                        <option value="daily" {{ old('duration_type', $plan->duration_type) == 'daily' ? 'selected' : '' }}>Daily</option>
                        <option value="weekly" {{ old('duration_type', $plan->duration_type) == 'weekly' ? 'selected' : '' }}>Weekly</option>
                        <option value="monthly" {{ old('duration_type', $plan->duration_type) == 'monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="yearly" {{ old('duration_type', $plan->duration_type) == 'yearly' ? 'selected' : '' }}>Yearly</option>
                    </select>
                    @error('duration_type')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">
                    Features (one per line)
                </label>
                <div id="features-container">
                    @if($plan->features && is_array($plan->features) && count($plan->features) > 0)
                        @foreach($plan->features as $feature)
                            <div class="flex mb-2">
                                <input type="text" name="features[]" value="{{ $feature }}" placeholder="Feature name"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                                <button type="button" onclick="this.parentElement.remove()" class="ml-2 text-red-500 hover:text-red-700">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        @endforeach
                    @else
                        <div class="flex mb-2">
                            <input type="text" name="features[]" placeholder="Feature name"
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                        </div>
                    @endif
                </div>
                <button type="button" onclick="addFeature()" class="text-blue-500 text-sm hover:text-blue-700">
                    + Add Feature
                </button>
            </div>

            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $plan->is_active) ? 'checked' : '' }} class="mr-2">
                    <span class="text-sm text-gray-700">Active</span>
                </label>
            </div>

            <div class="flex items-center justify-between">
                <a href="{{ route('admin.plans.index') }}" class="text-gray-600 hover:text-gray-800">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Update Plan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function addFeature() {
    const container = document.getElementById('features-container');
    const div = document.createElement('div');
    div.className = 'flex mb-2';
    div.innerHTML = `
        <input type="text" name="features[]" placeholder="Feature name"
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
        <button type="button" onclick="this.parentElement.remove()" class="ml-2 text-red-500 hover:text-red-700">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(div);
}
</script>
@endsection
