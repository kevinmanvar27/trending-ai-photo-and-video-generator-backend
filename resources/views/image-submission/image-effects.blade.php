<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ setting('site_title', config('app.name')) }} - All Image Effects</title>
    
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
        
        @keyframes shimmer {
            0% {
                background-position: -1000px 0;
            }
            100% {
                background-position: 1000px 0;
            }
        }
        
        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out forwards;
        }
        
        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .card-hover:hover {
            transform: translateY(-8px);
        }
        
        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 1000px 100%;
            animation: shimmer 2s infinite;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-slate-50 min-h-screen">
    @include('partials.header')

    <!-- Main Content -->
    <main class="container mx-auto px-4 lg:px-8 py-12">
        <!-- Back Button -->
        <div class="mb-8 animate-fade-in-up">
            <a href="{{ route('image-submission.index') }}" 
               class="inline-flex items-center space-x-2 text-blue-600 hover:text-blue-800 font-medium transition-all duration-300 bg-white px-6 py-3 rounded-full shadow-lg hover:shadow-xl group">
                <i class="fas fa-arrow-left transform group-hover:-translate-x-1 transition-transform duration-300"></i>
                <span>Back to All Effects</span>
            </a>
        </div>

        <!-- Page Header -->
        <div class="mb-12 text-center animate-fade-in-up" style="animation-delay: 0.1s;">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-blue-500 to-blue-600 rounded-3xl shadow-2xl mb-6">
                <i class="fas fa-image text-white text-3xl"></i>
            </div>
            <h1 class="text-5xl font-bold bg-gradient-to-r from-blue-600 via-cyan-600 to-blue-600 bg-clip-text text-transparent mb-4">
                All Image Effects
            </h1>
            <p class="text-gray-600 text-lg max-w-2xl mx-auto">
                Browse our collection of AI-powered image effects and transform your photos into stunning masterpieces
            </p>
        </div>

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

        <!-- Templates Grid -->
        @if($templates->count() > 0)
        <div id="templates-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8 mb-12">
            @foreach($templates as $index => $template)
                <div class="animate-fade-in-up card-hover" style="animation-delay: {{ $index * 0.05 }}s;">
                    @include('image-submission.partials.template-card', ['template' => $template])
                </div>
            @endforeach
        </div>

        <!-- Load More Button -->
        @if($templates->hasMorePages())
        <div class="text-center mb-12">
            <button id="load-more-btn" 
                    data-page="2" 
                    data-url="{{ route('image-submission.image-effects') }}"
                    class="bg-gradient-to-r from-blue-600 to-blue-600 hover:from-blue-700 hover:to-blue-700 text-white px-12 py-4 rounded-full font-bold text-lg transition-all duration-300 shadow-2xl hover:shadow-blue-500/50 inline-flex items-center space-x-3 transform hover:scale-105">
                <i class="fas fa-plus-circle text-xl"></i>
                <span>Load More Effects</span>
            </button>
            <div id="loading-spinner" class="hidden mt-8">
                <div class="inline-flex items-center space-x-3 bg-white px-8 py-4 rounded-full shadow-xl">
                    <i class="fas fa-spinner fa-spin text-blue-600 text-2xl"></i>
                    <p class="text-gray-700 font-medium">Loading more effects...</p>
                </div>
            </div>
        </div>
        @endif

        @else
        <!-- No Templates Available -->
        <div class="bg-white rounded-3xl shadow-2xl p-16 text-center animate-fade-in-up max-w-2xl mx-auto">
            <div class="w-32 h-32 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-inbox text-gray-400 text-6xl"></i>
            </div>
            <h2 class="text-3xl font-bold text-gray-800 mb-4">No Image Effects Available</h2>
            <p class="text-gray-600 mb-8 text-lg">There are currently no active image effects. Please check back later for exciting new effects!</p>
            <a href="{{ route('image-submission.index') }}" 
               class="inline-flex items-center space-x-2 bg-gradient-to-r from-blue-600 to-blue-600 hover:from-blue-700 hover:to-blue-700 text-white px-8 py-4 rounded-full font-bold transition-all duration-300 shadow-xl hover:shadow-blue-500/50">
                <i class="fas fa-arrow-left"></i>
                <span>Back to All Effects</span>
            </a>
        </div>
        @endif
    </main>

    @include('partials.footer')

    <!-- Load More JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loadMoreBtn = document.getElementById('load-more-btn');
            const loadingSpinner = document.getElementById('loading-spinner');
            const templatesGrid = document.getElementById('templates-grid');

            if (loadMoreBtn) {
                loadMoreBtn.addEventListener('click', function() {
                    const page = parseInt(this.getAttribute('data-page'));
                    const url = this.getAttribute('data-url');

                    // Show loading spinner and hide button
                    loadMoreBtn.classList.add('hidden');
                    loadingSpinner.classList.remove('hidden');

                    // Fetch more templates
                    fetch(`${url}?page=${page}`, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Append new templates to grid
                        templatesGrid.insertAdjacentHTML('beforeend', data.html);

                        // Hide loading spinner
                        loadingSpinner.classList.add('hidden');

                        // Update button state
                        if (data.has_more) {
                            loadMoreBtn.setAttribute('data-page', data.next_page);
                            loadMoreBtn.classList.remove('hidden');
                        } else {
                            // No more pages, show message
                            loadingSpinner.innerHTML = '<div class="inline-flex items-center space-x-3 bg-white px-8 py-4 rounded-full shadow-xl"><i class="fas fa-check-circle text-green-500 text-2xl"></i><p class="text-gray-700 font-medium">All effects loaded!</p></div>';
                            loadingSpinner.classList.remove('hidden');
                        }
                    })
                    .catch(error => {
                        console.error('Error loading more templates:', error);
                        loadingSpinner.classList.add('hidden');
                        loadMoreBtn.classList.remove('hidden');
                        alert('Failed to load more effects. Please try again.');
                    });
                });
            }
        });
    </script>
</body>
</html>
