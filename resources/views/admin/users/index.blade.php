@extends('admin.layout')

@section('title', 'Users')
@section('header', 'User Management')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <div class="flex items-center space-x-4">
            <h3 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-users mr-2"></i> All Users
            </h3>
            <!-- Status Filter Dropdown -->
            <div class="relative">
                <select id="statusFilter" onchange="filterByStatus(this.value)" class="border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Users</option>
                    <option value="not_deleted" {{ request('status') === 'not_deleted' ? 'selected' : '' }}>Active Only</option>
                    <option value="deleted" {{ request('status') === 'deleted' ? 'selected' : '' }}>Deleted Only</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
            </div>
        </div>
        <a href="{{ route('admin.users.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            <i class="fas fa-plus mr-1"></i> Add User
        </a>
    </div>

    <div class="p-6">
        <div class="overflow-x-auto">
            <table id="usersTable" class="min-w-full display" style="width:100%">
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
                        <tr class="{{ $user->trashed() ? 'bg-red-50' : '' }}">
                            <td class="px-6 py-4 {{ $user->trashed() ? 'text-gray-500' : '' }}">
                                {{ $user->name }}
                                @if($user->trashed())
                                    <i class="fas fa-trash text-red-500 ml-1" title="Deleted"></i>
                                @endif
                            </td>
                            <td class="px-6 py-4 {{ $user->trashed() ? 'text-gray-500' : '' }}">{{ $user->email }}</td>
                            <td class="px-6 py-4">
                                @if($user->trashed())
                                    <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-800">
                                        <i class="fas fa-trash mr-1"></i> Deleted
                                    </span>
                                @elseif($user->is_suspended)
                                    <span class="px-2 py-1 text-xs rounded bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-ban mr-1"></i> Suspended
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i> Active
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 {{ $user->trashed() ? 'text-gray-500' : '' }}">
                                @if($user->activeSubscription)
                                    <span class="text-sm">{{ $user->activeSubscription->plan->name }}</span>
                                @else
                                    <span class="text-gray-400">No subscription</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 {{ $user->trashed() ? 'text-gray-500' : '' }}">{{ $user->formatted_time_spent }}</td>
                            <td class="px-6 py-4 {{ $user->trashed() ? 'text-gray-500' : '' }}" data-order="{{ $user->created_at->timestamp }}">
                                {{ $user->created_at->format('M d, Y') }}
                                @if($user->trashed())
                                    <br><small class="text-red-600">Deleted: {{ $user->deleted_at->format('M d, Y') }}</small>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    @if($user->trashed())
                                        <!-- Actions for deleted users -->
                                        <form action="{{ route('admin.users.restore', $user->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-green-600 hover:text-green-800" title="Restore User">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        </form>
                                        <button onclick="showForceDeleteModal({{ $user->id }})" class="text-red-600 hover:text-red-800" title="Permanently Delete">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    @else
                                        <!-- Actions for active users -->
                                        <a href="{{ route('admin.users.show', $user->id) }}" class="text-blue-600 hover:text-blue-800" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.users.edit', $user->id) }}" class="text-green-600 hover:text-green-800" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($user->is_suspended)
                                            <form action="{{ route('admin.users.unsuspend', $user->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-green-600 hover:text-green-800" title="Unsuspend">
                                                    <i class="fas fa-user-check"></i>
                                                </button>
                                            </form>
                                        @else
                                            <button onclick="showSuspendModal({{ $user->id }})" class="text-yellow-600 hover:text-yellow-800" title="Suspend">
                                                <i class="fas fa-user-slash"></i>
                                            </button>
                                        @endif
                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this user? They can be restored later.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
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

<!-- Force Delete Modal -->
<div id="forceDeleteModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="text-center">
            <i class="fas fa-exclamation-triangle text-red-500 text-5xl mb-4"></i>
            <h3 class="text-lg font-bold mb-4 text-red-600">Permanently Delete User</h3>
            <p class="text-gray-600 mb-6">
                This action <strong>cannot be undone</strong>. The user and all their data will be permanently removed from the database.
            </p>
            <form id="forceDeleteForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="flex justify-center space-x-2">
                    <button type="button" onclick="closeForceDeleteModal()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                        Cancel
                    </button>
                    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                        <i class="fas fa-trash-alt mr-1"></i> Permanently Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

