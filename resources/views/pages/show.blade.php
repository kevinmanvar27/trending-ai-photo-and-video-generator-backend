<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->title }} - {{ setting('site_title', config('app.name')) }}</title>
    
    @if($page->meta_description)
        <meta name="description" content="{{ $page->meta_description }}">
    @endif
    
    @if($page->meta_keywords)
        <meta name="keywords" content="{{ $page->meta_keywords }}">
    @endif
    
    @if(setting('site_favicon'))
        <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . setting('site_favicon')) }}">
    @endif
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @if(setting('header_code'))
        {!! setting('header_code') !!}
    @endif
    
    <style>
        /* Rich text content styling */
        .page-content {
            line-height: 1.8;
        }
        .page-content h1 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-top: 2rem;
            margin-bottom: 1rem;
        }
        .page-content h2 {
            font-size: 2rem;
            font-weight: bold;
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
        }
        .page-content h3 {
            font-size: 1.5rem;
            font-weight: bold;
            margin-top: 1.25rem;
            margin-bottom: 0.5rem;
        }
        .page-content h4 {
            font-size: 1.25rem;
            font-weight: bold;
            margin-top: 1rem;
            margin-bottom: 0.5rem;
        }
        .page-content p {
            margin-bottom: 1rem;
        }
        .page-content ul, .page-content ol {
            margin-left: 2rem;
            margin-bottom: 1rem;
        }
        .page-content ul {
            list-style-type: disc;
        }
        .page-content ol {
            list-style-type: decimal;
        }
        .page-content li {
            margin-bottom: 0.5rem;
        }
        .page-content a {
            color: #3b82f6;
            text-decoration: underline;
        }
        .page-content a:hover {
            color: #2563eb;
        }
        .page-content img {
            max-width: 100%;
            height: auto;
            margin: 1rem 0;
            border-radius: 0.5rem;
        }
        .page-content blockquote {
            border-left: 4px solid #e5e7eb;
            padding-left: 1rem;
            margin: 1rem 0;
            font-style: italic;
            color: #6b7280;
        }
        .page-content table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
        }
        .page-content table th,
        .page-content table td {
            border: 1px solid #e5e7eb;
            padding: 0.75rem;
            text-align: left;
        }
        .page-content table th {
            background-color: #f9fafb;
            font-weight: bold;
        }
        .page-content code {
            background-color: #f3f4f6;
            padding: 0.125rem 0.25rem;
            border-radius: 0.25rem;
            font-family: monospace;
            font-size: 0.875rem;
        }
        .page-content pre {
            background-color: #1f2937;
            color: #f9fafb;
            padding: 1rem;
            border-radius: 0.5rem;
            overflow-x: auto;
            margin: 1rem 0;
        }
        .page-content pre code {
            background-color: transparent;
            color: inherit;
            padding: 0;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Include Shared Header -->
    @include('partials.header')

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <article class="bg-white rounded-lg shadow-md p-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-6">{{ $page->title }}</h1>
            
            <div class="text-sm text-gray-500 mb-8">
                <i class="far fa-clock mr-1"></i>
                Last updated: {{ $page->updated_at->format('F d, Y') }}
            </div>
            
            <div class="page-content text-gray-700">
                {!! $page->content !!}
            </div>
        </article>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="text-center">
                <p>{{ setting('footer_text', '© ' . date('Y') . ' ' . setting('site_title', config('app.name')) . '. All rights reserved.') }}</p>
            </div>
        </div>
    </footer>
    
    @if(setting('footer_code'))
        {!! setting('footer_code') !!}
    @endif
</body>
</html>
