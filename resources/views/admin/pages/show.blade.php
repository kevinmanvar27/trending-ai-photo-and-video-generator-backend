@extends('admin.layout')

@section('title', $page->title)
@section('header', 'View Page')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="mb-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold text-gray-800">{{ $page->title }}</h2>
            <div class="space-x-2">
                <a href="{{ route('admin.pages.edit', $page->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
                <a href="{{ route('admin.pages.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">
                    <i class="fas fa-arrow-left mr-2"></i>Back
                </a>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-6 text-sm">
            <div>
                <span class="font-semibold text-gray-700">Slug:</span>
                <span class="text-gray-600">{{ $page->slug }}</span>
            </div>
            <div>
                <span class="font-semibold text-gray-700">Order:</span>
                <span class="text-gray-600">{{ $page->order }}</span>
            </div>
            <div>
                <span class="font-semibold text-gray-700">Status:</span>
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $page->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ $page->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
            <div>
                <span class="font-semibold text-gray-700">Created:</span>
                <span class="text-gray-600">{{ $page->created_at->format('M d, Y H:i') }}</span>
            </div>
        </div>

        @if($page->meta_description)
        <div class="mb-4">
            <h3 class="font-semibold text-gray-700 mb-2">Meta Description:</h3>
            <p class="text-gray-600 text-sm">{{ $page->meta_description }}</p>
        </div>
        @endif

        @if($page->meta_keywords)
        <div class="mb-4">
            <h3 class="font-semibold text-gray-700 mb-2">Meta Keywords:</h3>
            <p class="text-gray-600 text-sm">{{ $page->meta_keywords }}</p>
        </div>
        @endif
    </div>

    <div class="border-t pt-6">
        <h3 class="font-semibold text-gray-700 mb-4">Content:</h3>
        <div class="prose max-w-none bg-gray-50 p-6 rounded">
            {!! $page->content !!}
        </div>
    </div>
</div>
@endsection
