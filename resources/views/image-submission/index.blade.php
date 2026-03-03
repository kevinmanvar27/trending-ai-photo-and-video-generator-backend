<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $siteTitle }} - Choose a Template</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <header class="bg-white shadow-md">
        <div class="container mx-auto px-4 py-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">
                        <i class="fas fa-wand-magic-sparkles mr-2 text-blue-500"></i>
                        {{ $siteTitle }}
                    </h1>
                    <p class="text-gray-600 mt-2">{{ $siteDescription }}</p>
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
        @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
            <i class="fas fa-check-circle mr-2"></i>
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
            <i class="fas fa-exclamation-circle mr-2"></i>
            {{ session('error') }}
        </div>
        @endif

        <!-- Instructions -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
            <h2 class="text-xl font-semibold text-blue-900 mb-3">
                <i class="fas fa-info-circle mr-2"></i>
                How It Works
            </h2>
            <ol class="list-decimal list-inside space-y-2 text-blue-800">
                <li>Choose an effect template below (Image or Video)</li>
                <li>Upload your image or video</li>
                <li>Our AI will process your file with the selected effect</li>
                <li>Download your transformed result</li>
            </ol>
        </div>

        <!-- Templates Grid -->
        @if($templates->count() > 0)
        
        <!-- Image Templates Section -->
        @php
            $imageTemplates = $templates->where('type', 'image');
            $videoTemplates = $templates->where('type', 'video');
        @endphp

        @if($imageTemplates->count() > 0)
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-800 mb-2 flex items-center">
                <i class="fas fa-image mr-3 text-blue-500"></i>
                Image Effects
            </h2>
            <p class="text-gray-600 mb-6">Transform your images with AI-powered effects</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($imageTemplates as $template)
                <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                    <!-- Reference Media -->
                    @if($template->reference_image_path)
                    <div class="relative h-64 bg-gray-200">
                        @php
                            $extension = strtolower(pathinfo($template->reference_image_path, PATHINFO_EXTENSION));
                            $isVideo = in_array($extension, ['mp4', 'mov', 'avi', 'webm']);
                        @endphp
                        
                        @if($isVideo)
                            <video src="{{ Storage::url($template->reference_image_path) }}" 
                                   class="w-full h-full object-cover"
                                   muted
                                   loop
                                   onmouseover="this.play()" 
                                   onmouseout="this.pause()">
                            </video>
                        @else
                            <img src="{{ Storage::url($template->reference_image_path) }}" 
                                 alt="{{ $template->title }}" 
                                 class="w-full h-full object-cover">
                        @endif
                        <div class="absolute top-3 right-3">
                            <span class="px-3 py-1 bg-blue-500 text-white rounded-full text-xs font-medium">
                                <i class="fas fa-magic mr-1"></i> AI Effect
                            </span>
                        </div>
                    </div>
                    @else
                    <div class="h-64 bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center">
                        <i class="fas fa-image text-white text-6xl opacity-50"></i>
                    </div>
                    @endif

                    <!-- Template Info -->
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-2">{{ $template->title }}</h3>
                        
                        @if($template->description)
                        <p class="text-gray-600 text-sm mb-4">{{ $template->description }}</p>
                        @endif

                        <!-- Action Button -->
                        <a href="{{ route('image-submission.create', ['template' => $template->id]) }}" 
                           class="block w-full bg-blue-500 hover:bg-blue-600 text-white text-center py-3 rounded-lg font-medium transition-colors duration-200">
                            <i class="fas fa-upload mr-2"></i>
                            Use This Effect
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Video Templates Section -->
        @if($videoTemplates->count() > 0)
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-800 mb-2 flex items-center">
                <i class="fas fa-video mr-3 text-purple-500"></i>
                Video Effects
            </h2>
            <p class="text-gray-600 mb-6">Transform your videos with AI-powered effects</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($videoTemplates as $template)
                <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                    <!-- Reference Media -->
                    @if($template->reference_image_path)
                    <div class="relative h-64 bg-gray-200">
                        @php
                            $extension = strtolower(pathinfo($template->reference_image_path, PATHINFO_EXTENSION));
                            $isVideo = in_array($extension, ['mp4', 'mov', 'avi', 'webm']);
                        @endphp
                        
                        @if($isVideo)
                            <video src="{{ Storage::url($template->reference_image_path) }}" 
                                   class="w-full h-full object-cover"
                                   muted
                                   loop
                                   onmouseover="this.play()" 
                                   onmouseout="this.pause()">
                            </video>
                        @else
                            <img src="{{ Storage::url($template->reference_image_path) }}" 
                                 alt="{{ $template->title }}" 
                                 class="w-full h-full object-cover">
                        @endif
                        <div class="absolute top-3 right-3">
                            <span class="px-3 py-1 bg-purple-500 text-white rounded-full text-xs font-medium">
                                <i class="fas fa-video mr-1"></i> AI Effect
                            </span>
                        </div>
                    </div>
                    @else
                    <div class="h-64 bg-gradient-to-br from-purple-400 to-pink-500 flex items-center justify-center">
                        <i class="fas fa-video text-white text-6xl opacity-50"></i>
                    </div>
                    @endif

                    <!-- Template Info -->
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-2">{{ $template->title }}</h3>
                        
                        @if($template->description)
                        <p class="text-gray-600 text-sm mb-4">{{ $template->description }}</p>
                        @endif

                        <!-- Action Button -->
                        <a href="{{ route('image-submission.create', ['template' => $template->id]) }}" 
                           class="block w-full bg-purple-500 hover:bg-purple-600 text-white text-center py-3 rounded-lg font-medium transition-colors duration-200">
                            <i class="fas fa-upload mr-2"></i>
                            Use This Effect
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @else
        <!-- No Templates Available -->
        <div class="bg-white rounded-lg shadow-lg p-12 text-center">
            <i class="fas fa-inbox text-gray-300 text-6xl mb-4"></i>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">No Templates Available</h2>
            <p class="text-gray-600">There are currently no active image processing templates. Please check back later.</p>
        </div>
        @endif
    </main>

    <!-- Footer -->
    <footer class="bg-white mt-12 py-6 border-t border-gray-200">
        <div class="container mx-auto px-4 text-center text-gray-600 text-sm">
            <p>&copy; {{ date('Y') }} {{ $siteTitle }}. {{ $footerText }}</p>
        </div>
    </footer>
</body>
</html>