<!-- DataTables Buttons Extension -->
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<style>
    /* Custom DataTables Styling */
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        padding: 0.5rem 0.75rem;
        margin-left: 0.5rem;
    }
    
    .dataTables_wrapper .dataTables_length select {
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        padding: 0.5rem 0.75rem;
        margin: 0 0.5rem;
    }
    
    .dataTables_wrapper .dataTables_info {
        padding-top: 1rem;
        color: #6b7280;
    }
    
    .dataTables_wrapper .dataTables_paginate {
        padding-top: 1rem;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0.5rem 0.75rem;
        margin: 0 0.125rem;
        border-radius: 0.375rem;
        border: 1px solid #d1d5db;
        background: white;
        color: #374151;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #f3f4f6;
        border-color: #9ca3af;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #3b82f6;
        color: white;
        border-color: #3b82f6;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    .dt-buttons {
        margin-bottom: 1rem;
    }
    
    .dt-button {
        background: #3b82f6;
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 0.375rem;
        margin-right: 0.5rem;
        cursor: pointer;
    }
    
    .dt-button:hover {
        background: #2563eb;
    }
    
    table.dataTable thead th {
        border-bottom: 2px solid #e5e7eb;
    }
    
    table.dataTable tbody tr:hover {
        background-color: #f9fafb;
    }
</style>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#usersTable').DataTable({
        // Enable responsive design
        responsive: true,
        
        // Page length options
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        
        // Default page length
        pageLength: 10,
        
        // Enable ordering
        order: [[5, 'desc']], // Sort by "Joined" column (index 5) in descending order
        
        // Column definitions
        columnDefs: [
            {
                // Disable sorting on Actions column
                targets: [6],
                orderable: false,
                searchable: false
            },
            {
                // Custom sorting for Status column
                targets: [2],
                type: 'html'
            }
        ],
        
        // Language customization
        language: {
            search: "Search users:",
            lengthMenu: "Show _MENU_ users per page",
            info: "Showing _START_ to _END_ of _TOTAL_ users",
            infoEmpty: "No users available",
            infoFiltered: "(filtered from _MAX_ total users)",
            zeroRecords: "No matching users found",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        },
        
        // Add export buttons
        dom: 'Blfrtip',
        buttons: [
            {
                extend: 'copy',
                text: '<i class="fas fa-copy mr-1"></i> Copy',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5] // Exclude Actions column
                }
            },
            {
                extend: 'csv',
                text: '<i class="fas fa-file-csv mr-1"></i> CSV',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5]
                }
            },
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel mr-1"></i> Excel',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5]
                }
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf mr-1"></i> PDF',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5]
                },
                orientation: 'landscape',
                pageSize: 'A4'
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print mr-1"></i> Print',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5]
                }
            }
        ],
        
        // Initialize with existing data
        deferRender: true
    });
});

// Suspend Modal Functions
function showSuspendModal(userId) {
    document.getElementById('suspendModal').classList.remove('hidden');
    document.getElementById('suspendForm').action = `/admin/users/${userId}/suspend`;
}

function closeSuspendModal() {
    document.getElementById('suspendModal').classList.add('hidden');
}

// Force Delete Modal Functions
function showForceDeleteModal(userId) {
    document.getElementById('forceDeleteModal').classList.remove('hidden');
    document.getElementById('forceDeleteForm').action = `/admin/users/${userId}/force-delete`;
}

function closeForceDeleteModal() {
    document.getElementById('forceDeleteModal').classList.add('hidden');
}

// Filter by status function
function filterByStatus(status) {
    const url = new URL(window.location.href);
    if (status) {
        url.searchParams.set('status', status);
    } else {
        url.searchParams.delete('status');
    }
    window.location.href = url.toString();
}

// Close modals when clicking outside
document.getElementById('suspendModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeSuspendModal();
    }
});

document.getElementById('forceDeleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeForceDeleteModal();
    }
});
</script>
@endpush
