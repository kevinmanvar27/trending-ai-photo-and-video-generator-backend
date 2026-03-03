@extends('admin.layout')

@section('title', 'Create Subscription Plan')
@section('header', 'Create Subscription Plan')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('admin.plans.store') }}" method="POST">
            @csrf
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="name">
                    Plan Name
                </label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
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
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="price">
                    Price ($)
                </label>
                <input type="number" step="0.01" name="price" id="price" value="{{ old('price') }}" required
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('price') border-red-500 @enderror">
                @error('price')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="coins">
                    Coins
                </label>
                <input type="number" name="coins" id="coins" value="{{ old('coins', 0) }}" required
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('coins') border-red-500 @enderror">
                @error('coins')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">
                    Features (one per line)
                </label>
                <div id="features-container">
                    <div class="flex mb-2">
                        <input type="text" name="features[]" placeholder="Feature name"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                    </div>
                </div>
                <button type="button" onclick="addFeature()" class="text-blue-500 text-sm hover:text-blue-700">
                    + Add Feature
                </button>
            </div>

            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="mr-2">
                    <span class="text-sm text-gray-700">Active</span>
                </label>
            </div>

            <div class="flex items-center justify-between">
                <a href="{{ route('admin.plans.index') }}" class="text-gray-600 hover:text-gray-800">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Create Plan
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
