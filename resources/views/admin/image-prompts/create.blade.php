@extends('admin.layout')

@section('title', 'Upload Image with Prompt')
@section('header', 'Upload Image with Prompt')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="mb-6">
            <a href="{{ route('admin.image-prompts.index') }}" class="text-blue-600 hover:text-blue-900">
                <i class="fas fa-arrow-left mr-2"></i>Back to Image Prompts
            </a>
        </div>

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('admin.image-prompts.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
            @csrf

            <!-- Image Upload Section -->
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">
                    Upload Image or Video <span class="text-red-500">*</span>
                </label>
                
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-500 transition-colors cursor-pointer" id="dropZone">
                    <input type="file" name="image" id="imageInput" class="hidden" accept="image/*,video/*" required>
                    
                    <div id="uploadPlaceholder">
                        <i class="fas fa-cloud-upload-alt text-gray-400 text-5xl mb-3"></i>
                        <p class="text-gray-600 mb-2">Click to upload or drag and drop</p>
                        <p class="text-gray-400 text-sm">Supported: JPG, PNG, GIF, MP4, MOV, AVI (Max 50MB)</p>
                    </div>

                    <div id="previewContainer" class="hidden">
                        <img id="imagePreview" class="max-w-full h-auto rounded-lg mx-auto mb-3" style="max-height: 300px;">
                        <video id="videoPreview" class="max-w-full h-auto rounded-lg mx-auto mb-3 hidden" style="max-height: 300px;" controls></video>
                        <p id="fileName" class="text-gray-700 font-semibold"></p>
                        <button type="button" id="removeFile" class="mt-2 text-red-600 hover:text-red-800">
                            <i class="fas fa-times-circle mr-1"></i>Remove
                        </button>
                    </div>
                </div>
                @error('image')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Prompt Input Section -->
            <div class="mb-6">
                <label for="prompt" class="block text-gray-700 text-sm font-bold mb-2">
                    AI Prompt <span class="text-red-500">*</span>
                </label>
                <textarea 
                    name="prompt" 
                    id="prompt" 
                    rows="5" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Enter your prompt here... (e.g., 'Analyze this image and describe what you see', 'Apply a vintage filter', 'Identify objects in this image')"
                    required
                >{{ old('prompt') }}</textarea>
                <p class="text-gray-500 text-xs mt-1">Describe what you want the AI to do with your image/video</p>
                @error('prompt')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Example Prompts -->
            <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <p class="text-sm font-semibold text-blue-900 mb-2">
                    <i class="fas fa-lightbulb mr-1"></i>Example Prompts:
                </p>
                <ul class="text-sm text-blue-800 space-y-1">
                    <li class="cursor-pointer hover:text-blue-600" onclick="setPrompt('Analyze this image and provide a detailed description of what you see')">
                        • Analyze this image and provide a detailed description
                    </li>
                    <li class="cursor-pointer hover:text-blue-600" onclick="setPrompt('Identify all objects and people in this image')">
                        • Identify all objects and people in this image
                    </li>
                    <li class="cursor-pointer hover:text-blue-600" onclick="setPrompt('What is the mood or emotion conveyed in this image?')">
                        • What is the mood or emotion conveyed in this image?
                    </li>
                    <li class="cursor-pointer hover:text-blue-600" onclick="setPrompt('Extract and list all text visible in this image')">
                        • Extract and list all text visible in this image
                    </li>
                </ul>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-between">
                <button 
                    type="submit" 
                    class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-6 rounded-lg focus:outline-none focus:shadow-outline inline-flex items-center"
                    id="submitBtn"
                >
                    <i class="fas fa-magic mr-2"></i>
                    Process with AI
                </button>
                <a href="{{ route('admin.image-prompts.index') }}" class="text-gray-600 hover:text-gray-800">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    const dropZone = document.getElementById('dropZone');
    const imageInput = document.getElementById('imageInput');
    const uploadPlaceholder = document.getElementById('uploadPlaceholder');
    const previewContainer = document.getElementById('previewContainer');
    const imagePreview = document.getElementById('imagePreview');
    const videoPreview = document.getElementById('videoPreview');
    const fileName = document.getElementById('fileName');
    const removeFile = document.getElementById('removeFile');
    const submitBtn = document.getElementById('submitBtn');

    // Click to upload
    dropZone.addEventListener('click', () => {
        if (!previewContainer.classList.contains('hidden')) return;
        imageInput.click();
    });

    // Drag and drop
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('border-blue-500', 'bg-blue-50');
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('border-blue-500', 'bg-blue-50');
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('border-blue-500', 'bg-blue-50');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            imageInput.files = files;
            handleFileSelect(files[0]);
        }
    });

    // File input change
    imageInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            handleFileSelect(e.target.files[0]);
        }
    });

    // Handle file selection
    function handleFileSelect(file) {
        const fileType = file.type.split('/')[0];
        
        uploadPlaceholder.classList.add('hidden');
        previewContainer.classList.remove('hidden');
        fileName.textContent = file.name;

        if (fileType === 'image') {
            const reader = new FileReader();
            reader.onload = (e) => {
                imagePreview.src = e.target.result;
                imagePreview.classList.remove('hidden');
                videoPreview.classList.add('hidden');
            };
            reader.readAsDataURL(file);
        } else if (fileType === 'video') {
            const reader = new FileReader();
            reader.onload = (e) => {
                videoPreview.src = e.target.result;
                videoPreview.classList.remove('hidden');
                imagePreview.classList.add('hidden');
            };
            reader.readAsDataURL(file);
        }
    }

    // Remove file
    removeFile.addEventListener('click', (e) => {
        e.stopPropagation();
        imageInput.value = '';
        uploadPlaceholder.classList.remove('hidden');
        previewContainer.classList.add('hidden');
        imagePreview.src = '';
        videoPreview.src = '';
    });

    // Set example prompt
    function setPrompt(text) {
        document.getElementById('prompt').value = text;
    }

    // Form submission
    document.getElementById('uploadForm').addEventListener('submit', function() {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
    });
</script>
@endsection
