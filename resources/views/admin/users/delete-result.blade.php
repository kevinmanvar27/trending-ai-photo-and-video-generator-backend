@extends('admin.layout')

@section('title', 'Delete User Result')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <!-- Result Card -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-4 {{ $success ? 'bg-green-500' : 'bg-red-500' }} text-white">
                <div class="flex items-center">
                    @if($success)
                        <i class="fas fa-check-circle text-3xl mr-4"></i>
                        <div>
                            <h1 class="text-2xl font-bold">Success!</h1>
                            <p class="text-sm opacity-90">User deleted successfully</p>
                        </div>
                    @else
                        <i class="fas fa-exclamation-circle text-3xl mr-4"></i>
                        <div>
                            <h1 class="text-2xl font-bold">Error!</h1>
                            <p class="text-sm opacity-90">Failed to delete user</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Content -->
            <div class="p-6">
                <!-- Message -->
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-2">Message</h2>
                    <p class="text-gray-700 bg-gray-50 p-4 rounded-lg border border-gray-200">
                        {{ $message }}
                    </p>
                </div>

                @if($success && isset($deleted_user))
                    <!-- Deleted User Information -->
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-3">Deleted User Information</h2>
                        <div class="bg-gray-50 rounded-lg border border-gray-200 overflow-hidden">
                            <table class="w-full">
                                <tbody>
                                    <tr class="border-b border-gray-200">
                                        <td class="px-4 py-3 font-semibold text-gray-700 bg-gray-100 w-1/3">User ID</td>
                                        <td class="px-4 py-3 text-gray-800">{{ $deleted_user['id'] }}</td>
                                    </tr>
                                    <tr class="border-b border-gray-200">
                                        <td class="px-4 py-3 font-semibold text-gray-700 bg-gray-100">Name</td>
                                        <td class="px-4 py-3 text-gray-800">{{ $deleted_user['name'] }}</td>
                                    </tr>
                                    <tr class="border-b border-gray-200">
                                        <td class="px-4 py-3 font-semibold text-gray-700 bg-gray-100">Email</td>
                                        <td class="px-4 py-3 text-gray-800">{{ $deleted_user['email'] }}</td>
                                    </tr>
                                    <tr>
                                        <td class="px-4 py-3 font-semibold text-gray-700 bg-gray-100">Role</td>
                                        <td class="px-4 py-3 text-gray-800">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $deleted_user['role'] === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                                {{ ucfirst($deleted_user['role']) }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                            <div>
                                <h3 class="font-semibold text-blue-800 mb-1">What was deleted:</h3>
                                <ul class="text-sm text-blue-700 space-y-1">
                                    <li>✓ User account record</li>
                                    <li>✓ All authentication tokens</li>
                                    <li>✓ User session data</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                @if(!$success && isset($error))
                    <!-- Error Details -->
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-2">Error Details</h2>
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <p class="text-red-700 font-mono text-sm">{{ $error }}</p>
                        </div>
                    </div>

                    @if(isset($user))
                        <!-- User Information (if found but couldn't delete) -->
                        <div class="mb-6">
                            <h2 class="text-lg font-semibold text-gray-800 mb-3">User Information</h2>
                            <div class="bg-gray-50 rounded-lg border border-gray-200 overflow-hidden">
                                <table class="w-full">
                                    <tbody>
                                        <tr class="border-b border-gray-200">
                                            <td class="px-4 py-3 font-semibold text-gray-700 bg-gray-100 w-1/3">User ID</td>
                                            <td class="px-4 py-3 text-gray-800">{{ $user->id }}</td>
                                        </tr>
                                        <tr class="border-b border-gray-200">
                                            <td class="px-4 py-3 font-semibold text-gray-700 bg-gray-100">Name</td>
                                            <td class="px-4 py-3 text-gray-800">{{ $user->name }}</td>
                                        </tr>
                                        <tr class="border-b border-gray-200">
                                            <td class="px-4 py-3 font-semibold text-gray-700 bg-gray-100">Email</td>
                                            <td class="px-4 py-3 text-gray-800">{{ $user->email }}</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-3 font-semibold text-gray-700 bg-gray-100">Role</td>
                                            <td class="px-4 py-3 text-gray-800">
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                                    {{ ucfirst($user->role) }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                @endif

                <!-- Actions -->
                <div class="flex gap-3 mt-6">
                    <a href="{{ route('admin.users.index') }}" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 text-center">
                        <i class="fas fa-users mr-2"></i>
                        Back to Users List
                    </a>
                    <a href="{{ route('admin.dashboard') }}" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 text-center">
                        <i class="fas fa-dashboard mr-2"></i>
                        Go to Dashboard
                    </a>
                </div>
            </div>
        </div>

        <!-- Usage Information -->
        <div class="mt-6 bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
                <i class="fas fa-book text-blue-500 mr-2"></i>
                How to Use This Feature
            </h2>
            <div class="space-y-3 text-sm text-gray-700">
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <p class="font-semibold mb-2">URL Format:</p>
                    <code class="bg-gray-800 text-green-400 px-3 py-2 rounded block overflow-x-auto">
                        {{ url('admin/delete-user?user_id=USER_ID') }}
                    </code>
                </div>
                
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <p class="font-semibold mb-2">Example:</p>
                    <code class="bg-gray-800 text-green-400 px-3 py-2 rounded block overflow-x-auto">
                        {{ url('admin/delete-user?user_id=123') }}
                    </code>
                </div>

                <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-triangle text-yellow-600 mt-1 mr-3"></i>
                        <div>
                            <h3 class="font-semibold text-yellow-800 mb-1">Important Notes:</h3>
                            <ul class="text-sm text-yellow-700 space-y-1">
                                <li>• This action is permanent and cannot be undone</li>
                                <li>• Admin users cannot be deleted via this method</li>
                                <li>• You must be logged in as an admin to use this feature</li>
                                <li>• All user tokens will be invalidated</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
