@extends('admin.layout')

@section('title', 'Edit Image Template')
@section('header', 'Edit Image Template')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <form action="{{ route('admin.image-templates.update', $template) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Title -->
            <div class="mb-6">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                    Template Title <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="title" 
                       id="title" 
                       value="{{ old('title', $template->title) }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('title') border-red-500 @enderror" 
                       placeholder="e.g., Vintage Film Effect, Cartoon Style, Professional Headshot"
                       required>
                @error('title')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Type -->
            <div class="mb-6">
                <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                    Template Type <span class="text-red-500">*</span>
                </label>
                <div class="flex gap-4">
                    <label class="flex items-center cursor-pointer">
                        <input type="radio" 
                               name="type" 
                               value="image" 
                               {{ old('type', $template->type) == 'image' ? 'checked' : '' }}
                               class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700">
                            <i class="fas fa-image mr-1 text-blue-500"></i> Image Template
                        </span>
                    </label>
                    <label class="flex items-center cursor-pointer">
                        <input type="radio" 
                               name="type" 
                               value="video" 
                               {{ old('type', $template->type) == 'video' ? 'checked' : '' }}
                               class="w-4 h-4 text-purple-600 border-gray-300 focus:ring-purple-500">
                        <span class="ml-2 text-sm text-gray-700">
                            <i class="fas fa-video mr-1 text-purple-500"></i> Video Template
                        </span>
                    </label>
                </div>
                @error('type')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-sm text-gray-500 mt-1">Choose whether this template is for images or videos</p>
            </div>

            <!-- Description -->
            <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                    Description
                </label>
                <textarea name="description" 
                          id="description" 
                          rows="3"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('description') border-red-500 @enderror"
                          placeholder="Describe what this effect does and how it transforms images...">{{ old('description', $template->description) }}</textarea>
                @error('description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-sm text-gray-500 mt-1">This will be shown to users when they browse templates</p>
            </div>

            <!-- Coins Required -->
            <div class="mb-6">
                <label for="coins_required" class="block text-sm font-medium text-gray-700 mb-2">
                    Coins Required
                </label>
                <input type="number" 
                       name="coins_required" 
                       id="coins_required" 
                       value="{{ old('coins_required', $template->coins_required ?? 0) }}"
                       min="0"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('coins_required') border-red-500 @enderror" 
                       placeholder="0">
                @error('coins_required')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-sm text-gray-500 mt-1">
                    <i class="fas fa-coins mr-1 text-yellow-500"></i>
                    Number of coins users need to use this template (0 = free)
                </p>
            </div>

            <!-- Current Reference Image -->
            @if($template->reference_image_path)
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Current Reference Image
                </label>
                <div class="border border-gray-300 rounded-lg p-4 bg-gray-50">
                    <img src="{{ Storage::url($template->reference_image_path) }}" 
                         alt="Current reference" 
                         class="max-w-full h-auto rounded-lg mx-auto"
                         style="max-height: 300px;">
                </div>
            </div>
            @endif

            <!-- New Reference Image -->
            <div class="mb-6">
                <label for="reference_image" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ $template->reference_image_path ? 'Replace Reference Image (Optional)' : 'Reference Image' }}
                </label>
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                    <input type="file" 
                           name="reference_image" 
                           id="reference_image" 
                           accept="image/*"
                           class="hidden"
                           onchange="previewImage(event)">
                    <label for="reference_image" class="cursor-pointer">
                        <div id="preview-container">
                            <i class="fas fa-cloud-upload-alt text-gray-400 text-5xl mb-2"></i>
                            <p class="text-gray-600">Click to {{ $template->reference_image_path ? 'replace' : 'upload' }} reference image</p>
                            <p class="text-sm text-gray-500 mt-2">Show users how their image will look after applying this effect</p>
                        </div>
                        <img id="image-preview" class="hidden max-w-full h-auto rounded-lg mx-auto" alt="Preview">
                    </label>
                </div>
                @error('reference_image')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-sm text-gray-500 mt-1">Supported: JPG, PNG, GIF (Max: 10MB)</p>
            </div>

            <!-- Prompt -->
            <div class="mb-6">
                <label for="prompt" class="block text-sm font-medium text-gray-700 mb-2">
                    AI Prompt <span class="text-red-500">*</span>
                </label>
                <textarea name="prompt" 
                          id="prompt" 
                          rows="6"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm @error('prompt') border-red-500 @enderror"
                          placeholder="Enter the AI prompt that will be applied to user images..."
                          required>{{ old('prompt', $template->prompt) }}</textarea>
                @error('prompt')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                
                <!-- Prompt Examples -->
                <div class="mt-3 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-sm font-medium text-blue-900 mb-2"><i class="fas fa-lightbulb mr-1"></i> Example Prompts:</p>
                    <div class="space-y-2 text-sm text-blue-800">
                        <div class="cursor-pointer hover:bg-blue-100 p-2 rounded" onclick="setPrompt(this.dataset.prompt)" data-prompt="Analyze this image and describe it in detail, including colors, composition, subjects, and mood.">
                            <strong>Image Analysis:</strong> Analyze this image and describe it in detail...
                        </div>
                        <div class="cursor-pointer hover:bg-blue-100 p-2 rounded" onclick="setPrompt(this.dataset.prompt)" data-prompt="Transform this image into a vintage 1970s film photograph style with warm tones, slight grain, and soft focus.">
                            <strong>Vintage Effect:</strong> Transform this image into a vintage 1970s film photograph...
                        </div>
                        <div class="cursor-pointer hover:bg-blue-100 p-2 rounded" onclick="setPrompt(this.dataset.prompt)" data-prompt="Describe this image as if it were a cartoon or animated illustration. What style would work best?">
                            <strong>Cartoon Style:</strong> Describe this image as if it were a cartoon...
                        </div>
                        <div class="cursor-pointer hover:bg-blue-100 p-2 rounded" onclick="setPrompt(this.dataset.prompt)" data-prompt="Provide professional photography tips to improve this image. Suggest composition, lighting, and editing improvements.">
                            <strong>Photo Critique:</strong> Provide professional photography tips...
                        </div>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mt-2">
                    <i class="fas fa-info-circle mr-1"></i> 
                    This prompt will be automatically applied to every image uploaded by users. 
                    <strong>Users will NOT see this prompt.</strong>
                </p>
            </div>

            <!-- Active Status -->
            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" 
                           name="is_active" 
                           value="1"
                           {{ old('is_active', $template->is_active) ? 'checked' : '' }}
                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-700">Make this template active and available to users</span>
                </label>
            </div>

            <!-- Action Buttons -->
            <div class="flex space-x-3">
                <button type="submit" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white py-3 rounded-lg font-medium">
                    <i class="fas fa-save mr-2"></i>
                    Update Template
                </button>
                <a href="{{ route('admin.image-templates.index') }}" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white py-3 rounded-lg font-medium text-center">
                    <i class="fas fa-times mr-2"></i>
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function previewImage(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-container').classList.add('hidden');
            const preview = document.getElementById('image-preview');
            preview.src = e.target.result;
            preview.classList.remove('hidden');
        }
        reader.readAsDataURL(file);
    }
}

function setPrompt(promptText) {
    document.getElementById('prompt').value = promptText;
}
</script>
@endsection
