<div class="bg-white rounded-2xl shadow-xl overflow-hidden hover:shadow-2xl transition-all duration-300 group">
    <!-- Reference Media -->
    @if($template->reference_image_path)
    <div class="relative h-64 bg-gradient-to-br from-gray-100 to-gray-200 overflow-hidden">
        @php
            $extension = strtolower(pathinfo($template->reference_image_path, PATHINFO_EXTENSION));
            $isVideo = in_array($extension, ['mp4', 'mov', 'avi', 'webm']);
        @endphp
        
        @if($isVideo)
            <video src="{{ Storage::url($template->reference_image_path) }}" 
                   class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-500"
                   muted
                   loop
                   onmouseover="this.play()" 
                   onmouseout="this.pause()">
            </video>
        @else
            <img src="{{ Storage::url($template->reference_image_path) }}" 
                 alt="{{ $template->title }}" 
                 class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-500">
        @endif
        
        <!-- Overlay on Hover -->
        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        
        <!-- Badge -->
        <div class="absolute top-4 right-4">
            <span class="px-4 py-2 {{ $template->type === 'video' ? 'bg-gradient-to-r from-blue-600 to-cyan-600' : 'bg-gradient-to-r from-blue-600 to-blue-600' }} text-white rounded-full text-xs font-bold shadow-lg backdrop-blur-sm flex items-center space-x-2">
                <i class="fas fa-{{ $template->type === 'video' ? 'video' : 'magic' }}"></i>
                <span>AI Powered</span>
            </span>
        </div>
        
        <!-- Quick View Icon -->
        <div class="absolute top-4 left-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
            <div class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center">
                <i class="fas fa-eye text-white"></i>
            </div>
        </div>
    </div>
    @else
    <div class="h-64 bg-gradient-to-br {{ $template->type === 'video' ? 'from-blue-500 via-cyan-500 to-blue-600' : 'from-blue-500 via-blue-500 to-cyan-500' }} flex items-center justify-center relative overflow-hidden">
        <!-- Animated Background -->
        <div class="absolute inset-0 opacity-20">
            <div class="absolute inset-0 bg-white transform rotate-45 translate-x-full group-hover:translate-x-[-100%] transition-transform duration-1000"></div>
        </div>
        <i class="fas fa-{{ $template->type === 'video' ? 'video' : 'image' }} text-white text-6xl opacity-80 relative z-10 transform group-hover:scale-110 transition-transform duration-300"></i>
    </div>
    @endif

    <!-- Template Info -->
    <div class="p-6">
        <!-- Title -->
        <h3 class="text-xl font-bold text-gray-800 mb-2 line-clamp-1 group-hover:text-transparent group-hover:bg-gradient-to-r {{ $template->type === 'video' ? 'group-hover:from-blue-600 group-hover:to-cyan-600' : 'group-hover:from-blue-600 group-hover:to-blue-600' }} group-hover:bg-clip-text transition-all duration-300">
            {{ $template->title }}
        </h3>
        
        <!-- Description -->
        @if($template->description)
        <p class="text-gray-600 text-sm mb-4 line-clamp-2 leading-relaxed">
            {{ $template->description }}
        </p>
        @endif

        <!-- Stats or Features (Optional) -->
        <div class="flex items-center space-x-4 mb-4 text-xs text-gray-500">
            <div class="flex items-center space-x-1">
                <i class="fas fa-bolt text-yellow-500"></i>
                <span>Fast Processing</span>
            </div>
            <div class="flex items-center space-x-1">
                <i class="fas fa-star text-blue-500"></i>
                <span>HD Quality</span>
            </div>
        </div>

        <!-- Action Button -->
        <a href="{{ route('image-submission.create', ['template' => $template->id]) }}" 
           class="block w-full {{ $template->type === 'video' ? 'bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 shadow-cyan-500/50' : 'bg-gradient-to-r from-blue-600 to-blue-600 hover:from-blue-700 hover:to-blue-700 shadow-blue-500/50' }} text-white text-center py-3.5 rounded-xl font-bold transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 flex items-center justify-center space-x-2">
            <i class="fas fa-upload"></i>
            <span>Use This Effect</span>
            <i class="fas fa-arrow-right text-sm"></i>
        </a>
    </div>
</div>
