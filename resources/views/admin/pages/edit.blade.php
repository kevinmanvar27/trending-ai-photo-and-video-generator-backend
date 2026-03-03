@extends('admin.layout')

@section('title', 'Edit Page')
@section('header', 'Edit Page')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <form action="{{ route('admin.pages.update', $page->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Page Title *</label>
            <input type="text" 
                   name="title" 
                   id="title" 
                   value="{{ old('title', $page->title) }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('title') border-red-500 @enderror"
                   required>
            @error('title')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">
                Slug 
                <span class="text-gray-500 text-xs">(Leave empty to auto-generate from title)</span>
            </label>
            <input type="text" 
                   name="slug" 
                   id="slug" 
                   value="{{ old('slug', $page->slug) }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('slug') border-red-500 @enderror">
            @error('slug')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="content" class="block text-sm font-medium text-gray-700 mb-2">Page Content *</label>
            <textarea name="content" 
                      id="content" 
                      rows="15"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('content') border-red-500 @enderror">{{ old('content', $page->content) }}</textarea>
            @error('content')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="meta_description" class="block text-sm font-medium text-gray-700 mb-2">Meta Description</label>
            <textarea name="meta_description" 
                      id="meta_description" 
                      rows="3"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('meta_description') border-red-500 @enderror"
                      placeholder="Brief description for SEO (max 500 characters)">{{ old('meta_description', $page->meta_description) }}</textarea>
            @error('meta_description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="meta_keywords" class="block text-sm font-medium text-gray-700 mb-2">Meta Keywords</label>
            <input type="text" 
                   name="meta_keywords" 
                   id="meta_keywords" 
                   value="{{ old('meta_keywords', $page->meta_keywords) }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('meta_keywords') border-red-500 @enderror"
                   placeholder="keyword1, keyword2, keyword3">
            @error('meta_keywords')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="order" class="block text-sm font-medium text-gray-700 mb-2">Display Order</label>
            <input type="number" 
                   name="order" 
                   id="order" 
                   value="{{ old('order', $page->order) }}"
                   min="0"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('order') border-red-500 @enderror">
            @error('order')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label class="flex items-center">
                <input type="checkbox" 
                       name="is_active" 
                       value="1" 
                       {{ old('is_active', $page->is_active) ? 'checked' : '' }}
                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                <span class="ml-2 text-sm text-gray-700">Active (visible to users)</span>
            </label>
        </div>

        <div class="flex justify-between items-center">
            <a href="{{ route('admin.pages.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded">
                <i class="fas fa-arrow-left mr-2"></i>Back to Pages
            </a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded">
                <i class="fas fa-save mr-2"></i>Update Page
            </button>
        </div>
    </form>
</div>

<!-- TinyMCE Rich Text Editor -->
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: '#content',
        height: 500,
        menubar: true,
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount'
        ],
        toolbar: 'undo redo | blocks | ' +
            'bold italic forecolor | alignleft aligncenter ' +
            'alignright alignjustify | bullist numlist outdent indent | ' +
            'removeformat | link image media | code | help',
        content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
        branding: false,
        promotion: false,
        image_advtab: true,
        file_picker_types: 'image',
        automatic_uploads: true,
        images_upload_handler: function (blobInfo, success, failure) {
            // You can implement image upload here
            // For now, we'll use base64
            const reader = new FileReader();
            reader.onload = function() {
                success(reader.result);
            };
            reader.readAsDataURL(blobInfo.blob());
        }
    });
</script>
@endsection
