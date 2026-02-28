@extends('admin.layout')

@section('title', 'Users')
@section('header', 'User Management')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <div class="flex items-center space-x-4">
            <form action="{{ route('admin.users.index') }}" method="GET" class="flex space-x-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search users..." 
                    class="border rounded px-3 py-2">
                <select name="status" class="border rounded px-3 py-2">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
                <button type="submit" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Filter
                </button>
            </form>
        </div>
        <a href="{{ route('admin.users.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            <i class="fas fa-plus mr-1"></i> Add User
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subscription</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time Spent</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Joined</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($users as $user)
                    <tr>
                        <td class="px-6 py-4">{{ $user->name }}</td>
                        <td class="px-6 py-4">{{ $user->email }}</td>
                        <td class="px-6 py-4">
                            @if($user->is_suspended)
                                <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-800">Suspended</span>
                            @else
                                <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-800">Active</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($user->activeSubscription)
                                <span class="text-sm">{{ $user->activeSubscription->plan->name }}</span>
                            @else
                                <span class="text-gray-400">No subscription</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">{{ $user->formatted_time_spent }}</td>
                        <td class="px-6 py-4">{{ $user->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-4">
                            <div class="flex space-x-2">
                                <a href="{{ route('admin.users.show', $user->id) }}" class="text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="text-green-600 hover:text-green-800">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($user->is_suspended)
                                    <form action="{{ route('admin.users.unsuspend', $user->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-green-600 hover:text-green-800">
                                            <i class="fas fa-user-check"></i>
                                        </button>
                                    </form>
                                @else
                                    <button onclick="showSuspendModal({{ $user->id }})" class="text-yellow-600 hover:text-yellow-800">
                                        <i class="fas fa-user-slash"></i>
                                    </button>
                                @endif
                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">No users found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-6 py-4">
        {{ $users->links() }}
    </div>
</div>

<!-- Suspend Modal -->
<div id="suspendModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <h3 class="text-lg font-bold mb-4">Suspend User</h3>
        <form id="suspendForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Reason for Suspension</label>
                <textarea name="suspension_reason" required rows="4" 
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700"></textarea>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeSuspendModal()" class="bg-gray-500 text-white px-4 py-2 rounded">
                    Cancel
                </button>
                <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded">
                    Suspend
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showSuspendModal(userId) {
    document.getElementById('suspendModal').classList.remove('hidden');
    document.getElementById('suspendForm').action = `/admin/users/${userId}/suspend`;
}

function closeSuspendModal() {
    document.getElementById('suspendModal').classList.add('hidden');
}
</script>
@endsection
