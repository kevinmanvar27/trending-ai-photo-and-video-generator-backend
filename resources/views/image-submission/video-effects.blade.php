<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ setting('site_title', config('app.name')) }} - All Video Effects</title>
    
    @if(setting('site_favicon'))
        <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . setting('site_favicon')) }}">
    @endif
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @if(setting('header_code'))
        {!! setting('header_code') !!}
    @endif
</head>
<body class="bg-gray-100">
    @include('partials.header')

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('image-submission.index') }}" 
               class="inline-flex items-center text-purple-600 hover:text-purple-800 font-medium transition-colors duration-200">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to All Effects
            </a>
        </div>

        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2 flex items-center">
                <i class="fas fa-video mr-3 text-purple-500"></i>
                All Video Effects
            </h1>
            <p class="text-gray-600">Browse all available AI-powered video effects</p>
        </div>

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

        <!-- Templates Grid -->
        @if($templates->count() > 0)
        <div id="templates-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            @foreach($templates as $template)
                @include('image-submission.partials.template-card', ['template' => $template])
            @endforeach
        </div>

        <!-- Load More Button -->
        @if($templates->hasMorePages())
        <div class="text-center mb-8">
            <button id="load-more-btn" 
                    data-page="2" 
                    data-url="{{ route('image-submission.video-effects') }}"
                    class="bg-purple-500 hover:bg-purple-600 text-white px-8 py-3 rounded-lg font-medium transition-colors duration-200 inline-flex items-center">
                <i class="fas fa-plus-circle mr-2"></i>
                Load More
            </button>
            <div id="loading-spinner" class="hidden">
                <i class="fas fa-spinner fa-spin text-purple-500 text-3xl"></i>
                <p class="text-gray-600 mt-2">Loading more effects...</p>
            </div>
        </div>
        @endif

        @else
        <!-- No Templates Available -->
        <div class="bg-white rounded-lg shadow-lg p-12 text-center">
            <i class="fas fa-inbox text-gray-300 text-6xl mb-4"></i>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">No Video Effects Available</h2>
            <p class="text-gray-600 mb-6">There are currently no active video effects. Please check back later.</p>
            <a href="{{ route('image-submission.index') }}" 
               class="inline-block bg-purple-500 hover:bg-purple-600 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to All Effects
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
                            loadingSpinner.innerHTML = '<p class="text-gray-600">No more effects to load</p>';
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
