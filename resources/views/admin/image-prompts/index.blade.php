@extends('admin.layout')

@section('title', 'Image Prompts')
@section('header', 'Image Prompts')

@section('content')
<div class="mb-6">
    <div class="flex justify-between items-center">
        <div>
            <p class="text-gray-600">Upload and process images with AI prompts</p>
        </div>
        <a href="{{ route('admin.image-prompts.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg inline-flex items-center">
            <i class="fas fa-plus mr-2"></i>
            Upload New Image
        </a>
    </div>
</div>

@if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        {{ session('error') }}
    </div>
@endif

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-6">
        <div class="overflow-x-auto">
            <table id="imagePromptsTable" class="min-w-full display" style="width:100%">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Preview</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prompt</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($imagePrompts as $imagePrompt)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($imagePrompt->file_type === 'image')
                                    <img src="{{ $imagePrompt->original_image_url }}" alt="Preview" class="h-16 w-16 object-cover rounded">
                                @else
                                    <div class="h-16 w-16 bg-gray-200 rounded flex items-center justify-center">
                                        <i class="fas fa-video text-gray-500 text-2xl"></i>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 max-w-xs truncate" title="{{ $imagePrompt->prompt }}">
                                    {{ Str::limit($imagePrompt->prompt, 50) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap" data-type="{{ $imagePrompt->file_type }}">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $imagePrompt->file_type === 'image' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                    {{ ucfirst($imagePrompt->file_type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap" data-status="{{ $imagePrompt->status }}">
                                @if($imagePrompt->status === 'completed')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i> Completed
                                    </span>
                                @elseif($imagePrompt->status === 'processing')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-spinner fa-spin mr-1"></i> Processing
                                    </span>
                                @elseif($imagePrompt->status === 'failed')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        <i class="fas fa-times-circle mr-1"></i> Failed
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        <i class="fas fa-clock mr-1"></i> Pending
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $imagePrompt->user->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" data-order="{{ $imagePrompt->created_at->timestamp }}">
                                {{ $imagePrompt->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="{{ route('admin.image-prompts.show', $imagePrompt->id) }}" class="text-blue-600 hover:text-blue-900" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($imagePrompt->status === 'failed')
                                        <form action="{{ route('admin.image-prompts.reprocess', $imagePrompt->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-yellow-600 hover:text-yellow-900" title="Reprocess">
                                                <i class="fas fa-redo"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('admin.image-prompts.destroy', $imagePrompt->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                No image prompts found. <a href="{{ route('admin.image-prompts.create') }}" class="text-blue-600 hover:text-blue-900">Upload your first image</a>
                            </td>
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
    var table = $('#imagePromptsTable').DataTable({
        // Enable responsive design
        responsive: true,
        
        // Page length options
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        
        // Default page length
        pageLength: 10,
        
        // Enable ordering
        order: [[5, 'desc']], // Sort by "Created" column (index 5) in descending order
        
        // Column definitions
        columnDefs: [
            {
                // Disable sorting on Preview and Actions columns
                targets: [0, 6],
                orderable: false,
                searchable: false
            },
            {
                // Custom sorting for Type column
                targets: [2],
                type: 'html'
            },
            {
                // Custom sorting for Status column
                targets: [3],
                type: 'html'
            }
        ],
        
        // Language customization
        language: {
            search: "Search prompts:",
            lengthMenu: "Show _MENU_ prompts per page",
            info: "Showing _START_ to _END_ of _TOTAL_ prompts",
            infoEmpty: "No prompts available",
            infoFiltered: "(filtered from _MAX_ total prompts)",
            zeroRecords: "No matching prompts found",
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
                    columns: [1, 2, 3, 4, 5] // Exclude Preview and Actions columns
                }
            },
            {
                extend: 'csv',
                text: '<i class="fas fa-file-csv mr-1"></i> CSV',
                exportOptions: {
                    columns: [1, 2, 3, 4, 5]
                }
            },
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel mr-1"></i> Excel',
                exportOptions: {
                    columns: [1, 2, 3, 4, 5]
                }
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf mr-1"></i> PDF',
                exportOptions: {
                    columns: [1, 2, 3, 4, 5]
                },
                orientation: 'landscape',
                pageSize: 'A4'
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print mr-1"></i> Print',
                exportOptions: {
                    columns: [1, 2, 3, 4, 5]
                }
            }
        ],
        
        // Initialize with existing data
        deferRender: true,
        
        // Add custom initialization
        initComplete: function() {
            // Add custom filter buttons
            var filterButtons = `
                <div class="flex flex-wrap gap-2">
                    <div class="inline-flex space-x-2">
                        <span class="text-sm font-medium text-gray-700 self-center mr-2">Status:</span>
                        <button class="status-filter-btn filter-btn px-3 py-2 rounded text-sm border border-gray-300 bg-white hover:bg-gray-50" data-filter="all" data-column="status">
                            All
                        </button>
                        <button class="status-filter-btn filter-btn px-3 py-2 rounded text-sm border border-green-300 bg-green-50 hover:bg-green-100 text-green-700" data-filter="completed" data-column="status">
                            <i class="fas fa-check-circle mr-1"></i> Completed
                        </button>
                        <button class="status-filter-btn filter-btn px-3 py-2 rounded text-sm border border-yellow-300 bg-yellow-50 hover:bg-yellow-100 text-yellow-700" data-filter="processing" data-column="status">
                            <i class="fas fa-spinner mr-1"></i> Processing
                        </button>
                        <button class="status-filter-btn filter-btn px-3 py-2 rounded text-sm border border-red-300 bg-red-50 hover:bg-red-100 text-red-700" data-filter="failed" data-column="status">
                            <i class="fas fa-times-circle mr-1"></i> Failed
                        </button>
                        <button class="status-filter-btn filter-btn px-3 py-2 rounded text-sm border border-gray-300 bg-gray-50 hover:bg-gray-100 text-gray-700" data-filter="pending" data-column="status">
                            <i class="fas fa-clock mr-1"></i> Pending
                        </button>
                    </div>
                    <div class="inline-flex space-x-2">
                        <span class="text-sm font-medium text-gray-700 self-center mr-2">Type:</span>
                        <button class="type-filter-btn filter-btn px-3 py-2 rounded text-sm border border-gray-300 bg-white hover:bg-gray-50" data-filter="all" data-column="type">
                            All
                        </button>
                        <button class="type-filter-btn filter-btn px-3 py-2 rounded text-sm border border-blue-300 bg-blue-50 hover:bg-blue-100 text-blue-700" data-filter="image" data-column="type">
                            <i class="fas fa-image mr-1"></i> Image
                        </button>
                        <button class="type-filter-btn filter-btn px-3 py-2 rounded text-sm border border-purple-300 bg-purple-50 hover:bg-purple-100 text-purple-700" data-filter="video" data-column="type">
                            <i class="fas fa-video mr-1"></i> Video
                        </button>
                    </div>
                </div>
            `;
            $('.custom-filters').html(filterButtons);
            
            // Status filter functionality
            $('.status-filter-btn').on('click', function() {
                var filter = $(this).data('filter');
                
                // Update active state for status buttons
                $('.status-filter-btn').removeClass('ring-2 ring-blue-500');
                $(this).addClass('ring-2 ring-blue-500');
                
                if (filter === 'all') {
                    table.column(3).search('').draw();
                } else {
                    table.column(3).search(filter).draw();
                }
            });
            
            // Type filter functionality
            $('.type-filter-btn').on('click', function() {
                var filter = $(this).data('filter');
                
                // Update active state for type buttons
                $('.type-filter-btn').removeClass('ring-2 ring-blue-500');
                $(this).addClass('ring-2 ring-blue-500');
                
                if (filter === 'all') {
                    table.column(2).search('').draw();
                } else {
                    table.column(2).search(filter).draw();
                }
            });
            
            // Set "All" as active by default
            $('.status-filter-btn[data-filter="all"]').addClass('ring-2 ring-blue-500');
            $('.type-filter-btn[data-filter="all"]').addClass('ring-2 ring-blue-500');
        }
    });
    
    // Custom search function for status column
    $.fn.dataTable.ext.search.push(
        function(settings, data, dataIndex) {
            if (settings.nTable.id !== 'imagePromptsTable') {
                return true;
            }
            
            var searchStatus = table.column(3).search();
            var searchType = table.column(2).search();
            
            var rowStatus = $(table.row(dataIndex).node()).find('td:eq(3)').data('status');
            var rowType = $(table.row(dataIndex).node()).find('td:eq(2)').data('type');
            
            var statusMatch = !searchStatus || rowStatus === searchStatus;
            var typeMatch = !searchType || rowType === searchType;
            
            return statusMatch && typeMatch;
        }
    );
});
</script>
@endpush
