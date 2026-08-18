@extends('layouts.app')

@section('content')
<div class="app-header">
    <div class="header-title">
        <h1>My Tasks</h1>
    </div>
    
    <div class="topbar-right">
        <button onclick="document.getElementById('requestTaskModal').showModal()" class="btn btn-primary"><i class='bx bx-plus'></i> Request Task</button>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success">
    <i class='bx bx-check-circle'></i> {{ session('success') }}
</div>
@endif

<div class="metrics-grid" style="grid-template-columns: repeat(6, 1fr); margin-bottom: 20px; gap: 15px;">
    <a href="{{ route('employee.tasks', ['filter' => 'all']) }}" class="content-panel" style="padding: 15px; text-align: center; text-decoration: none; border-color: {{ $filter === 'all' ? 'var(--primary)' : 'var(--border-color)' }};">
        <div style="font-weight: {{ $filter === 'all' ? '600' : '500' }}; font-size: 13px; color: {{ $filter === 'all' ? 'var(--primary)' : 'var(--text-muted)' }};">All Tasks ({{ $allTasks->count() }})</div>
    </a>
    <a href="{{ route('employee.tasks', ['filter' => 'today']) }}" class="content-panel" style="padding: 15px; text-align: center; text-decoration: none; border-color: {{ $filter === 'today' ? 'var(--primary)' : 'var(--border-color)' }};">
        <div style="font-weight: {{ $filter === 'today' ? '600' : '500' }}; font-size: 13px; color: {{ $filter === 'today' ? 'var(--primary)' : 'var(--text-muted)' }};">Today ({{ $allTasks->where('due_date', \Carbon\Carbon::today()->format('Y-m-d'))->count() }})</div>
    </a>
    <a href="{{ route('employee.tasks', ['filter' => 'previous']) }}" class="content-panel" style="padding: 15px; text-align: center; text-decoration: none; border-color: {{ $filter === 'previous' ? 'var(--danger)' : 'var(--border-color)' }};">
        <div style="font-weight: {{ $filter === 'previous' ? '600' : '500' }}; font-size: 13px; color: {{ $filter === 'previous' ? 'var(--danger)' : 'var(--text-muted)' }};">Previous Tasks ({{ $allTasks->filter(function($task) { return \Carbon\Carbon::parse($task->due_date)->isPast() && $task->status != 'completed'; })->count() }})</div>
    </a>
    <a href="{{ route('employee.tasks', ['filter' => 'pending']) }}" class="content-panel" style="padding: 15px; text-align: center; text-decoration: none; border-color: {{ $filter === 'pending' ? 'var(--primary)' : 'var(--border-color)' }};">
        <div style="font-weight: {{ $filter === 'pending' ? '600' : '500' }}; font-size: 13px; color: {{ $filter === 'pending' ? 'var(--primary)' : 'var(--text-muted)' }};">Pending ({{ $allTasks->where('status', 'pending')->count() }})</div>
    </a>
    <a href="{{ route('employee.tasks', ['filter' => 'in_progress']) }}" class="content-panel" style="padding: 15px; text-align: center; text-decoration: none; border-color: {{ $filter === 'in_progress' ? 'var(--primary)' : 'var(--border-color)' }};">
        <div style="font-weight: {{ $filter === 'in_progress' ? '600' : '500' }}; font-size: 13px; color: {{ $filter === 'in_progress' ? 'var(--primary)' : 'var(--text-muted)' }};">In Progress ({{ $allTasks->where('status', 'in_progress')->count() }})</div>
    </a>
    <a href="{{ route('employee.tasks', ['filter' => 'completed']) }}" class="content-panel" style="padding: 15px; text-align: center; text-decoration: none; border-color: {{ $filter === 'completed' ? 'var(--primary)' : 'var(--border-color)' }};">
        <div style="font-weight: {{ $filter === 'completed' ? '600' : '500' }}; font-size: 13px; color: {{ $filter === 'completed' ? 'var(--primary)' : 'var(--text-muted)' }};">Completed ({{ $allTasks->where('status', 'completed')->count() }})</div>
    </a>
</div>

