@extends('admin.layout')

@section('title', 'User Activity')
@section('header', 'User Activity - ' . $user->name)

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.users.show', $user->id) }}" class="text-blue-600 hover:text-blue-800">
        <i class="fas fa-arrow-left mr-1"></i> Back to User Details
    </a>
</div>

<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold">Activity Logs</h3>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Session Start</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Session End</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duration</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Device Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP Address</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($activities as $activity)
                    <tr>
                        <td class="px-6 py-4">{{ $activity->session_start->format('M d, Y H:i:s') }}</td>
                        <td class="px-6 py-4">
                            {{ $activity->session_end ? $activity->session_end->format('M d, Y H:i:s') : 'Active' }}
                        </td>
                        <td class="px-6 py-4">{{ $activity->formatted_duration }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-800">
                                {{ ucfirst($activity->device_type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">{{ $activity->ip_address }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">No activity logs found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-6 py-4">
        {{ $activities->links() }}
    </div>
</div>
@endsection
