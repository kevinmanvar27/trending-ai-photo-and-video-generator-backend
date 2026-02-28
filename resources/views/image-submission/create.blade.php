<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $siteTitle }} - {{ $template->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <header class="bg-white shadow-md">
        <div class="container mx-auto px-4 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <a href="{{ route('image-submission.index') }}" class="text-blue-500 hover:text-blue-600 mr-4">
                        <i class="fas fa-arrow-left text-xl"></i>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Upload Your Image</h1>
                        <p class="text-gray-600 mt-1">Effect: <span class="font-semibold">{{ $template->title }}</span></p>
                    </div>
                </div>
                @auth
                <div class="flex items-center space-x-4">
                    <span class="text-gray-700">Welcome, <span class="font-semibold">{{ auth()->user()->name }}</span></span>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                            <i class="fas fa-sign-out-alt mr-2"></i>
                            Logout
                        </button>
                    </form>
                </div>
                @endauth
            </div>
        </div>
    </header>

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
                        <img src="{{ Storage::url($template->reference_image_path) }}" 
                             alt="{{ $template->title }}" 
                             class="w-full h-auto">
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
                        <i class="fas fa-upload mr-2 text-blue-500"></i>
                        Upload Your Image
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
                                       accept="image/*"
                                       class="hidden"
                                       required
                                       onchange="previewImage(event)">
                                
                                <label for="image-input" class="cursor-pointer">
                                    <div id="upload-prompt">
                                        <i class="fas fa-cloud-upload-alt text-gray-400 text-6xl mb-4"></i>
                                        <p class="text-gray-700 font-medium mb-2">Click to upload or drag and drop</p>
                                        <p class="text-gray-500 text-sm">JPG, PNG, GIF, WebP (Max: 50MB)</p>
                                    </div>
                                    <div id="preview-container" class="hidden">
                                        <img id="image-preview" class="max-w-full h-auto rounded-lg mx-auto" alt="Preview">
                                        <p class="text-gray-600 mt-3 text-sm">
                                            <i class="fas fa-check-circle text-green-500 mr-1"></i>
                                            Image selected. Click to change.
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
                                <li><i class="fas fa-check mr-2"></i>Your image will be uploaded securely</li>
                                <li><i class="fas fa-check mr-2"></i>AI will process it with the selected effect</li>
                                <li><i class="fas fa-check mr-2"></i>Processing typically takes 10-30 seconds</li>
                                <li><i class="fas fa-check mr-2"></i>You'll be able to download the result</li>
                            </ul>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" 
                                class="w-full bg-blue-500 hover:bg-blue-600 text-white py-4 rounded-lg font-medium text-lg transition-colors duration-200">
                            <i class="fas fa-magic mr-2"></i>
                            Process My Image
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Image preview functionality
        function previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('upload-prompt').classList.add('hidden');
                    document.getElementById('preview-container').classList.remove('hidden');
                    document.getElementById('image-preview').src = e.target.result;
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
                previewImage({ target: { files: files } });
            }
        });
    </script>
</body>
</html>
