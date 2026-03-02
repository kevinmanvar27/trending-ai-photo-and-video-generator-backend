@extends('admin.layout')

@section('title', 'Edit Page')
@section('header', 'Edit Page')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <form action="{{ route('admin.pages.update', $page->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Title *</label>
            <input type="text" name="title" id="title" value="{{ old('title', $page->title) }}" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('title') border-red-500 @enderror" 
                   required>
            @error('title')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">Slug (URL)</label>
            <input type="text" name="slug" id="slug" value="{{ old('slug', $page->slug) }}" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('slug') border-red-500 @enderror"
                   placeholder="Leave empty to auto-generate from title">
            <p class="mt-1 text-xs text-gray-500">URL-friendly version of the title. Leave empty to auto-generate.</p>
            @error('slug')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="content" class="block text-sm font-medium text-gray-700 mb-2">Content *</label>
            <div id="editor" style="height: 400px; background: white;"></div>
            <textarea name="content" id="content" style="display:none;" required>{{ old('content', $page->content) }}</textarea>
            @error('content')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="meta_description" class="block text-sm font-medium text-gray-700 mb-2">Meta Description</label>
            <textarea name="meta_description" id="meta_description" rows="3" 
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('meta_description') border-red-500 @enderror"
                      placeholder="Brief description for SEO (max 500 characters)">{{ old('meta_description', $page->meta_description) }}</textarea>
            @error('meta_description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="meta_keywords" class="block text-sm font-medium text-gray-700 mb-2">Meta Keywords</label>
            <input type="text" name="meta_keywords" id="meta_keywords" value="{{ old('meta_keywords', $page->meta_keywords) }}" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('meta_keywords') border-red-500 @enderror"
                   placeholder="keyword1, keyword2, keyword3">
            <p class="mt-1 text-xs text-gray-500">Comma-separated keywords for SEO.</p>
            @error('meta_keywords')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="order" class="block text-sm font-medium text-gray-700 mb-2">Order</label>
            <input type="number" name="order" id="order" value="{{ old('order', $page->order) }}" min="0"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('order') border-red-500 @enderror">
            <p class="mt-1 text-xs text-gray-500">Lower numbers appear first in listings.</p>
            @error('order')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label class="flex items-center">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $page->is_active) ? 'checked' : '' }}
                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                <span class="ml-2 text-sm text-gray-700">Active (visible to users)</span>
            </label>
        </div>

        <div class="flex items-center justify-between">
            <a href="{{ route('admin.pages.index') }}" class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-arrow-left mr-2"></i>Back to Pages
            </a>
            <div class="space-x-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded">
                    <i class="fas fa-save mr-2"></i>Update Page
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Quill Rich Text Editor -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

<script>
    // Initialize Quill editor
    var quill = new Quill('#editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'align': [] }],
                ['link', 'image', 'video'],
                ['blockquote', 'code-block'],
                ['clean']
            ]
        },
        placeholder: 'Write your page content here...'
    });

    // Sync Quill content to hidden textarea
    quill.on('text-change', function() {
        document.getElementById('content').value = quill.root.innerHTML;
    });

    // Load existing content
    var existingContent = document.getElementById('content').value;
    if (existingContent) {
        quill.root.innerHTML = existingContent;
    }
</script>
@endsection
