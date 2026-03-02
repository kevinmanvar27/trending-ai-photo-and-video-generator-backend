@extends('admin.layout')

@section('title', 'Deleted Users')
@section('header', 'Deleted Users Management')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <div class="flex items-center space-x-4">
            <form action="{{ route('admin.users.deleted') }}" method="GET" class="flex space-x-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search deleted users..." 
                    class="border rounded px-3 py-2">
                <button type="submit" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    <i class="fas fa-search mr-1"></i> Search
                </button>
            </form>
        </div>
        <a href="{{ route('admin.users.index') }}" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            <i class="fas fa-arrow-left mr-1"></i> Back to Active Users
        </a>
    </div>

    @if(session('success'))
        <div class="mx-6 mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mx-6 mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            {{ session('error') }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Deleted At</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Days Ago</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($deletedUsers as $user)
                    <tr>
                        <td class="px-6 py-4">{{ $user->name }}</td>
                        <td class="px-6 py-4">{{ $user->email }}</td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-gray-600">
                                {{ $user->deleted_at->format('M d, Y H:i') }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-gray-600">
                                {{ $user->deleted_at->diffForHumans() }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex space-x-2">
                                <!-- Restore Button -->
                                <form action="{{ route('admin.users.restore', $user->id) }}" method="POST" 
                                    onsubmit="return confirm('Are you sure you want to restore this user? They will be able to login again.');">
                                    @csrf
                                    <button type="submit" class="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600">
                                        <i class="fas fa-undo mr-1"></i> Restore
                                    </button>
                                </form>

                                <!-- Permanent Delete Button -->
                                <form action="{{ route('admin.users.force-delete', $user->id) }}" method="POST" 
                                    onsubmit="return confirm('⚠️ WARNING: This will PERMANENTLY delete this user and all their data. This action CANNOT be undone. Are you absolutely sure?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700">
                                        <i class="fas fa-trash-alt mr-1"></i> Delete Permanently
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2"></i>
                            <p>No deleted users found.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($deletedUsers->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $deletedUsers->links() }}
        </div>
    @endif
</div>

<!-- Information Panel -->
<div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
    <h3 class="text-lg font-semibold text-blue-900 mb-2">
        <i class="fas fa-info-circle mr-2"></i> About Deleted Users
    </h3>
    <ul class="list-disc list-inside text-sm text-blue-800 space-y-1">
        <li><strong>Soft Delete:</strong> Users are not permanently removed from the database, just marked as deleted.</li>
        <li><strong>Restore:</strong> You can restore deleted users, allowing them to login again with their original data.</li>
        <li><strong>Permanent Delete:</strong> Removes the user and all their data permanently. This action cannot be undone.</li>
        <li><strong>Auto-Purge:</strong> Run <code class="bg-blue-100 px-1 rounded">php artisan users:purge-deleted --days=90</code> to permanently delete users older than 90 days.</li>
    </ul>
</div>
@endsection
