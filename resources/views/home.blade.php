<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-bold">Radhika App</h1>
                </div>
                <div class="flex items-center space-x-4">
                    @auth
                        <span class="text-gray-700">Welcome, {{ auth()->user()->name }}</span>
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                                Logout
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-gray-900">Login</a>
                        <a href="{{ route('register') }}" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            Register
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @auth
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-2xl font-bold mb-4">Dashboard</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-blue-50 p-4 rounded">
                        <h3 class="font-semibold text-gray-700">Total Time Spent</h3>
                        <p class="text-2xl font-bold text-blue-600">{{ $user->formatted_time_spent }}</p>
                    </div>

                    <div class="bg-green-50 p-4 rounded">
                        <h3 class="font-semibold text-gray-700">Last Activity</h3>
                        <p class="text-lg text-green-600">
                            {{ $user->last_activity_at ? $user->last_activity_at->diffForHumans() : 'Never' }}
                        </p>
                    </div>

                    <div class="bg-purple-50 p-4 rounded">
                        <h3 class="font-semibold text-gray-700">Account Status</h3>
                        <p class="text-lg text-purple-600">
                            @if($user->is_suspended)
                                <span class="text-red-600">Suspended</span>
                            @else
                                <span class="text-green-600">Active</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            @if($activeSubscription)
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-xl font-bold mb-4">Your Subscription</h3>
                    <div class="border-l-4 border-green-500 pl-4">
                        <h4 class="font-semibold text-lg">{{ $activeSubscription->plan->name }}</h4>
                        <p class="text-gray-600">Remaining Coins: <span class="font-bold text-green-600">{{ number_format($activeSubscription->remaining_coins) }}</span></p>
                        <p class="text-sm text-gray-500">Used: {{ number_format($activeSubscription->coins_used) }} / {{ number_format($activeSubscription->plan->coins) }} coins</p>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-xl font-bold mb-4">No Active Subscription</h3>
                    <p class="text-gray-600">You don't have an active subscription. Contact admin to subscribe.</p>
                </div>
            @endif
        @else
            <div class="text-center py-12">
                <h1 class="text-4xl font-bold text-gray-900 mb-4">Welcome to Radhika App</h1>
                <p class="text-xl text-gray-600 mb-8">Please login or register to continue</p>
                <div class="space-x-4">
                    <a href="{{ route('login') }}" class="bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600">
                        Login
                    </a>
                    <a href="{{ route('register') }}" class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600">
                        Register
                    </a>
                </div>
            </div>
        @endauth
    </main>
</body>
</html>
