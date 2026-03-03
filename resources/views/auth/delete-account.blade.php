<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ setting('site_title', config('app.name')) }} - Delete Account</title>
    
    @if(setting('site_favicon'))
        <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . setting('site_favicon')) }}">
    @endif
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
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
        
        .animate-float {
            animation: float 3s ease-in-out infinite;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 min-h-screen flex items-center justify-center p-4">
    <!-- Background Decoration -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-20 left-20 w-72 h-72 bg-blue-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-float"></div>
        <div class="absolute bottom-20 right-20 w-72 h-72 bg-cyan-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-float" style="animation-delay: 1s;"></div>
        <div class="absolute top-1/2 left-1/2 w-72 h-72 bg-blue-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-float" style="animation-delay: 2s;"></div>
    </div>

    <!-- Delete Account Card -->
    <div class="relative w-full max-w-md animate-fade-in-up">
        <div class="bg-white/10 backdrop-blur-lg rounded-2xl shadow-2xl p-8 border border-white/20">
            <!-- Header -->
            <div class="text-center mb-8">
                @if(setting('site_logo'))
                    <img src="{{ asset('storage/' . setting('site_logo')) }}" alt="Logo" class="h-12 mx-auto mb-4">
                @else
                    <div class="flex items-center justify-center mb-4">
                        <i class="fas fa-user-slash text-4xl text-red-400"></i>
                    </div>
                @endif
                <h2 class="text-3xl font-bold text-white mb-2">Delete Account</h2>
                <p class="text-gray-300">Enter your credentials to delete your account</p>
                <p class="text-sm text-gray-400 mt-1">No login required</p>
            </div>

            <!-- Warning Message -->
            <div class="bg-red-500/20 border border-red-500/50 rounded-lg p-4 mb-6">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-triangle text-red-400 mt-1 mr-3"></i>
                    <div>
                        <p class="text-red-200 text-sm font-semibold mb-1">⚠️ Warning!</p>
                        <p class="text-red-300 text-xs">
                            Deleting your account will remove all your data. Your account will be marked as deleted and cannot be recovered.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Error Messages -->
            @if ($errors->any())
                <div class="bg-red-500/20 border border-red-500/50 rounded-lg p-4 mb-6">
                    <div class="flex items-center text-red-300">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <div class="text-sm">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Delete Account Form -->
            <form method="POST" action="{{ route('account.delete') }}" id="deleteForm">
                @csrf
                
                <!-- Email Input -->
                <div class="mb-6">
                    <label for="email" class="block text-sm font-medium text-gray-200 mb-2">
                        <i class="fas fa-envelope mr-2"></i>Confirm Your Email
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="{{ old('email') }}"
                        required
                        class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition duration-200"
                        placeholder="Enter your email"
                    >
                </div>

                <!-- Password Input -->
                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-gray-200 mb-2">
                        <i class="fas fa-lock mr-2"></i>Confirm Your Password
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required
                            class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition duration-200"
                            placeholder="Enter your password"
                        >
                        <button 
                            type="button"
                            onclick="togglePassword()"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-white transition duration-200"
                        >
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Confirmation Checkbox -->
                <div class="mb-6">
                    <label class="flex items-start cursor-pointer">
                        <input 
                            type="checkbox" 
                            id="confirmDelete" 
                            required
                            class="mt-1 w-4 h-4 text-red-600 bg-white/10 border-white/20 rounded focus:ring-red-500 focus:ring-2"
                        >
                        <span class="ml-3 text-sm text-gray-300">
                            I understand that this action is permanent and cannot be undone. I want to delete my account.
                        </span>
                    </label>
                </div>

                <!-- Buttons -->
                <div class="flex gap-4">
                    <a 
                        href="{{ route('home') }}"
                        class="flex-1 px-6 py-3 bg-white/10 hover:bg-white/20 text-white rounded-lg font-semibold transition duration-200 text-center border border-white/20"
                    >
                        <i class="fas fa-arrow-left mr-2"></i>Cancel
                    </a>
                    <button 
                        type="submit"
                        class="flex-1 px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold transition duration-200 shadow-lg hover:shadow-red-500/50"
                    >
                        <i class="fas fa-trash-alt mr-2"></i>Delete Account
                    </button>
                </div>
            </form>

            <!-- Back to Home Link -->
            <div class="mt-6 text-center">
                <a href="{{ route('home') }}" class="text-sm text-gray-300 hover:text-white transition duration-200">
                    <i class="fas fa-home mr-1"></i>Back to Home
                </a>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Confirm before submission
        document.getElementById('deleteForm').addEventListener('submit', function(e) {
            const confirmed = confirm('Are you absolutely sure you want to delete your account? This action cannot be undone!');
            if (!confirmed) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
