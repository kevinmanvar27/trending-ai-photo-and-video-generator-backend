<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ setting('site_title', config('app.name')) }} - Choose a Template</title>
    
    @if(setting('site_favicon'))
        <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . setting('site_favicon')) }}">
    @endif
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @if(setting('header_code'))
        {!! setting('header_code') !!}
    @endif
    
    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }
        
        @keyframes gradient {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }
        
        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out forwards;
        }
        
        .animate-float {
            animation: float 3s ease-in-out infinite;
        }
        
        .animate-gradient {
            background-size: 200% 200%;
            animation: gradient 5s ease infinite;
        }
        
        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .card-hover:hover {
            transform: translateY(-8px);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-slate-50 min-h-screen">
    @include('partials.header')

    <!-- Main Content -->
    <main class="container mx-auto px-4 lg:px-8 py-12">
        @if(session('success'))
        <div class="bg-gradient-to-r from-green-500 to-emerald-500 text-white px-6 py-4 rounded-2xl mb-8 shadow-lg animate-fade-in-up flex items-center space-x-3">
            <i class="fas fa-check-circle text-2xl"></i>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
        @endif

        @if(session('error'))
        <div class="bg-gradient-to-r from-red-500 to-cyan-500 text-white px-6 py-4 rounded-2xl mb-8 shadow-lg animate-fade-in-up flex items-center space-x-3">
            <i class="fas fa-exclamation-circle text-2xl"></i>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
        @endif

        <!-- Hero Section -->
        <div class="text-center mb-16 animate-fade-in-up">
            <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-blue-500 via-cyan-500 to-blue-600 rounded-3xl shadow-2xl mb-6 animate-float">
                <i class="fas fa-magic text-white text-4xl"></i>
            </div>
            <h1 class="text-6xl font-bold bg-gradient-to-r from-blue-600 via-cyan-600 to-blue-600 bg-clip-text text-transparent mb-4 animate-gradient">
                AI-Powered Effects
            </h1>
            <p class="text-gray-600 text-xl max-w-3xl mx-auto leading-relaxed">
                Transform your media with cutting-edge AI technology. Choose from our collection of stunning effects and watch the magic happen.
            </p>
        </div>

        <!-- Instructions Card -->
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-3xl p-1 mb-16 shadow-2xl animate-fade-in-up max-w-4xl mx-auto" style="animation-delay: 0.1s;">
            <div class="bg-white rounded-3xl p-8">
                <h2 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-blue-600 bg-clip-text text-transparent mb-6 flex items-center justify-center space-x-3">
                    <i class="fas fa-lightbulb"></i>
                    <span>How It Works</span>
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="text-center group">
                        <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg group-hover:scale-110 transition-transform duration-300">
                            <span class="text-white text-2xl font-bold">1</span>
                        </div>
                        <h3 class="font-bold text-gray-800 mb-2">Choose Effect</h3>
                        <p class="text-gray-600 text-sm">Select your favorite template</p>
                    </div>
                    <div class="text-center group">
                        <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg group-hover:scale-110 transition-transform duration-300">
                            <span class="text-white text-2xl font-bold">2</span>
                        </div>
                        <h3 class="font-bold text-gray-800 mb-2">Upload Media</h3>
                        <p class="text-gray-600 text-sm">Upload your image or video</p>
                    </div>
                    <div class="text-center group">
                        <div class="w-16 h-16 bg-gradient-to-br from-cyan-500 to-cyan-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg group-hover:scale-110 transition-transform duration-300">
                            <span class="text-white text-2xl font-bold">3</span>
                        </div>
                        <h3 class="font-bold text-gray-800 mb-2">AI Processing</h3>
                        <p class="text-gray-600 text-sm">Let AI work its magic</p>
                    </div>
                    <div class="text-center group">
                        <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg group-hover:scale-110 transition-transform duration-300">
                            <span class="text-white text-2xl font-bold">4</span>
                        </div>
                        <h3 class="font-bold text-gray-800 mb-2">Download</h3>
                        <p class="text-gray-600 text-sm">Get your transformed result</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Templates Grid -->
        @if($imageTemplates->count() > 0 || $videoTemplates->count() > 0)
        
        <!-- Image Templates Section -->
        @if($imageTemplates->count() > 0)
        <div class="mb-20 animate-fade-in-up" style="animation-delay: 0.2s;">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-4xl font-bold text-gray-800 mb-2 flex items-center space-x-3">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-image text-white text-xl"></i>
                        </div>
                        <span>Image Effects</span>
                    </h2>
                    <p class="text-gray-600 text-lg">Transform your images with AI-powered effects</p>
                </div>
                @if($hasMoreImages)
                <a href="{{ route('image-submission.image-effects') }}" 
                   class="hidden md:inline-flex items-center space-x-2 bg-gradient-to-r from-blue-600 to-blue-600 hover:from-blue-700 hover:to-blue-700 text-white px-6 py-3 rounded-full font-bold transition-all duration-300 shadow-lg hover:shadow-xl">
                    <span>View All</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
                @endif
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                @foreach($imageTemplates as $index => $template)
                <div class="card-hover" style="animation: fadeInUp 0.6s ease-out forwards; animation-delay: {{ $index * 0.05 }}s; opacity: 0;">
                    @include('image-submission.partials.template-card', ['template' => $template])
                </div>
                @endforeach
            </div>

            <!-- Mobile View All Button -->
            @if($hasMoreImages)
            <div class="text-center mt-10 md:hidden">
                <a href="{{ route('image-submission.image-effects') }}" 
                   class="inline-flex items-center space-x-2 bg-gradient-to-r from-blue-600 to-blue-600 hover:from-blue-700 hover:to-blue-700 text-white px-8 py-4 rounded-full font-bold transition-all duration-300 shadow-xl hover:shadow-2xl">
                    <i class="fas fa-plus-circle text-xl"></i>
                    <span>Load More Image Effects</span>
                </a>
            </div>
            @endif
        </div>
        @endif

        <!-- Video Templates Section -->
        @if($videoTemplates->count() > 0)
        <div class="mb-20 animate-fade-in-up" style="animation-delay: 0.3s;">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-4xl font-bold text-gray-800 mb-2 flex items-center space-x-3">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-2xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-video text-white text-xl"></i>
                        </div>
                        <span>Video Effects</span>
                    </h2>
                    <p class="text-gray-600 text-lg">Transform your videos with AI-powered effects</p>
                </div>
                @if($hasMoreVideos)
                <a href="{{ route('image-submission.video-effects') }}" 
                   class="hidden md:inline-flex items-center space-x-2 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 text-white px-6 py-3 rounded-full font-bold transition-all duration-300 shadow-lg hover:shadow-xl">
                    <span>View All</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
                @endif
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                @foreach($videoTemplates as $index => $template)
                <div class="card-hover" style="animation: fadeInUp 0.6s ease-out forwards; animation-delay: {{ $index * 0.05 }}s; opacity: 0;">
                    @include('image-submission.partials.template-card', ['template' => $template])
                </div>
                @endforeach
            </div>

            <!-- Mobile View All Button -->
            @if($hasMoreVideos)
            <div class="text-center mt-10 md:hidden">
                <a href="{{ route('image-submission.video-effects') }}" 
                   class="inline-flex items-center space-x-2 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 text-white px-8 py-4 rounded-full font-bold transition-all duration-300 shadow-xl hover:shadow-2xl">
                    <i class="fas fa-plus-circle text-xl"></i>
                    <span>Load More Video Effects</span>
                </a>
            </div>
            @endif
        </div>
        @endif

        @else
        <!-- No Templates Available -->
        <div class="bg-white rounded-3xl shadow-2xl p-16 text-center animate-fade-in-up max-w-2xl mx-auto">
            <div class="w-32 h-32 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-inbox text-gray-400 text-6xl"></i>
            </div>
            <h2 class="text-3xl font-bold text-gray-800 mb-4">No Templates Available</h2>
            <p class="text-gray-600 text-lg mb-8">There are currently no active image processing templates. Please check back later for exciting new effects!</p>
            <div class="inline-flex items-center space-x-3 bg-gradient-to-r from-blue-100 to-cyan-100 px-6 py-3 rounded-full">
                <i class="fas fa-clock text-blue-600"></i>
                <span class="text-gray-700 font-medium">Coming Soon</span>
            </div>
        </div>
        @endif
    </main>

    @include('partials.footer')
</body>
</html>
