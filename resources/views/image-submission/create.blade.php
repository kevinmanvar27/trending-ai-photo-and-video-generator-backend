<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ setting('site_title', config('app.name')) }} - {{ $template->title }}</title>
    
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

    <!-- Breadcrumb -->
    <div class="bg-white border-b border-gray-200">
        <div class="container mx-auto px-4 py-3">
            <div class="flex items-center text-sm text-gray-600">
                <a href="{{ route('image-submission.index') }}" class="hover:text-blue-500">
                    <i class="fas fa-home mr-1"></i>
                    Home
                </a>
                <i class="fas fa-chevron-right mx-2 text-xs"></i>
                <span class="text-gray-800 font-medium">{{ $template->title }}</span>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Template Preview -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">
                        <i class="fas fa-eye mr-2 text-blue-500"></i>
                        Effect Preview
                    </h2>
                    
                    @if($template->reference_image_path)
                    <div class="border border-gray-300 rounded-lg overflow-hidden mb-4">
                        @php
                            $extension = strtolower(pathinfo($template->reference_image_path, PATHINFO_EXTENSION));
                            $isVideo = in_array($extension, ['mp4', 'mov', 'avi', 'webm']);
                        @endphp
                        
                        @if($isVideo)
                            <video src="{{ Storage::url($template->reference_image_path) }}" 
                                   controls
                                   class="w-full h-auto">
                                Your browser does not support the video tag.
                            </video>
                        @else
                            <img src="{{ Storage::url($template->reference_image_path) }}" 
                                 alt="{{ $template->title }}" 
                                 class="w-full h-auto">
                        @endif
                    </div>
                    @endif

                    <h3 class="font-semibold text-gray-800 mb-2">{{ $template->title }}</h3>
                    @if($template->description)
                    <p class="text-gray-600 text-sm">{{ $template->description }}</p>
                    @endif
                </div>

                <!-- Upload Form -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">
                        <i class="fas fa-upload mr-2 {{ $template->type == 'video' ? 'text-purple-500' : 'text-blue-500' }}"></i>
                        Upload Your {{ ucfirst($template->type) }}
                    </h2>

                    @if($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form action="{{ route('image-submission.store', $template) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="template_id" value="{{ $template->id }}">

                        <!-- File Upload Area -->
                        <div class="mb-6">
                            <div id="drop-zone" class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center cursor-pointer hover:border-blue-500 transition-colors duration-200">
                                <input type="file" 
                                       name="image" 
                                       id="image-input" 
                                       accept="{{ $template->type == 'video' ? 'video/*' : 'image/*' }}"
                                       class="hidden"
                                       required
                                       onchange="previewMedia(event)">
                                
                                <label for="image-input" class="cursor-pointer">
                                    <div id="upload-prompt">
                                        <i class="fas fa-cloud-upload-alt text-gray-400 text-6xl mb-4"></i>
                                        <p class="text-gray-700 font-medium mb-2">Click to upload or drag and drop</p>
                                        <p class="text-gray-500 text-sm">
                                            @if($template->type == 'video')
                                                MP4, MOV, AVI, WEBM (Max: 50MB)
                                            @else
                                                JPG, PNG, GIF, WebP (Max: 50MB)
                                            @endif
                                        </p>
                                    </div>
                                    <div id="preview-container" class="hidden">
                                        <img id="image-preview" class="hidden max-w-full h-auto rounded-lg mx-auto" alt="Preview">
                                        <video id="video-preview" class="hidden max-w-full h-auto rounded-lg mx-auto" controls alt="Preview"></video>
                                        <p class="text-gray-600 mt-3 text-sm">
                                            <i class="fas fa-check-circle text-green-500 mr-1"></i>
                                            {{ ucfirst($template->type) }} selected. Click to change.
                                        </p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Processing Info -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                            <h3 class="text-sm font-semibold text-blue-900 mb-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                What Happens Next?
                            </h3>
                            <ul class="text-sm text-blue-800 space-y-1">
                                <li><i class="fas fa-check mr-2"></i>Your {{ $template->type }} will be uploaded securely</li>
                                <li><i class="fas fa-check mr-2"></i>AI will process it with the selected effect</li>
                                <li><i class="fas fa-check mr-2"></i>Processing typically takes {{ $template->type == 'video' ? '30-60 seconds' : '10-30 seconds' }}</li>
                                <li><i class="fas fa-check mr-2"></i>You'll be able to download the result</li>
                            </ul>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" 
                                class="w-full {{ $template->type == 'video' ? 'bg-purple-500 hover:bg-purple-600' : 'bg-blue-500 hover:bg-blue-600' }} text-white py-4 rounded-lg font-medium text-lg transition-colors duration-200">
                            <i class="fas fa-magic mr-2"></i>
                            Process My {{ ucfirst($template->type) }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Media preview functionality (supports both image and video)
        function previewMedia(event) {
            const file = event.target.files[0];
            if (file) {
                const fileType = file.type;
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    document.getElementById('upload-prompt').classList.add('hidden');
                    document.getElementById('preview-container').classList.remove('hidden');
                    
                    if (fileType.startsWith('video/')) {
                        // Show video preview
                        const videoPreview = document.getElementById('video-preview');
                        const imagePreview = document.getElementById('image-preview');
                        imagePreview.classList.add('hidden');
                        videoPreview.src = e.target.result;
                        videoPreview.classList.remove('hidden');
                    } else {
                        // Show image preview
                        const imagePreview = document.getElementById('image-preview');
                        const videoPreview = document.getElementById('video-preview');
                        videoPreview.classList.add('hidden');
                        imagePreview.src = e.target.result;
                        imagePreview.classList.remove('hidden');
                    }
                }
                reader.readAsDataURL(file);
            }
        }

        // Drag and drop functionality
        const dropZone = document.getElementById('drop-zone');
        const imageInput = document.getElementById('image-input');

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('border-blue-500', 'bg-blue-50');
        });

        dropZone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-blue-500', 'bg-blue-50');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-blue-500', 'bg-blue-50');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                imageInput.files = files;
                previewMedia({ target: { files: files } });
            }
        });
    </script>

    @include('partials.footer')
</body>
</html>
