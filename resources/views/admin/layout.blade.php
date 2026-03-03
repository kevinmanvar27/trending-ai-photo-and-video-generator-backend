<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') - {{ setting('site_title', 'Admin Panel') }}</title>
    
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
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-800 text-white">
            <div class="p-4">
                @if(setting('site_logo'))
                    <img src="{{ asset('storage/' . setting('site_logo')) }}" alt="{{ setting('site_title', 'Admin Panel') }}" class="h-12 mb-2">
                @else
                    <h1 class="text-2xl font-bold">{{ setting('site_title', 'Admin Panel') }}</h1>
                @endif
            </div>
            <nav class="mt-4">
                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-3 hover:bg-gray-700 {{ request()->routeIs('admin.dashboard') ? 'bg-gray-700' : '' }}">
                    <i class="fas fa-dashboard mr-2"></i> Dashboard
                </a>
                <a href="{{ route('admin.users.index') }}" class="block px-4 py-3 hover:bg-gray-700 {{ request()->routeIs('admin.users.*') ? 'bg-gray-700' : '' }}">
                    <i class="fas fa-users mr-2"></i> Users
                </a>
                <a href="{{ route('admin.plans.index') }}" class="block px-4 py-3 hover:bg-gray-700 {{ request()->routeIs('admin.plans.*') ? 'bg-gray-700' : '' }}">
                    <i class="fas fa-box mr-2"></i> Subscription Plans
                </a>
                <a href="{{ route('admin.subscriptions.index') }}" class="block px-4 py-3 hover:bg-gray-700 {{ request()->routeIs('admin.subscriptions.*') ? 'bg-gray-700' : '' }}">
                    <i class="fas fa-credit-card mr-2"></i> Subscriptions
                </a>
                <a href="{{ route('admin.image-prompts.index') }}" class="block px-4 py-3 hover:bg-gray-700 {{ request()->routeIs('admin.image-prompts.*') ? 'bg-gray-700' : '' }}">
                    <i class="fas fa-image mr-2"></i> Image Prompts
                </a>
                <a href="{{ route('admin.image-templates.index') }}" class="block px-4 py-3 hover:bg-gray-700 {{ request()->routeIs('admin.image-templates.*') ? 'bg-gray-700' : '' }}">
                    <i class="fas fa-wand-magic-sparkles mr-2"></i> Image Templates
                </a>
                <a href="{{ route('admin.pages.index') }}" class="block px-4 py-3 hover:bg-gray-700 {{ request()->routeIs('admin.pages.*') ? 'bg-gray-700' : '' }}">
                    <i class="fas fa-file-alt mr-2"></i> Pages
                </a>
                <a href="{{ route('admin.settings.index') }}" class="block px-4 py-3 hover:bg-gray-700 {{ request()->routeIs('admin.settings.*') ? 'bg-gray-700' : '' }}">
                    <i class="fas fa-cog mr-2"></i> Settings
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1">
            <!-- Header -->
            <header class="bg-white shadow">
                <div class="flex justify-between items-center px-6 py-4">
                    <h2 class="text-xl font-semibold text-gray-800">@yield('header', 'Dashboard')</h2>
                    <div class="flex items-center space-x-4">
                        <span class="text-gray-600">{{ auth()->user()->name }}</span>
                        <form action="{{ route('admin.logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-red-600 hover:text-red-800">
                                <i class="fas fa-sign-out-alt mr-1"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <main class="p-6">
                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
    
    @stack('scripts')
    
    @if(setting('footer_code'))
        {!! setting('footer_code') !!}
    @endif
</body>
</html>
