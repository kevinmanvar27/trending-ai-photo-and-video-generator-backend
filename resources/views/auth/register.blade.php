<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ setting('site_title', config('app.name')) }} - Register</title>
    
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

    <div class="max-w-md w-full relative z-10 animate-fade-in-up">
        <!-- Logo/Brand -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-3xl shadow-2xl mb-4">
                <i class="fas fa-magic text-white text-3xl"></i>
            </div>
            <h1 class="text-4xl font-bold text-white mb-2">Create Account</h1>
            <p class="text-gray-300">Join us and start creating amazing content</p>
        </div>

        <!-- Register Card -->
        <div class="bg-white/10 backdrop-blur-xl rounded-3xl shadow-2xl p-8 border border-white/20">
            @if($errors->any())
                <div class="bg-red-500/20 border border-red-500/50 text-red-100 px-4 py-3 rounded-2xl mb-6 backdrop-blur-sm">
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-exclamation-circle text-xl mt-0.5"></i>
                        <div class="flex-1">
                            @foreach($errors->all() as $error)
                                <p class="text-sm">{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <form action="{{ route('register') }}" method="POST" class="space-y-5">
                @csrf
                
                <!-- Name Field -->
                <div>
                    <label class="block text-white text-sm font-bold mb-2" for="name">
                        <i class="fas fa-user mr-2"></i>Full Name
                    </label>
                    <input type="text" 
                           name="name" 
                           id="name" 
                           value="{{ old('name') }}" 
                           required
                           class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent backdrop-blur-sm transition-all duration-300"
                           placeholder="Enter your full name">
                </div>

                <!-- Email Field -->
                <div>
                    <label class="block text-white text-sm font-bold mb-2" for="email">
                        <i class="fas fa-envelope mr-2"></i>Email Address
                    </label>
                    <input type="email" 
                           name="email" 
                           id="email" 
                           value="{{ old('email') }}" 
                           required
                           class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent backdrop-blur-sm transition-all duration-300"
                           placeholder="Enter your email">
                </div>

                <!-- Password Field -->
                <div>
                    <label class="block text-white text-sm font-bold mb-2" for="password">
                        <i class="fas fa-lock mr-2"></i>Password
                    </label>
                    <input type="password" 
                           name="password" 
                           id="password" 
                           required
                           class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent backdrop-blur-sm transition-all duration-300"
                           placeholder="Create a strong password">
                </div>

                <!-- Confirm Password Field -->
                <div>
                    <label class="block text-white text-sm font-bold mb-2" for="password_confirmation">
                        <i class="fas fa-lock mr-2"></i>Confirm Password
                    </label>
                    <input type="password" 
                           name="password_confirmation" 
                           id="password_confirmation" 
                           required
                           class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent backdrop-blur-sm transition-all duration-300"
                           placeholder="Confirm your password">
                </div>

                <!-- Terms & Conditions -->
                <div class="flex items-start">
                    <input type="checkbox" 
                           name="terms" 
                           id="terms"
                           required
                           class="w-5 h-5 mt-0.5 rounded border-white/20 bg-white/10 text-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-offset-0 cursor-pointer">
                    <label for="terms" class="ml-2 text-sm text-gray-300 cursor-pointer">
                        I agree to the <a href="#" class="text-cyan-400 hover:text-cyan-300">Terms & Conditions</a> and <a href="#" class="text-cyan-400 hover:text-cyan-300">Privacy Policy</a>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 text-white font-bold py-4 rounded-xl transition-all duration-300 shadow-lg hover:shadow-blue-500/50 transform hover:scale-105 flex items-center justify-center space-x-2">
                    <i class="fas fa-user-plus"></i>
                    <span>Create Account</span>
                </button>

                <!-- Divider -->
                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-white/20"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-4 bg-transparent text-gray-400">Or sign up with</span>
                    </div>
                </div>

                <!-- Social Login (Optional) -->
                <div class="grid grid-cols-2 gap-4">
                    <button type="button" class="bg-white/10 hover:bg-white/20 border border-white/20 text-white py-3 rounded-xl transition-all duration-300 flex items-center justify-center space-x-2">
                        <i class="fab fa-google"></i>
                        <span>Google</span>
                    </button>
                    <button type="button" class="bg-white/10 hover:bg-white/20 border border-white/20 text-white py-3 rounded-xl transition-all duration-300 flex items-center justify-center space-x-2">
                        <i class="fab fa-facebook-f"></i>
                        <span>Facebook</span>
                    </button>
                </div>

                <!-- Login Link -->
                <div class="text-center pt-4">
                    <p class="text-gray-300">
                        Already have an account? 
                        <a href="{{ route('login') }}" class="text-cyan-400 hover:text-cyan-300 font-bold transition-colors duration-300">Sign In</a>
                    </p>
                </div>
            </form>
        </div>

        <!-- Back to Home -->
        <div class="text-center mt-6">
            <a href="{{ route('image-submission.index') }}" class="text-gray-400 hover:text-white transition-colors duration-300 inline-flex items-center space-x-2">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Home</span>
            </a>
        </div>
    </div>
</body>
</html>
