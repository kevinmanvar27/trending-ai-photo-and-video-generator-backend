<!-- Professional Header with Modern Design -->
<header class="bg-gradient-to-r from-slate-900 via-blue-900 to-slate-900 shadow-2xl sticky top-0 z-50 backdrop-blur-sm bg-opacity-95">
    <div class="container mx-auto px-4 lg:px-8">
        <!-- Top Section: Logo and Auth -->
        <div class="flex justify-between items-center py-4">
            <!-- Logo -->
            <a href="{{ route('image-submission.index') }}" class="flex items-center group">
                @if(setting('site_logo'))
                    <img src="{{ asset('storage/' . setting('site_logo')) }}" 
                         alt="{{ setting('site_title', config('app.name')) }}" 
                         class="h-12 md:h-16 transition-transform duration-300 group-hover:scale-105">
                @else
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-magic text-white text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl md:text-3xl font-bold bg-gradient-to-r from-blue-400 via-cyan-400 to-blue-400 bg-clip-text text-transparent">
                                {{ setting('site_title', config('app.name')) }}
                            </h1>
                            @if(setting('site_description'))
                                <p class="text-xs md:text-sm text-gray-300 hidden sm:block">
                                    {{ setting('site_description') }}
                                </p>
                            @endif
                        </div>
                    </div>
                @endif
            </a>

            <!-- Auth Section -->
            <div class="flex items-center space-x-2 md:space-x-3">
                @auth
                    <!-- User Profile Dropdown -->
                    <div class="relative group">
                        <button class="hidden lg:flex items-center space-x-2 bg-white/10 backdrop-blur-sm px-4 py-2 rounded-full border border-white/20 hover:bg-white/20 transition-all duration-300">
                            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-full flex items-center justify-center">
                                <span class="text-white text-sm font-bold">{{ substr(auth()->user()->name, 0, 1) }}</span>
                            </div>
                            <span class="text-white font-medium">{{ auth()->user()->name }}</span>
                            <i class="fas fa-chevron-down text-white text-xs"></i>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 overflow-hidden border border-gray-200 z-50">
                            <div class="p-4 bg-gradient-to-r from-blue-600 to-cyan-600">
                                <p class="text-white font-semibold truncate">{{ auth()->user()->name }}</p>
                                <p class="text-blue-100 text-sm truncate">{{ auth()->user()->email }}</p>
                            </div>
                            <div class="py-2">
                                <a href="{{ route('home') }}" class="flex items-center space-x-3 px-4 py-3 hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-home text-gray-600 w-5"></i>
                                    <span class="text-gray-700">Dashboard</span>
                                </a>
                                <a href="{{ route('account.delete.form') }}" class="flex items-center space-x-3 px-4 py-3 hover:bg-red-50 transition-colors text-red-600">
                                    <i class="fas fa-trash-alt w-5"></i>
                                    <span>Delete Account</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}" 
                           class="bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 text-white px-4 md:px-6 py-2.5 rounded-full font-medium transition-all duration-300 text-sm md:text-base shadow-lg hover:shadow-blue-500/50 flex items-center space-x-2">
                            <i class="fas fa-crown"></i>
                            <span class="hidden sm:inline">Admin</span>
                        </a>
                    @endif
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" 
                                class="bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white px-4 md:px-6 py-2.5 rounded-full font-medium transition-all duration-300 text-sm md:text-base shadow-lg hover:shadow-red-500/50 flex items-center space-x-2">
                            <i class="fas fa-sign-out-alt"></i>
                            <span class="hidden sm:inline">Logout</span>
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" 
                       class="bg-white/10 backdrop-blur-sm hover:bg-white/20 text-white px-4 md:px-6 py-2.5 rounded-full font-medium transition-all duration-300 text-sm md:text-base border border-white/20 hover:border-white/40 flex items-center space-x-2">
                        <i class="fas fa-sign-in-alt"></i>
                        <span class="hidden sm:inline">Login</span>
                    </a>
                    <a href="{{ route('register') }}" 
                       class="bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 text-white px-4 md:px-6 py-2.5 rounded-full font-medium transition-all duration-300 text-sm md:text-base shadow-lg hover:shadow-blue-500/50 flex items-center space-x-2">
                        <i class="fas fa-user-plus"></i>
                        <span class="hidden sm:inline">Register</span>
                    </a>
                @endauth
            </div>
        </div>

        <!-- Navigation Bar -->
        <nav class="border-t border-white/10">
            <ul class="flex flex-wrap items-center gap-1 md:gap-2 py-3">
                <!-- Home Link -->
                <li>
                    <a href="{{ route('image-submission.index') }}" 
                       class="px-4 py-2 rounded-full font-medium transition-all duration-300 flex items-center space-x-2 {{ request()->routeIs('image-submission.index') ? 'bg-white/20 text-white shadow-lg' : 'text-gray-300 hover:text-white hover:bg-white/10' }}">
                        <i class="fas fa-home"></i>
                        <span>Home</span>
                    </a>
                </li>

                <!-- Dynamic Pages -->
                @if(isset($pages) && $pages->count() > 0)
                    @foreach($pages as $page)
                    <li>
                        <a href="{{ route('page.show', $page->slug) }}" 
                           class="px-4 py-2 rounded-full font-medium transition-all duration-300 flex items-center space-x-2 {{ request()->is('page/' . $page->slug) ? 'bg-white/20 text-white shadow-lg' : 'text-gray-300 hover:text-white hover:bg-white/10' }}">
                            <span>{{ $page->title }}</span>
                        </a>
                    </li>
                    @endforeach
                @endif

                <!-- Additional Links -->
                <li class="ml-auto">
                    <a href="{{ route('image-submission.image-effects') }}" 
                       class="px-4 py-2 rounded-full font-medium transition-all duration-300 flex items-center space-x-2 {{ request()->routeIs('image-submission.image-effects') ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-lg shadow-blue-500/50' : 'text-gray-300 hover:text-white hover:bg-white/10' }}">
                        <i class="fas fa-image"></i>
                        <span>Images</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('image-submission.video-effects') }}" 
                       class="px-4 py-2 rounded-full font-medium transition-all duration-300 flex items-center space-x-2 {{ request()->routeIs('image-submission.video-effects') ? 'bg-gradient-to-r from-blue-600 to-cyan-600 text-white shadow-lg shadow-cyan-500/50' : 'text-gray-300 hover:text-white hover:bg-white/10' }}">
                        <i class="fas fa-video"></i>
                        <span>Videos</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</header>

<style>
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    header {
        animation: slideDown 0.5s ease-out;
    }
</style>
