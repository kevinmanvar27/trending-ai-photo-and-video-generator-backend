<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ setting('site_title', config('app.name')) }} - Dashboard</title>
    
    @if(setting('site_favicon'))
        <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . setting('site_favicon')) }}">
    @endif
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @if(setting('header_code'))
        {!! setting('header_code') !!}
    @endif
    
    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes gradient {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-20px);
            }
        }
        
        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out forwards;
        }
        
        .animate-gradient {
            background-size: 200% 200%;
            animation: gradient 5s ease infinite;
        }
        
        .animate-float {
            animation: float 3s ease-in-out infinite;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-slate-50 min-h-screen">
    @include('partials.header')

    <main class="container mx-auto px-4 lg:px-8 py-12">
        @if(session('success'))
        <div class="bg-gradient-to-r from-green-500 to-emerald-500 text-white px-6 py-4 rounded-2xl mb-8 shadow-lg animate-fade-in-up flex items-center space-x-3">
            <i class="fas fa-check-circle text-2xl"></i>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
        @endif

        @auth
            <!-- Welcome Banner -->
            <div class="bg-gradient-to-r from-blue-600 via-cyan-600 to-blue-600 rounded-3xl p-1 mb-12 shadow-2xl animate-fade-in-up animate-gradient">
                <div class="bg-white rounded-3xl p-8 md:p-12">
                    <div class="flex flex-col md:flex-row items-center justify-between">
                        <div class="mb-6 md:mb-0">
                            <h1 class="text-4xl md:text-5xl font-bold text-gray-800 mb-3">
                                Welcome back, <span class="bg-gradient-to-r from-blue-600 to-cyan-600 bg-clip-text text-transparent">{{ auth()->user()->name }}</span>! 👋
                            </h1>
                            <p class="text-gray-600 text-lg">Ready to create something amazing today?</p>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="w-24 h-24 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-3xl flex items-center justify-center shadow-2xl animate-float">
                                <span class="text-white text-4xl font-bold">{{ substr(auth()->user()->name, 0, 1) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dashboard Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12 animate-fade-in-up" style="animation-delay: 0.1s;">
                <!-- Time Spent Card -->
                <div class="bg-white rounded-2xl shadow-xl p-6 hover:shadow-2xl transition-all duration-300 group border-2 border-transparent hover:border-blue-500">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-clock text-white text-2xl"></i>
                        </div>
                        <span class="text-blue-500 text-sm font-bold bg-blue-50 px-3 py-1 rounded-full">Activity</span>
                    </div>
                    <h3 class="text-gray-600 font-semibold mb-2">Total Time Spent</h3>
                    <p class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-blue-700 bg-clip-text text-transparent">
                        {{ $user->formatted_time_spent }}
                    </p>
                </div>

                <!-- Last Activity Card -->
                <div class="bg-white rounded-2xl shadow-xl p-6 hover:shadow-2xl transition-all duration-300 group border-2 border-transparent hover:border-green-500">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-14 h-14 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-history text-white text-2xl"></i>
                        </div>
                        <span class="text-green-500 text-sm font-bold bg-green-50 px-3 py-1 rounded-full">Recent</span>
                    </div>
                    <h3 class="text-gray-600 font-semibold mb-2">Last Activity</h3>
                    <p class="text-xl font-bold text-gray-800">
                        {{ $user->last_activity_at ? $user->last_activity_at->diffForHumans() : 'Never' }}
                    </p>
                </div>

                <!-- Account Status Card -->
                <div class="bg-white rounded-2xl shadow-xl p-6 hover:shadow-2xl transition-all duration-300 group border-2 border-transparent hover:border-blue-500">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-user-shield text-white text-2xl"></i>
                        </div>
                        @if($user->is_suspended)
                            <span class="text-red-500 text-sm font-bold bg-red-50 px-3 py-1 rounded-full">Suspended</span>
                        @else
                            <span class="text-green-500 text-sm font-bold bg-green-50 px-3 py-1 rounded-full">Active</span>
                        @endif
                    </div>
                    <h3 class="text-gray-600 font-semibold mb-2">Account Status</h3>
                    <p class="text-xl font-bold">
                        @if($user->is_suspended)
                            <span class="text-red-600">Suspended</span>
                        @else
                            <span class="text-green-600">Active & Verified</span>
                        @endif
                    </p>
                </div>
            </div>

            <!-- Subscription Card -->
            @if($activeSubscription)
                <div class="bg-gradient-to-br from-blue-600 to-cyan-600 rounded-3xl shadow-2xl p-8 mb-12 animate-fade-in-up" style="animation-delay: 0.2s;">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center space-x-4">
                            <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center">
                                <i class="fas fa-crown text-white text-3xl"></i>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-white mb-1">{{ $activeSubscription->plan->name }}</h3>
                                <p class="text-blue-100">Your active subscription</p>
                            </div>
                        </div>
                        <div class="hidden md:block">
                            <div class="bg-white/20 backdrop-blur-sm px-6 py-3 rounded-full">
                                <span class="text-white font-bold text-lg">Premium</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Remaining Coins -->
                        <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/20">
                            <div class="flex items-center space-x-3 mb-3">
                                <i class="fas fa-coins text-yellow-300 text-2xl"></i>
                                <span class="text-white/80 font-medium">Remaining Coins</span>
                            </div>
                            <p class="text-4xl font-bold text-white">{{ number_format($activeSubscription->remaining_coins) }}</p>
                        </div>
                        
                        <!-- Used Coins -->
                        <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/20">
                            <div class="flex items-center space-x-3 mb-3">
                                <i class="fas fa-chart-line text-blue-300 text-2xl"></i>
                                <span class="text-white/80 font-medium">Coins Used</span>
                            </div>
                            <p class="text-4xl font-bold text-white">{{ number_format($activeSubscription->coins_used) }}</p>
                        </div>
                        
                        <!-- Total Coins -->
                        <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/20">
                            <div class="flex items-center space-x-3 mb-3">
                                <i class="fas fa-wallet text-green-300 text-2xl"></i>
                                <span class="text-white/80 font-medium">Total Coins</span>
                            </div>
                            <p class="text-4xl font-bold text-white">{{ number_format($activeSubscription->plan->coins) }}</p>
                        </div>
                    </div>
                    
                    <!-- Progress Bar -->
                    <div class="mt-6">
                        <div class="flex justify-between text-white/80 text-sm mb-2">
                            <span>Usage Progress</span>
                            <span>{{ round(($activeSubscription->coins_used / $activeSubscription->plan->coins) * 100, 1) }}%</span>
                        </div>
                        <div class="w-full bg-white/20 rounded-full h-3 overflow-hidden">
                            <div class="bg-gradient-to-r from-yellow-400 to-green-400 h-full rounded-full transition-all duration-500" 
                                 style="width: {{ min(($activeSubscription->coins_used / $activeSubscription->plan->coins) * 100, 100) }}%"></div>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-3xl shadow-2xl p-12 text-center animate-fade-in-up" style="animation-delay: 0.2s;">
                    <div class="w-24 h-24 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-crown text-gray-400 text-4xl"></i>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-800 mb-4">No Active Subscription</h3>
                    <p class="text-gray-600 text-lg mb-8">You don't have an active subscription. Contact admin to unlock premium features.</p>
                    <div class="inline-flex items-center space-x-3 bg-gradient-to-r from-blue-100 to-cyan-100 px-6 py-3 rounded-full">
                        <i class="fas fa-info-circle text-blue-600"></i>
                        <span class="text-gray-700 font-medium">Contact Support</span>
                    </div>
                </div>
            @endif

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 animate-fade-in-up" style="animation-delay: 0.3s;">
                <a href="{{ route('image-submission.image-effects') }}" 
                   class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-8 shadow-xl hover:shadow-2xl transition-all duration-300 group transform hover:scale-105">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-bold text-white mb-2">Image Effects</h3>
                            <p class="text-blue-100">Transform your images with AI</p>
                        </div>
                        <i class="fas fa-image text-white text-4xl opacity-50 group-hover:opacity-100 transition-opacity duration-300"></i>
                    </div>
                </a>
                
                <a href="{{ route('image-submission.video-effects') }}" 
                   class="bg-gradient-to-br from-blue-500 to-cyan-600 rounded-2xl p-8 shadow-xl hover:shadow-2xl transition-all duration-300 group transform hover:scale-105">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-bold text-white mb-2">Video Effects</h3>
                            <p class="text-blue-100">Create stunning video content</p>
                        </div>
                        <i class="fas fa-video text-white text-4xl opacity-50 group-hover:opacity-100 transition-opacity duration-300"></i>
                    </div>
                </a>
            </div>
        @else
            <!-- Guest Welcome Section -->
            <div class="text-center py-20 animate-fade-in-up">
                <div class="inline-flex items-center justify-center w-32 h-32 bg-gradient-to-br from-blue-500 via-cyan-500 to-blue-600 rounded-3xl shadow-2xl mb-8 animate-float">
                    <i class="fas fa-magic text-white text-5xl"></i>
                </div>
                <h1 class="text-6xl font-bold bg-gradient-to-r from-blue-600 via-cyan-600 to-blue-600 bg-clip-text text-transparent mb-6 animate-gradient">
                    Welcome to {{ setting('site_title', config('app.name')) }}
                </h1>
                <p class="text-2xl text-gray-600 mb-12 max-w-3xl mx-auto">
                    Transform your media with cutting-edge AI technology. Please login or register to continue your creative journey.
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center space-y-4 sm:space-y-0 sm:space-x-6">
                    <a href="{{ route('login') }}" 
                       class="inline-flex items-center space-x-3 bg-gradient-to-r from-blue-600 to-blue-600 hover:from-blue-700 hover:to-blue-700 text-white px-10 py-5 rounded-full font-bold text-lg transition-all duration-300 shadow-2xl hover:shadow-blue-500/50 transform hover:scale-105">
                        <i class="fas fa-sign-in-alt text-xl"></i>
                        <span>Login</span>
                    </a>
                    <a href="{{ route('register') }}" 
                       class="inline-flex items-center space-x-3 bg-white hover:bg-gray-50 text-gray-800 px-10 py-5 rounded-full font-bold text-lg transition-all duration-300 shadow-2xl border-2 border-gray-200 hover:border-blue-500 transform hover:scale-105">
                        <i class="fas fa-user-plus text-xl"></i>
                        <span>Register</span>
                    </a>
                </div>
            </div>
        @endauth
    </main>

    @include('partials.footer')
</body>
</html>
