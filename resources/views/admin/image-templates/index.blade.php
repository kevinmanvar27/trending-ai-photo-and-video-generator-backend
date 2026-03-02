@extends('admin.layout')

@section('title', 'Image Effect Templates')
@section('header', 'Image Effect Templates')

@section('content')
<div class="mb-6">
    <div class="flex justify-between items-center">
        <div>
            <p class="text-gray-600">Create templates with prompts that users can apply to their images</p>
        </div>
        <a href="{{ route('admin.image-templates.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg inline-flex items-center">
            <i class="fas fa-plus mr-2"></i>
            Create New Template
        </a>
    </div>
</div>

@if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        {{ session('error') }}
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($templates as $template)
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Reference Image -->
            <div class="h-48 bg-gray-200 flex items-center justify-center overflow-hidden">
                @if($template->reference_image_path)
                    <img src="{{ $template->reference_image_url }}" alt="{{ $template->title }}" class="w-full h-full object-cover">
                @else
                    <div class="text-center text-gray-400">
                        <i class="fas fa-image text-6xl mb-2"></i>
                        <p class="text-sm">No reference image</p>
                    </div>
                @endif
            </div>

            <!-- Template Info -->
            <div class="p-4">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $template->title }}</h3>
                        <span class="inline-block mt-1 px-2 py-1 {{ $template->type == 'video' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }} rounded-full text-xs font-semibold">
                            <i class="fas fa-{{ $template->type == 'video' ? 'video' : 'image' }} mr-1"></i>
                            {{ ucfirst($template->type) }}
                        </span>
                    </div>
                    <form action="{{ route('admin.image-templates.toggle-status', $template->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-sm">
                            @if($template->is_active)
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">Active</span>
                            @else
                                <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs font-semibold">Inactive</span>
                            @endif
                        </button>
                    </form>
                </div>

                @if($template->description)
                    <p class="text-sm text-gray-600 mb-3">{{ Str::limit($template->description, 100) }}</p>
                @endif

                <!-- Prompt Preview -->
                <div class="bg-gray-50 rounded p-2 mb-3">
                    <p class="text-xs text-gray-500 mb-1">Prompt:</p>
                    <p class="text-sm text-gray-700 line-clamp-2">{{ Str::limit($template->prompt, 80) }}</p>
                </div>

                <!-- Stats -->
                <div class="flex items-center justify-between text-sm text-gray-500 mb-3">
                    <span><i class="fas fa-users mr-1"></i> {{ $template->submissions_count }} uses</span>
                    <span><i class="fas fa-clock mr-1"></i> {{ $template->created_at->diffForHumans() }}</span>
                </div>

                <!-- Coins Required -->
                <div class="flex items-center text-sm mb-3">
                    @if($template->coins_required > 0)
                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold">
                            <i class="fas fa-coins mr-1"></i> {{ $template->coins_required }} coins
                        </span>
                    @else
                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">
                            <i class="fas fa-gift mr-1"></i> Free
                        </span>
                    @endif
                </div>

                <!-- Actions -->
                <div class="flex space-x-2">
                    <a href="{{ route('admin.image-templates.show', $template->id) }}" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white text-center py-2 rounded text-sm">
                        <i class="fas fa-eye mr-1"></i> View
                    </a>
                    <a href="{{ route('admin.image-templates.edit', $template->id) }}" class="flex-1 bg-yellow-500 hover:bg-yellow-600 text-white text-center py-2 rounded text-sm">
                        <i class="fas fa-edit mr-1"></i> Edit
                    </a>
                    <form action="{{ route('admin.image-templates.destroy', $template->id) }}" method="POST" class="flex-1" onsubmit="return confirm('Are you sure?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white py-2 rounded text-sm">
                            <i class="fas fa-trash mr-1"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-full bg-white rounded-lg shadow p-8 text-center">
            <i class="fas fa-image text-gray-300 text-6xl mb-4"></i>
            <p class="text-gray-500 mb-4">No templates created yet.</p>
            <a href="{{ route('admin.image-templates.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg inline-flex items-center">
                <i class="fas fa-plus mr-2"></i>
                Create Your First Template
            </a>
        </div>
    @endforelse
</div>

@if($templates->hasPages())
    <div class="mt-6">
        {{ $templates->links() }}
    </div>
@endif
@endsection
