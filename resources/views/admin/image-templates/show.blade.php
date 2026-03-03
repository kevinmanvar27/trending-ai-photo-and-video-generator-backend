@extends('admin.layout')

@section('title', 'View Template')
@section('header', 'Template Details')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Template Info Card -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">{{ $template->title }}</h2>
                <div class="flex items-center space-x-4 text-sm text-gray-600">
                    <span>
                        <i class="fas fa-calendar mr-1"></i>
                        Created {{ $template->created_at->format('M d, Y') }}
                    </span>
                    <span>
                        <i class="fas fa-clock mr-1"></i>
                        Updated {{ $template->updated_at->diffForHumans() }}
                    </span>
                </div>
            </div>
            <div>
                @if($template->is_active)
                    <span class="px-4 py-2 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                        <i class="fas fa-check-circle mr-1"></i> Active
                    </span>
                @else
                    <span class="px-4 py-2 bg-gray-100 text-gray-800 rounded-full text-sm font-medium">
                        <i class="fas fa-times-circle mr-1"></i> Inactive
                    </span>
                @endif
            </div>
        </div>

        <!-- Description -->
        @if($template->description)
        <div class="mb-6">
            <h3 class="text-sm font-medium text-gray-700 mb-2">Description</h3>
            <p class="text-gray-600">{{ $template->description }}</p>
        </div>
        @endif

        <!-- Reference Media -->
        @if($template->reference_image_path)
        <div class="mb-6">
            <h3 class="text-sm font-medium text-gray-700 mb-3">Reference Media</h3>
            <div class="border border-gray-300 rounded-lg p-4 bg-gray-50">
                @php
                    $extension = strtolower(pathinfo($template->reference_image_path, PATHINFO_EXTENSION));
                    $isVideo = in_array($extension, ['mp4', 'mov', 'avi', 'webm']);
                @endphp
                
                @if($isVideo)
                    <video src="{{ Storage::url($template->reference_image_path) }}" 
                           controls
                           class="max-w-full h-auto rounded-lg mx-auto"
                           style="max-height: 500px;">
                        Your browser does not support the video tag.
                    </video>
                @else
                    <img src="{{ Storage::url($template->reference_image_path) }}" 
                         alt="{{ $template->title }}" 
                         class="max-w-full h-auto rounded-lg mx-auto"
                         style="max-height: 500px;">
                @endif
            </div>
        </div>
        @endif

        <!-- Prompt -->
        <div class="mb-6">
            <h3 class="text-sm font-medium text-gray-700 mb-2">AI Prompt</h3>
            <div class="bg-gray-50 border border-gray-300 rounded-lg p-4">
                <pre class="whitespace-pre-wrap text-sm text-gray-800 font-mono">{{ $template->prompt }}</pre>
            </div>
            <p class="text-xs text-gray-500 mt-2">
                <i class="fas fa-lock mr-1"></i> This prompt is hidden from users and automatically applied to their uploaded images
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="flex space-x-3">
            <a href="{{ route('admin.image-templates.edit', $template) }}" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white py-3 rounded-lg font-medium text-center">
                <i class="fas fa-edit mr-2"></i>
                Edit Template
            </a>
            <a href="{{ route('admin.image-templates.index') }}" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white py-3 rounded-lg font-medium text-center">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to List
            </a>
            <form action="{{ route('admin.image-templates.destroy', $template) }}" 
                  method="POST" 
                  class="flex-1"
                  onsubmit="return confirm('Are you sure you want to delete this template? This action cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white py-3 rounded-lg font-medium">
                    <i class="fas fa-trash mr-2"></i>
                    Delete Template
                </button>
            </form>
        </div>
    </div>

    <!-- Usage Statistics -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">
            <i class="fas fa-chart-bar mr-2"></i>
            Usage Statistics
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-blue-50 rounded-lg p-4 text-center">
                <div class="text-3xl font-bold text-blue-600">{{ $template->submissions()->count() }}</div>
                <div class="text-sm text-gray-600 mt-1">Total Submissions</div>
            </div>
            <div class="bg-green-50 rounded-lg p-4 text-center">
                <div class="text-3xl font-bold text-green-600">{{ $template->submissions()->where('status', 'completed')->count() }}</div>
                <div class="text-sm text-gray-600 mt-1">Completed</div>
            </div>
            <div class="bg-yellow-50 rounded-lg p-4 text-center">
                <div class="text-3xl font-bold text-yellow-600">{{ $template->submissions()->whereIn('status', ['pending', 'processing'])->count() }}</div>
                <div class="text-sm text-gray-600 mt-1">Processing</div>
            </div>
            <div class="bg-red-50 rounded-lg p-4 text-center">
                <div class="text-3xl font-bold text-red-600">{{ $template->submissions()->where('status', 'failed')->count() }}</div>
                <div class="text-sm text-gray-600 mt-1">Failed</div>
            </div>
        </div>

        <!-- Recent Submissions -->
        @if($template->submissions()->count() > 0)
        <h4 class="text-md font-semibold text-gray-700 mb-3">Recent Submissions</h4>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Submitted</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Processing Time</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($template->submissions()->latest()->take(10)->get() as $submission)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-900">#{{ $submission->id }}</td>
                        <td class="px-4 py-3">
                            @if($submission->status === 'completed')
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">
                                    <i class="fas fa-check mr-1"></i> Completed
                                </span>
                            @elseif($submission->status === 'processing')
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">
                                    <i class="fas fa-spinner mr-1"></i> Processing
                                </span>
                            @elseif($submission->status === 'failed')
                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">
                                    <i class="fas fa-times mr-1"></i> Failed
                                </span>
                            @else
                                <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs">
                                    <i class="fas fa-clock mr-1"></i> Pending
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $submission->created_at->diffForHumans() }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            @if($submission->processing_started_at && $submission->processing_completed_at)
                                {{ $submission->processing_started_at->diffInSeconds($submission->processing_completed_at) }}s
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-8 text-gray-500">
            <i class="fas fa-inbox text-4xl mb-2"></i>
            <p>No submissions yet</p>
        </div>
        @endif
    </div>
</div>
@endsection
