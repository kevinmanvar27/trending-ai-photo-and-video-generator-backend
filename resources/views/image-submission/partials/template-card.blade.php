<div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
    <!-- Reference Media -->
    @if($template->reference_image_path)
    <div class="relative h-48 bg-gray-200">
        @php
            $extension = strtolower(pathinfo($template->reference_image_path, PATHINFO_EXTENSION));
            $isVideo = in_array($extension, ['mp4', 'mov', 'avi', 'webm']);
        @endphp
        
        @if($isVideo)
            <video src="{{ Storage::url($template->reference_image_path) }}" 
                   class="w-full h-full object-cover"
                   muted
                   loop
                   onmouseover="this.play()" 
                   onmouseout="this.pause()">
            </video>
        @else
            <img src="{{ Storage::url($template->reference_image_path) }}" 
                 alt="{{ $template->title }}" 
                 class="w-full h-full object-cover">
        @endif
        <div class="absolute top-2 right-2">
            <span class="px-2 py-1 {{ $template->type === 'video' ? 'bg-purple-500' : 'bg-blue-500' }} text-white rounded-full text-xs font-medium">
                <i class="fas fa-{{ $template->type === 'video' ? 'video' : 'magic' }} mr-1"></i> AI
            </span>
        </div>
    </div>
    @else
    <div class="h-48 bg-gradient-to-br {{ $template->type === 'video' ? 'from-purple-400 to-pink-500' : 'from-blue-400 to-purple-500' }} flex items-center justify-center">
        <i class="fas fa-{{ $template->type === 'video' ? 'video' : 'image' }} text-white text-5xl opacity-50"></i>
    </div>
    @endif

    <!-- Template Info -->
    <div class="p-4">
        <h3 class="text-lg font-bold text-gray-800 mb-2 line-clamp-1">{{ $template->title }}</h3>
        
        @if($template->description)
        <p class="text-gray-600 text-sm mb-3 line-clamp-2">{{ $template->description }}</p>
        @endif

        <!-- Action Button -->
        <a href="{{ route('image-submission.create', ['template' => $template->id]) }}" 
           class="block w-full {{ $template->type === 'video' ? 'bg-purple-500 hover:bg-purple-600' : 'bg-blue-500 hover:bg-blue-600' }} text-white text-center py-2 rounded-lg font-medium transition-colors duration-200 text-sm">
            <i class="fas fa-upload mr-1"></i>
            Use This Effect
        </a>
    </div>
</div>
