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

<!-- Filters Section -->
<div class="bg-white rounded-lg shadow-lg p-4 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Search Filter -->
        <div>
            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <input type="text" 
                   id="search" 
                   name="search" 
                   placeholder="Search templates..." 
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                   value="{{ request('search') }}">
        </div>

        <!-- Type Filter -->
        <div>
            <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Template Type</label>
            <select id="type" 
                    name="type" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                <option value="">All Types</option>
                <option value="image" {{ request('type') == 'image' ? 'selected' : '' }}>Image</option>
                <option value="video" {{ request('type') == 'video' ? 'selected' : '' }}>Video</option>
            </select>
        </div>

        <!-- Status Filter -->
        <div>
            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select id="status" 
                    name="status" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                <option value="">All Status</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <!-- Filter Actions -->
        <div class="flex items-end space-x-2">
            <button type="button" 
                    id="applyFilters" 
                    class="flex-1 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-filter mr-1"></i> Apply
            </button>
            <button type="button" 
                    id="resetFilters" 
                    class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-redo mr-1"></i> Reset
            </button>
        </div>
    </div>
</div>

<!-- Templates Grid -->
<div id="templates-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @include('admin.image-templates.partials.template-cards', ['templates' => $templates])
</div>

<!-- Load More Button -->
@if($templates->hasMorePages())
    <div class="mt-6 text-center">
        <button type="button" 
                id="loadMoreBtn" 
                class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg inline-flex items-center"
                data-page="{{ $templates->currentPage() + 1 }}">
            <i class="fas fa-arrow-down mr-2"></i>
            Load More Templates
        </button>
    </div>
@endif

<!-- Loading Indicator -->
<div id="loadingIndicator" class="mt-6 text-center hidden">
    <div class="inline-flex items-center text-gray-600">
        <svg class="animate-spin h-5 w-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Loading templates...
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentPage = {{ $templates->currentPage() }};
    let isLoading = false;

    // Get filter values
    function getFilters() {
        return {
            search: document.getElementById('search').value,
            type: document.getElementById('type').value,
            status: document.getElementById('status').value
        };
    }

    // Apply filters
    function applyFilters() {
        if (isLoading) return;
        
        isLoading = true;
        const filters = getFilters();
        const loadingIndicator = document.getElementById('loadingIndicator');
        const container = document.getElementById('templates-container');
        
        loadingIndicator.classList.remove('hidden');
        
        // Build query string
        const params = new URLSearchParams({ ...filters, page: 1 });
        
        fetch(`{{ route('admin.image-templates.index') }}?${params.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            container.innerHTML = data.html;
            currentPage = 1;
            
            // Update load more button
            const loadMoreBtn = document.getElementById('loadMoreBtn');
            if (data.has_more) {
                if (!loadMoreBtn) {
                    const btnHtml = `
                        <div class="mt-6 text-center">
                            <button type="button" 
                                    id="loadMoreBtn" 
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg inline-flex items-center"
                                    data-page="${data.next_page}">
                                <i class="fas fa-arrow-down mr-2"></i>
                                Load More Templates
                            </button>
                        </div>
                    `;
                    loadingIndicator.insertAdjacentHTML('beforebegin', btnHtml);
                    attachLoadMoreListener();
                } else {
                    loadMoreBtn.setAttribute('data-page', data.next_page);
                    loadMoreBtn.closest('div').classList.remove('hidden');
                }
            } else if (loadMoreBtn) {
                loadMoreBtn.closest('div').classList.add('hidden');
            }
            
            loadingIndicator.classList.add('hidden');
            isLoading = false;
        })
        .catch(error => {
            console.error('Error:', error);
            loadingIndicator.classList.add('hidden');
            isLoading = false;
            alert('Failed to load templates. Please try again.');
        });
    }

    // Load more templates
    function loadMore() {
        if (isLoading) return;
        
        isLoading = true;
        const loadMoreBtn = document.getElementById('loadMoreBtn');
        const loadingIndicator = document.getElementById('loadingIndicator');
        const container = document.getElementById('templates-container');
        
        const nextPage = parseInt(loadMoreBtn.getAttribute('data-page'));
        const filters = getFilters();
        
        loadMoreBtn.disabled = true;
        loadMoreBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Loading...';
        
        // Build query string
        const params = new URLSearchParams({ ...filters, page: nextPage });
        
        fetch(`{{ route('admin.image-templates.index') }}?${params.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            // Append new templates
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = data.html;
            
            // Remove empty state if exists
            const emptyState = container.querySelector('.col-span-full');
            if (emptyState) {
                emptyState.remove();
            }
            
            // Append each template card
            Array.from(tempDiv.children).forEach(child => {
                if (child.classList.contains('template-card')) {
                    container.appendChild(child);
                }
            });
            
            currentPage = nextPage;
            
            // Update or hide load more button
            if (data.has_more) {
                loadMoreBtn.setAttribute('data-page', data.next_page);
                loadMoreBtn.disabled = false;
                loadMoreBtn.innerHTML = '<i class="fas fa-arrow-down mr-2"></i> Load More Templates';
            } else {
                loadMoreBtn.closest('div').remove();
            }
            
            isLoading = false;
        })
        .catch(error => {
            console.error('Error:', error);
            loadMoreBtn.disabled = false;
            loadMoreBtn.innerHTML = '<i class="fas fa-arrow-down mr-2"></i> Load More Templates';
            isLoading = false;
            alert('Failed to load more templates. Please try again.');
        });
    }

    // Attach load more listener
    function attachLoadMoreListener() {
        const loadMoreBtn = document.getElementById('loadMoreBtn');
        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', loadMore);
        }
    }

    // Event listeners
    document.getElementById('applyFilters').addEventListener('click', applyFilters);
    
    document.getElementById('resetFilters').addEventListener('click', function() {
        document.getElementById('search').value = '';
        document.getElementById('type').value = '';
        document.getElementById('status').value = '';
        applyFilters();
    });

    // Apply filters on Enter key in search
    document.getElementById('search').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            applyFilters();
        }
    });

    // Initial load more listener
    attachLoadMoreListener();
});
</script>
@endpush
@endsection

