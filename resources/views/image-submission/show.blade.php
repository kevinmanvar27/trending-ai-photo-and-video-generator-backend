<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $siteTitle }} - {{ $submission->template->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @if($submission->status !== 'completed' && $submission->status !== 'failed')
    <meta http-equiv="refresh" content="5">
    @endif
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <header class="bg-white shadow-md">
        <div class="container mx-auto px-4 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <a href="{{ route('image-submission.index') }}" class="text-blue-500 hover:text-blue-600 mr-4">
                        <i class="fas fa-arrow-left text-xl"></i>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Processing Result</h1>
                        <p class="text-gray-600 mt-1">Submission #{{ $submission->id }} - {{ $submission->template->title }}</p>
                    </div>
                </div>
                @auth
                <div class="flex items-center space-x-4">
                    <span class="text-gray-700">Welcome, <span class="font-semibold">{{ auth()->user()->name }}</span></span>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                            <i class="fas fa-sign-out-alt mr-2"></i>
                            Logout
                        </button>
                    </form>
                </div>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            
            <!-- Status Card -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                @if($submission->status === 'completed')
                    <!-- Completed Status -->
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-check-circle text-green-500 text-2xl"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">Processing Complete!</h2>
                            <p class="text-gray-600">Your image has been successfully processed</p>
                        </div>
                    </div>
                @elseif($submission->status === 'failed')
                    <!-- Failed Status -->
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-times-circle text-red-500 text-2xl"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">Processing Failed</h2>
                            <p class="text-gray-600">There was an error processing your image</p>
                        </div>
                    </div>
                    @if($submission->error_message)
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mt-4">
                        <p class="text-sm text-red-800">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <strong>Error:</strong> {{ $submission->error_message }}
                        </p>
                    </div>
                    @endif
                @elseif($submission->status === 'processing')
                    <!-- Processing Status -->
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-spinner fa-spin text-blue-500 text-2xl"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">Processing Your Image...</h2>
                            <p class="text-gray-600">This usually takes 10-30 seconds</p>
                        </div>
                    </div>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <p class="text-sm text-blue-800">
                            <i class="fas fa-info-circle mr-2"></i>
                            This page will automatically refresh. Please don't close this window.
                        </p>
                    </div>
                @else
                    <!-- Pending Status -->
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-clock text-yellow-500 text-2xl"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">Queued for Processing</h2>
                            <p class="text-gray-600">Your image is in the queue and will be processed shortly</p>
                        </div>
                    </div>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <p class="text-sm text-yellow-800">
                            <i class="fas fa-info-circle mr-2"></i>
                            This page will automatically refresh. Please wait...
                        </p>
                    </div>
                @endif

                <!-- Processing Time -->
                @if($submission->processing_started_at && $submission->processing_completed_at)
                <div class="mt-4 text-sm text-gray-600">
                    <i class="fas fa-stopwatch mr-1"></i>
                    Processing time: {{ $submission->processing_started_at->diffInSeconds($submission->processing_completed_at) }} seconds
                </div>
                @endif
            </div>

            <!-- Images/Videos Comparison -->
            @if($submission->status === 'completed')
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Original Image -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">
                        <i class="fas fa-image mr-2 text-gray-500"></i>
                        Original Image
                    </h3>
                    <div class="border border-gray-300 rounded-lg overflow-hidden">
                        <img src="{{ Storage::url($submission->original_image_path) }}" 
                             alt="Original" 
                             class="w-full h-auto">
                    </div>
                </div>

                <!-- AI Processing Result -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">
                        <i class="fas fa-wand-magic-sparkles mr-2 text-blue-500"></i>
                        AI Processing Result
                    </h3>
                    <div class="border border-gray-300 rounded-lg overflow-hidden bg-gradient-to-br from-blue-50 to-purple-50">
                        @if($submission->processed_image_path)
                            @php
                                $extension = pathinfo($submission->processed_image_path, PATHINFO_EXTENSION);
                                $isTextFile = in_array($extension, ['txt', 'text']);
                                $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                $isVideo = in_array($extension, ['mp4', 'mov', 'avi', 'webm']);
                            @endphp
                            
                            @if($isImage)
                                <!-- Display processed image -->
                                <img src="{{ Storage::url($submission->processed_image_path) }}" 
                                     alt="Processed Result" 
                                     class="w-full h-auto">
                            @elseif($isVideo)
                                <!-- Display processed video -->
                                <video controls class="w-full h-auto">
                                    <source src="{{ Storage::url($submission->processed_image_path) }}" type="video/{{ $extension }}">
                                    Your browser does not support the video tag.
                                </video>
                            @elseif($isTextFile)
                                <!-- Display text analysis -->
                                @php
                                    $resultPath = storage_path('app/public/' . $submission->processed_image_path);
                                    $resultText = file_exists($resultPath) ? file_get_contents($resultPath) : 'Result not available';
                                @endphp
                                <div class="p-6">
                                    <div class="prose max-w-none">
                                        <p class="text-gray-800 leading-relaxed whitespace-pre-wrap">{{ $resultText }}</p>
                                    </div>
                                </div>
                            @else
                                <div class="p-8 flex items-center justify-center">
                                    <p class="text-gray-500">Unsupported file format</p>
                                </div>
                            @endif
                        @else
                        <div class="bg-gray-100 p-8 rounded-lg flex items-center justify-center">
                            <p class="text-gray-500">No result available</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex flex-col sm:flex-row gap-3">
                    @if($submission->processed_image_path)
                    <a href="{{ route('image-submission.download', $submission) }}" 
                       class="flex-1 bg-green-500 hover:bg-green-600 text-white py-4 rounded-lg font-medium text-center transition-colors duration-200">
                        <i class="fas fa-download mr-2"></i>
                        Download AI Analysis
                    </a>
                    @endif
                    <a href="{{ route('image-submission.create', ['template' => $submission->template_id]) }}" 
                       class="flex-1 bg-blue-500 hover:bg-blue-600 text-white py-4 rounded-lg font-medium text-center transition-colors duration-200">
                        <i class="fas fa-redo mr-2"></i>
                        Analyze Another Image
                    </a>
                    <a href="{{ route('image-submission.index') }}" 
                       class="flex-1 bg-gray-500 hover:bg-gray-600 text-white py-4 rounded-lg font-medium text-center transition-colors duration-200">
                        <i class="fas fa-home mr-2"></i>
                        Back to Templates
                    </a>
                </div>
            </div>
            @elseif($submission->status === 'failed')
            <!-- Failed - Retry Options -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('image-submission.create', ['template' => $submission->template_id]) }}" 
                       class="flex-1 bg-blue-500 hover:bg-blue-600 text-white py-4 rounded-lg font-medium text-center transition-colors duration-200">
                        <i class="fas fa-redo mr-2"></i>
                        Try Again
                    </a>
                    <a href="{{ route('image-submission.index') }}" 
                       class="flex-1 bg-gray-500 hover:bg-gray-600 text-white py-4 rounded-lg font-medium text-center transition-colors duration-200">
                        <i class="fas fa-home mr-2"></i>
                        Back to Templates
                    </a>
                </div>
            </div>
            @endif

        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white mt-12 py-6 border-t border-gray-200">
        <div class="container mx-auto px-4 text-center text-gray-600 text-sm">
            <p>&copy; {{ date('Y') }} {{ $siteTitle }}. {{ $footerText }}</p>
        </div>
    </footer>
</body>
</html>
