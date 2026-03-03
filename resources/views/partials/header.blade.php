<!-- Header with Logo and Navigation -->
<header class="bg-white shadow-md">
    <div class="container mx-auto px-4 py-4">
        <!-- Top Section: Logo and Auth -->
        <div class="flex justify-between items-center">
            <!-- Logo -->
            <a href="{{ route('image-submission.index') }}" class="flex items-center">
                @if(setting('site_logo'))
                    <img src="{{ asset('storage/' . setting('site_logo')) }}" 
                         alt="{{ setting('site_title', config('app.name')) }}" 
                         class="h-12 md:h-14">
                @else
                    <div class="flex items-center">
                        <div>
                            <h1 class="text-2xl md:text-3xl font-bold text-gray-800">
                                {{ setting('site_title', config('app.name')) }}
                            </h1>
                            @if(setting('site_description'))
                                <p class="text-xs md:text-sm text-gray-600 hidden sm:block">
                                    {{ setting('site_description') }}
                                </p>
                            @endif
                        </div>
                    </div>
                @endif
            </a>

            <!-- Auth Section -->
            <div class="flex items-center space-x-2 md:space-x-4">
                @auth
                    <span class="text-gray-700 hidden md:inline">
                        Welcome, <span class="font-semibold">{{ auth()->user()->name }}</span>
                    </span>
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}" 
                           class="bg-purple-500 hover:bg-purple-600 text-white px-3 py-2 rounded-lg font-medium transition-colors duration-200 text-sm md:text-base">
                            <span class="hidden sm:inline">Admin</span>
                        </a>
                    @endif
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" 
                                class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-lg font-medium transition-colors duration-200 text-sm md:text-base">
                            <span class="hidden sm:inline">Logout</span>
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" 
                       class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded-lg font-medium transition-colors duration-200 text-sm md:text-base">
                        <span class="hidden sm:inline">Login</span>
                    </a>
                    <a href="{{ route('register') }}" 
                       class="bg-green-500 hover:bg-green-600 text-white px-3 py-2 rounded-lg font-medium transition-colors duration-200 text-sm md:text-base">
                        <span class="hidden sm:inline">Register</span>
                    </a>
                @endauth
            </div>
        </div>

        <!-- Navigation Bar -->
        <nav class="mt-4 pt-4 border-t border-gray-200">
            <ul class="flex flex-wrap items-center gap-3 md:gap-6">
                <!-- Home Link -->
                <li>
                    <a href="{{ route('image-submission.index') }}" 
                       class="font-medium transition-colors duration-200 flex items-center {{ request()->routeIs('image-submission.index') ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-700 hover:text-blue-500' }}">
                        <span>Home</span>
                    </a>
                </li>

                <!-- Dynamic Pages -->
                @if(isset($pages) && $pages->count() > 0)
                    @foreach($pages as $page)
                    <li>
                        <a href="{{ route('page.show', $page->slug) }}" 
                           class="font-medium transition-colors duration-200 flex items-center {{ request()->is('page/' . $page->slug) ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-700 hover:text-blue-500' }}">
                            <span>{{ $page->title }}</span>
                        </a>
                    </li>
                    @endforeach
                @endif

                <!-- Additional Links -->
                <li class="ml-auto">
                    <a href="{{ route('image-submission.image-effects') }}" 
                       class="font-medium transition-colors duration-200 flex items-center {{ request()->routeIs('image-submission.image-effects') ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-700 hover:text-blue-500' }}">
                        <span>Images</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('image-submission.video-effects') }}" 
                       class="font-medium transition-colors duration-200 flex items-center {{ request()->routeIs('image-submission.video-effects') ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-700 hover:text-blue-500' }}">
                        <span>Videos</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</header>
