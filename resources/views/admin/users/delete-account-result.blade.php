<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $success ? 'Account Deleted' : 'Deletion Failed' }} - Delete My Account</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, 
                {{ $success ? '#10b981 0%, #059669 100%' : '#ef4444 0%, #dc2626 100%' }});
            min-height: 100vh;
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .result-icon {
            animation: scaleIn 0.5s ease-out;
        }
        @keyframes scaleIn {
            from {
                transform: scale(0);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }
        .fade-in {
            animation: fadeIn 0.8s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="flex items-center justify-center p-4">
    <div class="glass-effect rounded-2xl shadow-2xl p-8 max-w-2xl w-full fade-in">
        
        @if($success)
            <!-- Success State -->
            <div class="text-center">
                <!-- Success Icon -->
                <div class="result-icon inline-block p-4 bg-green-500 rounded-full mb-6">
                    <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>

                <h1 class="text-4xl font-bold text-white mb-4">✅ Account Deleted Successfully</h1>
                <p class="text-white text-opacity-90 text-lg mb-8">{{ $message }}</p>

                <!-- Deleted User Info -->
                @if(isset($deleted_user))
                <div class="bg-white bg-opacity-10 rounded-xl p-6 mb-8 text-left">
                    <h3 class="text-white font-bold text-xl mb-4">🗑️ Deleted Account Information:</h3>
                    <div class="space-y-3 text-white">
                        <div class="flex justify-between items-center p-3 bg-black bg-opacity-20 rounded-lg">
                            <span class="font-semibold">User ID:</span>
                            <span class="font-mono">{{ $deleted_user['id'] }}</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-black bg-opacity-20 rounded-lg">
                            <span class="font-semibold">Name:</span>
                            <span>{{ $deleted_user['name'] }}</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-black bg-opacity-20 rounded-lg">
                            <span class="font-semibold">Email:</span>
                            <span>{{ $deleted_user['email'] }}</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-black bg-opacity-20 rounded-lg">
                            <span class="font-semibold">Role:</span>
                            <span class="uppercase">{{ $deleted_user['role'] }}</span>
                        </div>
                    </div>
                </div>
                @endif

                <!-- What Happened -->
                <div class="bg-white bg-opacity-10 rounded-xl p-6 mb-8 text-left">
                    <h3 class="text-white font-bold text-lg mb-3">✓ Actions Completed:</h3>
                    <ul class="space-y-2 text-white text-sm">
                        <li class="flex items-center space-x-2">
                            <span class="text-green-300">✓</span>
                            <span>User account permanently deleted</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <span class="text-green-300">✓</span>
                            <span>All authentication tokens revoked</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <span class="text-green-300">✓</span>
                            <span>User data removed from database</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <span class="text-green-300">✓</span>
                            <span>Account cannot be recovered</span>
                        </li>
                    </ul>
                </div>

                <!-- Action Buttons -->
                <div class="flex space-x-4">
                    <a href="/" class="flex-1 bg-white text-green-600 font-bold py-4 px-6 rounded-lg shadow-lg hover:bg-opacity-90 transition text-center">
                        🏠 Go to Homepage
                    </a>
                    <a href="/register" class="flex-1 bg-blue-500 text-white font-bold py-4 px-6 rounded-lg shadow-lg hover:bg-blue-600 transition text-center">
                        📝 Create New Account
                    </a>
                </div>
            </div>

        @else
            <!-- Error State -->
            <div class="text-center">
                <!-- Error Icon -->
                <div class="result-icon inline-block p-4 bg-red-600 rounded-full mb-6">
                    <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>

                <h1 class="text-4xl font-bold text-white mb-4">❌ Account Deletion Failed</h1>
                <p class="text-white text-opacity-90 text-lg mb-8">{{ $message }}</p>

                <!-- Error Details -->
                @if(isset($error))
                <div class="bg-white bg-opacity-10 rounded-xl p-6 mb-8 text-left">
                    <h3 class="text-white font-bold text-lg mb-3">⚠️ Error Details:</h3>
                    <div class="bg-red-900 bg-opacity-30 rounded-lg p-4 border border-red-500 border-opacity-50">
                        <p class="text-white font-mono text-sm">{{ $error }}</p>
                    </div>
                </div>
                @endif

                <!-- User Info (if available) -->
                @if(isset($user))
                <div class="bg-white bg-opacity-10 rounded-xl p-6 mb-8 text-left">
                    <h3 class="text-white font-bold text-lg mb-3">👤 User Information:</h3>
                    <div class="space-y-2 text-white text-sm">
                        <p><strong>Name:</strong> {{ $user->name }}</p>
                        <p><strong>Email:</strong> {{ $user->email }}</p>
                        <p><strong>Role:</strong> {{ $user->role }}</p>
                    </div>
                </div>
                @endif

                <!-- Common Issues -->
                <div class="bg-white bg-opacity-10 rounded-xl p-6 mb-8 text-left">
                    <h3 class="text-white font-bold text-lg mb-3">🔍 Common Issues:</h3>
                    <ul class="space-y-2 text-white text-sm">
                        <li class="flex items-start space-x-2">
                            <span class="text-red-300 flex-shrink-0">✗</span>
                            <span><strong>Invalid Credentials:</strong> Email or password is incorrect</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <span class="text-red-300 flex-shrink-0">✗</span>
                            <span><strong>Account Not Found:</strong> No account exists with this email</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <span class="text-red-300 flex-shrink-0">✗</span>
                            <span><strong>Admin Account:</strong> Admin accounts cannot be deleted via this method</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <span class="text-red-300 flex-shrink-0">✗</span>
                            <span><strong>Missing Parameters:</strong> Email or password not provided</span>
                        </li>
                    </ul>
                </div>

                <!-- Action Buttons -->
                <div class="flex space-x-4">
                    <a href="/delete-my-account.html" class="flex-1 bg-white text-red-600 font-bold py-4 px-6 rounded-lg shadow-lg hover:bg-opacity-90 transition text-center">
                        🔄 Try Again
                    </a>
                    <a href="/" class="flex-1 bg-gray-600 text-white font-bold py-4 px-6 rounded-lg shadow-lg hover:bg-gray-700 transition text-center">
                        🏠 Go to Homepage
                    </a>
                </div>
            </div>
        @endif

        <!-- Technical Information -->
        <div class="mt-8 pt-6 border-t border-white border-opacity-20">
            <details class="text-white text-sm">
                <summary class="cursor-pointer font-semibold mb-2">🔧 Technical Information</summary>
                <div class="mt-4 space-y-2 text-xs bg-black bg-opacity-20 rounded-lg p-4">
                    <p><strong>Timestamp:</strong> {{ now()->format('Y-m-d H:i:s') }}</p>
                    <p><strong>Method:</strong> GET</p>
                    <p><strong>Endpoint:</strong> /delete-my-account</p>
                    <p><strong>Status:</strong> {{ $success ? 'SUCCESS' : 'FAILED' }}</p>
                    @if(isset($deleted_user))
                    <p><strong>Deleted User ID:</strong> {{ $deleted_user['id'] }}</p>
                    @endif
                </div>
            </details>
        </div>

        <!-- API Info -->
        <div class="mt-6 p-4 bg-blue-500 bg-opacity-20 rounded-lg border border-blue-400 border-opacity-30">
            <p class="text-white text-sm">
                <strong>💡 API Endpoint:</strong> 
                <code class="bg-black bg-opacity-30 px-2 py-1 rounded text-xs">
                    GET /api/delete-account-via-credentials?email={email}&password={password}
                </code>
            </p>
        </div>
    </div>

    <script>
        // Auto-scroll to top on page load
        window.scrollTo(0, 0);

        // Add confetti effect on success (optional)
        @if($success)
        console.log('✅ Account successfully deleted');
        @else
        console.log('❌ Account deletion failed: {{ $message }}');
        @endif
    </script>
</body>
</html>
