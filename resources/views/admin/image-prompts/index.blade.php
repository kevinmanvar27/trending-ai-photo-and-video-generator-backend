@extends('admin.layout')

@section('title', 'Image Prompts')
@section('header', 'Image Prompts')

@section('content')
<div class="mb-6">
    <div class="flex justify-between items-center">
        <div>
            <p class="text-gray-600">Upload and process images with AI prompts</p>
        </div>
        <a href="{{ route('admin.image-prompts.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg inline-flex items-center">
            <i class="fas fa-plus mr-2"></i>
            Upload New Image
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

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Preview</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prompt</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($imagePrompts as $imagePrompt)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($imagePrompt->file_type === 'image')
                            <img src="{{ $imagePrompt->original_image_url }}" alt="Preview" class="h-16 w-16 object-cover rounded">
                        @else
                            <div class="h-16 w-16 bg-gray-200 rounded flex items-center justify-center">
                                <i class="fas fa-video text-gray-500 text-2xl"></i>
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-900 max-w-xs truncate" title="{{ $imagePrompt->prompt }}">
                            {{ Str::limit($imagePrompt->prompt, 50) }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $imagePrompt->file_type === 'image' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                            {{ ucfirst($imagePrompt->file_type) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($imagePrompt->status === 'completed')
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i> Completed
                            </span>
                        @elseif($imagePrompt->status === 'processing')
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                <i class="fas fa-spinner fa-spin mr-1"></i> Processing
                            </span>
                        @elseif($imagePrompt->status === 'failed')
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                <i class="fas fa-times-circle mr-1"></i> Failed
                            </span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                <i class="fas fa-clock mr-1"></i> Pending
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $imagePrompt->user->name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $imagePrompt->created_at->format('M d, Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{ route('admin.image-prompts.show', $imagePrompt->id) }}" class="text-blue-600 hover:text-blue-900 mr-3">
                            <i class="fas fa-eye"></i>
                        </a>
                        @if($imagePrompt->status === 'failed')
                            <form action="{{ route('admin.image-prompts.reprocess', $imagePrompt->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-yellow-600 hover:text-yellow-900 mr-3">
                                    <i class="fas fa-redo"></i>
                                </button>
                            </form>
                        @endif
                        <form action="{{ route('admin.image-prompts.destroy', $imagePrompt->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                        No image prompts found. <a href="{{ route('admin.image-prompts.create') }}" class="text-blue-600 hover:text-blue-900">Upload your first image</a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($imagePrompts->hasPages())
    <div class="mt-4">
        {{ $imagePrompts->links() }}
    </div>
@endif
@endsection
