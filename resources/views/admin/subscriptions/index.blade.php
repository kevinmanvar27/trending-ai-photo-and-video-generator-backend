@extends('admin.layout')

@section('title', 'Subscriptions')
@section('header', 'User Subscriptions')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <div class="flex items-center space-x-4">
            <h3 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-credit-card mr-2"></i> All Subscriptions
            </h3>
        </div>
        <a href="{{ route('admin.subscriptions.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            <i class="fas fa-plus mr-1"></i> Add Subscription
        </a>
    </div>

    <div class="p-6">
        <div class="overflow-x-auto">
            <table id="subscriptionsTable" class="min-w-full display" style="width:100%">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Coins</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Started</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($subscriptions as $subscription)
                        <tr>
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.users.show', $subscription->user->id) }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $subscription->user->name }}
                                </a>
                            </td>
                            <td class="px-6 py-4">{{ $subscription->plan->name }}</td>
                            <td class="px-6 py-4" data-order="{{ $subscription->plan->price }}">${{ number_format($subscription->plan->price, 2) }}</td>
                            <td class="px-6 py-4" data-order="{{ $subscription->remaining_coins }}">
                                <span class="font-semibold text-green-600">{{ number_format($subscription->remaining_coins) }}</span> / {{ number_format($subscription->plan->coins) }}
                                <span class="text-xs text-gray-500 block">Used: {{ number_format($subscription->coins_used) }}</span>
                            </td>
                            <td class="px-6 py-4" data-order="{{ $subscription->started_at->timestamp }}">{{ $subscription->started_at->format('M d, Y') }}</td>
                            <td class="px-6 py-4">
                                @if($subscription->status === 'active' && !$subscription->isExpired())
                                    <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-800" data-status="active">Active</span>
                                @elseif($subscription->status === 'cancelled')
                                    <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-800" data-status="cancelled">Cancelled</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-800" data-status="expired">Expired</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    @if($subscription->status === 'active' && !$subscription->isExpired())
                                        <form action="{{ route('admin.subscriptions.cancel', $subscription->id) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                            @csrf
                                            <button type="submit" class="text-red-600 hover:text-red-800" title="Cancel Subscription">
                                                <i class="fas fa-times"></i> Cancel
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.subscriptions.renew', $subscription->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="text-green-600 hover:text-green-800" title="Renew Subscription">
                                                <i class="fas fa-redo"></i> Renew
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">No subscriptions found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
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
    var table = $('#subscriptionsTable').DataTable({
        // Enable responsive design
        responsive: true,
        
        // Page length options
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        
        // Default page length
        pageLength: 10,
        
        // Enable ordering
        order: [[4, 'desc']], // Sort by "Started" column (index 4) in descending order
        
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
                targets: [5],
                type: 'html'
            },
            {
                // Numeric sorting for Price
                targets: [2],
                type: 'num'
            },
            {
                // Numeric sorting for Coins (remaining)
                targets: [3],
                type: 'num'
            }
        ],
        
        // Language customization
        language: {
            search: "Search subscriptions:",
            lengthMenu: "Show _MENU_ subscriptions per page",
            info: "Showing _START_ to _END_ of _TOTAL_ subscriptions",
            infoEmpty: "No subscriptions available",
            infoFiltered: "(filtered from _MAX_ total subscriptions)",
            zeroRecords: "No matching subscriptions found",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        },
        
        // Add export buttons and custom filter buttons
        dom: '<"flex justify-between items-center mb-4"<"flex space-x-2"B><"status-filters">>lfrtip',
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
        deferRender: true,
        
        // Add custom initialization
        initComplete: function() {
            // Add status filter buttons
            var statusFilters = `
                <div class="inline-flex space-x-2">
                    <button class="status-filter-btn px-3 py-2 rounded text-sm border border-gray-300 bg-white hover:bg-gray-50" data-status="all">
                        All
                    </button>
                    <button class="status-filter-btn px-3 py-2 rounded text-sm border border-green-300 bg-green-50 hover:bg-green-100 text-green-700" data-status="active">
                        <i class="fas fa-check-circle mr-1"></i> Active
                    </button>
                    <button class="status-filter-btn px-3 py-2 rounded text-sm border border-red-300 bg-red-50 hover:bg-red-100 text-red-700" data-status="cancelled">
                        <i class="fas fa-times-circle mr-1"></i> Cancelled
                    </button>
                    <button class="status-filter-btn px-3 py-2 rounded text-sm border border-gray-300 bg-gray-50 hover:bg-gray-100 text-gray-700" data-status="expired">
                        <i class="fas fa-clock mr-1"></i> Expired
                    </button>
                </div>
            `;
            $('.status-filters').html(statusFilters);
            
            // Status filter functionality
            $('.status-filter-btn').on('click', function() {
                var status = $(this).data('status');
                
                // Update active state
                $('.status-filter-btn').removeClass('ring-2 ring-blue-500');
                $(this).addClass('ring-2 ring-blue-500');
                
                if (status === 'all') {
                    table.column(5).search('').draw();
                } else {
                    table.column(5).search(status).draw();
                }
            });
            
            // Set "All" as active by default
            $('.status-filter-btn[data-status="all"]').addClass('ring-2 ring-blue-500');
        }
    });
    
    // Custom search function for status column
    $.fn.dataTable.ext.search.push(
        function(settings, data, dataIndex) {
            if (settings.nTable.id !== 'subscriptionsTable') {
                return true;
            }
            
            var searchStatus = table.column(5).search();
            if (!searchStatus) {
                return true;
            }
            
            var rowStatus = $(table.row(dataIndex).node()).find('td:eq(5) span').data('status');
            return rowStatus === searchStatus;
        }
    );
});
</script>
@endpush
