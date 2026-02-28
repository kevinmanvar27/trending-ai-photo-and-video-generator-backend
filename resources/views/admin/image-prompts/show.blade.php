@extends('admin.layout')

@section('title', 'View Image Prompt')
@section('header', 'Image Prompt Details')

@php
    use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.image-prompts.index') }}" class="text-blue-600 hover:text-blue-900">
            <i class="fas fa-arrow-left mr-2"></i>Back to Image Prompts
        </a>
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

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Original Image/Video -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold mb-4 flex items-center">
                <i class="fas fa-upload mr-2 text-blue-500"></i>
                Original {{ ucfirst($imagePrompt->file_type) }}
            </h3>
            <div class="bg-gray-100 rounded-lg p-4 flex items-center justify-center" style="min-height: 300px;">
                @if($imagePrompt->file_type === 'image')
                    <img src="{{ $imagePrompt->original_image_url }}" alt="Original Image" class="max-w-full h-auto rounded-lg shadow-md">
                @else
                    <video src="{{ $imagePrompt->original_image_url }}" controls class="max-w-full h-auto rounded-lg shadow-md"></video>
                @endif
            </div>
            <div class="mt-4">
                <a href="{{ $imagePrompt->original_image_url }}" download class="text-blue-600 hover:text-blue-900 text-sm">
                    <i class="fas fa-download mr-1"></i>Download Original
                </a>
            </div>
        </div>

        <!-- Processed Result -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold mb-4 flex items-center">
                <i class="fas fa-magic mr-2 text-purple-500"></i>
                AI Processing Result
            </h3>
            <div class="bg-gray-100 rounded-lg p-4" style="min-height: 300px;">
                @if($imagePrompt->status === 'completed')
                    @if($imagePrompt->processed_image_path)
                        @php
                            $extension = pathinfo($imagePrompt->processed_image_path, PATHINFO_EXTENSION);
                            $isTextFile = in_array($extension, ['txt', 'text']);
                            $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                            $isVideo = in_array($extension, ['mp4', 'mov', 'avi', 'webm']);
                        @endphp
                        
                        @if($isImage)
                            <!-- Display processed image -->
                            <div class="bg-white rounded overflow-hidden">
                                <img src="{{ Storage::url($imagePrompt->processed_image_path) }}" 
                                     alt="Processed Result" 
                                     class="w-full h-auto">
                            </div>
                        @elseif($isVideo)
                            <!-- Display processed video -->
                            <div class="bg-white rounded overflow-hidden">
                                <video controls class="w-full h-auto">
                                    <source src="{{ Storage::url($imagePrompt->processed_image_path) }}" type="video/{{ $extension }}">
                                    Your browser does not support the video tag.
                                </video>
                            </div>
                        @elseif($isTextFile)
                            <!-- Display text analysis -->
                            @php
                                $processedContent = Storage::disk('public')->get($imagePrompt->processed_image_path);
                            @endphp
                            <div class="bg-white rounded p-4 shadow-inner">
                                <h4 class="font-semibold mb-2 text-green-600">
                                    <i class="fas fa-check-circle mr-1"></i>Processing Complete
                                </h4>
                                <div class="text-gray-700 whitespace-pre-wrap">{{ $processedContent }}</div>
                            </div>
                        @else
                            <div class="text-center text-gray-500">
                                <i class="fas fa-question-circle text-4xl mb-2"></i>
                                <p>Unsupported file format</p>
                            </div>
                        @endif
                    @else
                        <div class="text-center text-gray-500">
                            <i class="fas fa-check-circle text-green-500 text-4xl mb-2"></i>
                            <p>Processing completed but no output file generated</p>
                        </div>
                    @endif
                @elseif($imagePrompt->status === 'processing')
                    <div class="text-center text-gray-500 flex flex-col items-center justify-center h-full">
                        <i class="fas fa-spinner fa-spin text-yellow-500 text-4xl mb-3"></i>
                        <p class="text-lg font-semibold">Processing in progress...</p>
                        <p class="text-sm mt-2">This may take a few moments</p>
                        <button onclick="location.reload()" class="mt-4 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            <i class="fas fa-sync-alt mr-1"></i>Refresh Status
                        </button>
                    </div>
                @elseif($imagePrompt->status === 'failed')
                    <div class="text-center text-red-500 flex flex-col items-center justify-center h-full">
                        <i class="fas fa-exclamation-triangle text-4xl mb-3"></i>
                        <p class="text-lg font-semibold mb-2">Processing Failed</p>
                        @if($imagePrompt->error_message)
                            <div class="bg-red-50 border border-red-200 rounded p-3 text-sm text-left max-w-md">
                                <p class="font-semibold mb-1">Error Details:</p>
                                <p class="text-red-700">{{ $imagePrompt->error_message }}</p>
                            </div>
                        @endif
                        <form action="{{ route('admin.image-prompts.reprocess', $imagePrompt->id) }}" method="POST" class="mt-4">
                            @csrf
                            <button type="submit" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">
                                <i class="fas fa-redo mr-1"></i>Retry Processing
                            </button>
                        </form>
                    </div>
                @else
                    <div class="text-center text-gray-500 flex flex-col items-center justify-center h-full">
                        <i class="fas fa-clock text-gray-400 text-4xl mb-3"></i>
                        <p class="text-lg font-semibold">Pending</p>
                        <p class="text-sm mt-2">Processing will start shortly</p>
                    </div>
                @endif
            </div>
            @if($imagePrompt->status === 'completed' && $imagePrompt->processed_image_path)
                <div class="mt-4">
                    <a href="{{ route('admin.image-prompts.download', $imagePrompt->id) }}" class="text-blue-600 hover:text-blue-900 text-sm">
                        <i class="fas fa-download mr-1"></i>Download Result
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Details Section -->
    <div class="bg-white rounded-lg shadow-lg p-6 mt-6">
        <h3 class="text-lg font-semibold mb-4">
            <i class="fas fa-info-circle mr-2 text-blue-500"></i>
            Details
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h4 class="text-sm font-semibold text-gray-600 mb-2">Prompt</h4>
                <div class="bg-gray-50 rounded p-3 border border-gray-200">
                    <p class="text-gray-800">{{ $imagePrompt->prompt }}</p>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <h4 class="text-sm font-semibold text-gray-600 mb-1">Status</h4>
                    <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full 
                        @if($imagePrompt->status === 'completed') bg-green-100 text-green-800
                        @elseif($imagePrompt->status === 'processing') bg-yellow-100 text-yellow-800
                        @elseif($imagePrompt->status === 'failed') bg-red-100 text-red-800
                        @else bg-gray-100 text-gray-800
                        @endif">
                        {{ ucfirst($imagePrompt->status) }}
                    </span>
                </div>

                <div>
                    <h4 class="text-sm font-semibold text-gray-600 mb-1">File Type</h4>
                    <p class="text-gray-800">{{ ucfirst($imagePrompt->file_type) }}</p>
                </div>

                <div>
                    <h4 class="text-sm font-semibold text-gray-600 mb-1">Uploaded By</h4>
                    <p class="text-gray-800">{{ $imagePrompt->user->name }}</p>
                </div>

                <div>
                    <h4 class="text-sm font-semibold text-gray-600 mb-1">Created At</h4>
                    <p class="text-gray-800">{{ $imagePrompt->created_at->format('M d, Y h:i A') }}</p>
                </div>

                @if($imagePrompt->processing_time)
                    <div>
                        <h4 class="text-sm font-semibold text-gray-600 mb-1">Processing Time</h4>
                        <p class="text-gray-800">{{ $imagePrompt->processing_time }} seconds</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="bg-white rounded-lg shadow-lg p-6 mt-6">
        <h3 class="text-lg font-semibold mb-4">
            <i class="fas fa-cog mr-2 text-gray-500"></i>
            Actions
        </h3>
        <div class="flex space-x-4">
            @if($imagePrompt->status === 'failed' || $imagePrompt->status === 'completed')
                <form action="{{ route('admin.image-prompts.reprocess', $imagePrompt->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded inline-flex items-center">
                        <i class="fas fa-redo mr-2"></i>
                        Reprocess
                    </button>
                </form>
            @endif

            <form action="{{ route('admin.image-prompts.destroy', $imagePrompt->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this image prompt? This action cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded inline-flex items-center">
                    <i class="fas fa-trash mr-2"></i>
                    Delete
                </button>
            </form>
        </div>
    </div>
</div>

@if($imagePrompt->status === 'processing')
<script>
    // Auto-refresh every 5 seconds if still processing
    setTimeout(() => {
        location.reload();
    }, 5000);
</script>
@endif
@endsection