<div class="content-panel">
    <div class="table-responsive">
        <table id="tasksTable" class="custom-table">
            <thead>
                <tr>
                    <th>Task ID</th>
                    <th>Task Details</th>
                    <th>Priority</th>
                    <th>Category</th>
                    <th>Progress</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tasks as $index => $task)
                <tr>
                    <td style="color: var(--primary); font-weight: 600;">TSK-{{ str_pad($task->id, 4, '0', STR_PAD_LEFT) }}</td>
                    <td>
                        <div style="font-weight: 600; color: var(--text-main);">{{ $task->title }}</div>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">{{ Str::limit($task->description, 40) }}</div>
                        <div style="display: flex; gap: 5px; margin-top: 6px;">
                            <span class="badge" style="background: var(--bg-main); color: var(--text-muted); font-size: 10px; border: 1px solid var(--border-color);"><i class='bx bx-paperclip'></i> 2</span>
                            <span class="badge" style="background: var(--bg-main); color: var(--text-muted); font-size: 10px; border: 1px solid var(--border-color);"><i class='bx bx-message-rounded'></i> 5</span>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-{{ $task->priority === 'high' ? 'danger' : ($task->priority === 'medium' ? 'warning' : 'info') }}">
                            {{ ucfirst($task->priority) }}
                        </span>
                    </td>
                    <td><span style="color: var(--text-muted); font-size: 12px;">Development</span></td>
                    <td style="width: 100px;">
                        <div style="display: flex; justify-content: space-between; font-size: 10px; color: var(--text-muted); margin-bottom: 4px;">
                            <span>{{ $task->status == 'completed' ? '100%' : ($task->status == 'in_progress' ? '50%' : '0%') }}</span>
                        </div>
                        <div style="width: 100%; height: 4px; background: rgba(0,0,0,0.05); border-radius: 4px;">
                            <div style="width: {{ $task->status == 'completed' ? '100%' : ($task->status == 'in_progress' ? '50%' : '0%') }}; height: 100%; border-radius: 4px; background: {{ $task->status == 'completed' ? 'var(--success)' : 'var(--primary)' }};"></div>
                        </div>
                    </td>
                    <td style="{{ \Carbon\Carbon::parse($task->due_date)->isPast() && $task->status != 'completed' ? 'color: var(--danger); font-weight: 600;' : 'color: var(--text-main);' }}">
                        {{ \Carbon\Carbon::parse($task->due_date)->format('M d, Y') }}
                    </td>
                    <td>
                        <span class="badge badge-{{ $task->status === 'completed' ? 'success' : ($task->status === 'in_progress' ? 'info' : 'warning') }}">
                            {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                        </span>
                    </td>
                    <td>
                        <form action="{{ route('employee.tasks.update', $task->id) }}" method="POST" style="display: flex; gap: 5px;">
                            @csrf
                            @method('PATCH')
                            <select name="status" class="form-input btn-sm" style="padding: 4px 8px; width: auto; font-size: 11px;">
                                <option value="pending" {{ $task->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="completed" {{ $task->status === 'completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                            <button type="submit" class="btn btn-primary" style="padding: 4px 8px; font-size: 11px;">Update</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- DataTables CSS for Theme Customization -->
<link href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css" rel="stylesheet">
<!-- Request Task Modal -->
<dialog id="requestTaskModal" style="padding: 0; border: none; border-radius: 12px; background: var(--bg-card); max-width: 500px; width: 100%; margin: auto; box-shadow: 0 10px 25px rgba(0,0,0,0.5);">
    <div style="padding: 20px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
        <h3 style="margin: 0; font-size: 18px; color: var(--text-main);">Request New Task</h3>
        <button onclick="document.getElementById('requestTaskModal').close()" style="background: none; border: none; color: var(--text-muted); font-size: 24px; cursor: pointer;">&times;</button>
    </div>
    <form action="{{ route('employee.tasks.request') }}" method="POST" style="padding: 20px;">
        @csrf
        <div class="form-group">
            <label class="form-label">Project</label>
            <select name="project_id" class="form-input" required>
                <option value="">Select Project</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}">{{ $project->title }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Task Title</label>
            <input type="text" name="title" class="form-input" required>
        </div>
        <div class="form-group">
            <label class="form-label">Description (Optional)</label>
            <textarea name="description" class="form-input" rows="3"></textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Priority</label>
                <select name="priority" class="form-input" required>
                    <option value="low">Low</option>
                    <option value="medium" selected>Medium</option>
                    <option value="high">High</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Due Date (Optional)</label>
                <input type="date" name="due_date" class="form-input">
            </div>
        </div>
        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
            <button type="button" onclick="document.getElementById('requestTaskModal').close()" class="btn btn-outline">Cancel</button>
            <button type="submit" class="btn btn-primary">Submit Request</button>
        </div>
    </form>
</dialog>

<style>
    dialog::backdrop {
        background: rgba(11, 15, 25, 0.8);
    }
    
    .dataTables_wrapper .dataTables_length, .dataTables_wrapper .dataTables_filter, .dataTables_wrapper .dataTables_info, .dataTables_wrapper .dataTables_processing, .dataTables_wrapper .dataTables_paginate {
        color: var(--text-muted);
        font-size: 13px;
        margin-bottom: 15px;
    }
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid var(--border-color);
        background: var(--bg-card);
        color: var(--text-main);
        border-radius: 6px;
        padding: 4px 10px;
        outline: none;
    }
    .dataTables_wrapper .dataTables_length select {
        border: 1px solid var(--border-color);
        background: var(--bg-card);
        color: var(--text-main);
        border-radius: 6px;
        padding: 4px;
        outline: none;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        color: var(--text-muted) !important;
        border-color: transparent !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current, .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: var(--primary) !important;
        color: white !important;
        border-color: var(--primary) !important;
        border-radius: 6px;
    }
    table.dataTable.no-footer {
        border-bottom: 1px solid var(--border-color);
    }
    table.dataTable thead th, table.dataTable thead td {
        border-bottom: 1px solid var(--border-color);
    }
</style>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        $('#tasksTable').DataTable({
            "pageLength": 10,
            "ordering": true,
            "language": {
                "search": "",
                "searchPlaceholder": "Search tasks..."
            }
        });
    });
</script>
@endpush
