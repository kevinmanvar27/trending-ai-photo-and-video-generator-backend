@extends('admin.layout')

@section('title', 'Pages Management')
@section('header', 'Pages Management')

@section('content')
<div class="bg-white rounded-lg shadow-md">
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <div class="flex items-center space-x-4">
            <h3 class="text-lg font-semibold text-gray-800">
                <i class="fas fa-file-alt mr-2"></i> All Pages
            </h3>
        </div>
        <a href="{{ route('admin.pages.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
            <i class="fas fa-plus mr-2"></i>
            Create New Page
        </a>
    </div>

    @if($pages->count() > 0)
        <div class="p-6">
            <div class="overflow-x-auto">
                <table id="pagesTable" class="min-w-full display" style="width:100%">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Slug</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($pages as $page)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" data-order="{{ $page->order }}">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-gray-700 font-semibold">
                                        {{ $page->order }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $page->title }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500 font-mono">{{ $page->slug }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap" data-status="{{ $page->is_active ? 'active' : 'inactive' }}">
                                    <form action="{{ route('admin.pages.toggle-status', $page->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $page->is_active ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-red-100 text-red-800 hover:bg-red-200' }}" title="Click to toggle status">
                                            <i class="fas fa-{{ $page->is_active ? 'check-circle' : 'times-circle' }} mr-1"></i>
                                            {{ $page->is_active ? 'Active' : 'Inactive' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" data-order="{{ $page->created_at->timestamp }}">
                                    {{ $page->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('admin.pages.edit', $page->id) }}" class="text-blue-600 hover:text-blue-900" title="Edit Page">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.pages.destroy', $page->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this page?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900" title="Delete Page">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="text-center py-12">
            <i class="fas fa-file-alt text-gray-400 text-5xl mb-4"></i>
            <p class="text-gray-500 text-lg mb-4">No pages found.</p>
            <a href="{{ route('admin.pages.create') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg">
                <i class="fas fa-plus mr-2"></i>
                Create Your First Page
            </a>
        </div>
    @endif
</div>
@endsection

@push('scripts')
@if($pages->count() > 0)
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
    
    /* Custom filter buttons styling */
    .filter-btn {
        transition: all 0.2s ease;
    }
    
    .filter-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
</style>

<script>
$(document).ready(function() {
    // Initialize DataTable
    var table = $('#pagesTable').DataTable({
        // Enable responsive design
        responsive: true,
        
        // Page length options
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        
        // Default page length
        pageLength: 10,
        
        // Enable ordering
        order: [[0, 'asc']], // Sort by "Order" column (index 0) in ascending order
        
        // Column definitions
        columnDefs: [
            {
                // Disable sorting on Actions column
                targets: [5],
                orderable: false,
                searchable: false
            },
            {
                // Numeric sorting for Order column
                targets: [0],
                type: 'num'
            },
            {
                // Custom sorting for Status column
                targets: [3],
                type: 'html'
            }
        ],
        
        // Language customization
        language: {
            search: "Search pages:",
            lengthMenu: "Show _MENU_ pages per page",
            info: "Showing _START_ to _END_ of _TOTAL_ pages",
            infoEmpty: "No pages available",
            infoFiltered: "(filtered from _MAX_ total pages)",
            zeroRecords: "No matching pages found",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        },
        
        // Add export buttons and custom filter buttons
        dom: '<"flex flex-wrap justify-between items-center mb-4 gap-4"<"flex space-x-2"B><"custom-filters">>lfrtip',
        buttons: [
            {
                extend: 'copy',
                text: '<i class="fas fa-copy mr-1"></i> Copy',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4] // Exclude Actions column
                }
            },
            {
                extend: 'csv',
                text: '<i class="fas fa-file-csv mr-1"></i> CSV',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4]
                }
            },
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel mr-1"></i> Excel',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4]
                }
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf mr-1"></i> PDF',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4]
                },
                orientation: 'landscape',
                pageSize: 'A4'
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print mr-1"></i> Print',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4]
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
                    <span class="text-sm font-medium text-gray-700 self-center mr-2">Status:</span>
                    <button class="status-filter-btn filter-btn px-3 py-2 rounded text-sm border border-gray-300 bg-white hover:bg-gray-50" data-status="all">
                        <i class="fas fa-list mr-1"></i> All
                    </button>
                    <button class="status-filter-btn filter-btn px-3 py-2 rounded text-sm border border-green-300 bg-green-50 hover:bg-green-100 text-green-700" data-status="active">
                        <i class="fas fa-check-circle mr-1"></i> Active
                    </button>
                    <button class="status-filter-btn filter-btn px-3 py-2 rounded text-sm border border-red-300 bg-red-50 hover:bg-red-100 text-red-700" data-status="inactive">
                        <i class="fas fa-times-circle mr-1"></i> Inactive
                    </button>
                </div>
            `;
            $('.custom-filters').html(statusFilters);
            
            // Status filter functionality
            $('.status-filter-btn').on('click', function() {
                var status = $(this).data('status');
                
                // Update active state
                $('.status-filter-btn').removeClass('ring-2 ring-blue-500');
                $(this).addClass('ring-2 ring-blue-500');
                
                if (status === 'all') {
                    table.column(3).search('').draw();
                } else {
                    table.column(3).search(status).draw();
                }
            });
            
            // Set "All" as active by default
            $('.status-filter-btn[data-status="all"]').addClass('ring-2 ring-blue-500');
        }
    });
    
    // Custom search function for status column
    $.fn.dataTable.ext.search.push(
        function(settings, data, dataIndex) {
            if (settings.nTable.id !== 'pagesTable') {
                return true;
            }
            
            var searchStatus = table.column(3).search();
            if (!searchStatus) {
                return true;
            }
            
            var rowStatus = $(table.row(dataIndex).node()).find('td:eq(3)').data('status');
            return rowStatus === searchStatus;
        }
    );
});
</script>
@endif
@endpush
