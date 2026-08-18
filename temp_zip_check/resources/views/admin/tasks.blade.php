@extends('layouts.app')

@section('content')
<div class="app-header">
    <div class="header-title">
        <h1>Task Allocation</h1>
    </div>
    
    <div class="topbar-right">
        <div class="noti-bell">
            <i class='bx bx-bell'></i>
            <span class="noti-bell-badge">1</span>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <div class="user-avatar-placeholder" style="width: 38px; height: 38px; font-size: 14px; ">
                {{ substr(auth()->user()->name, 0, 1) }}
            </div>
            <div style="font-size: 13px; font-weight: 600; color: var(--text-main); text-align: left;">
                <div>{{ auth()->user()->name }}</div>
                <div style="font-size: 10px; color: var(--text-muted); font-weight: normal;">{{ auth()->user()->position ?? auth()->user()->role }}</div>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 24px;">
        <i class='bx bx-check-circle'></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

<div class="section-grid">
    <!-- Left: Existing Workspace Tasks list -->
    <div class="content-panel">
        <div class="panel-header">
            <h3 class="panel-title"><i class='bx bx-task' style="color: var(--primary);"></i> Current Work Allocations</h3>
        </div>
        
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Task details</th>
                        <th>Project</th>
                        <th>Assignee</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasks as $t)
                        <tr>
                            <td>
                                <strong style="color: var(--text-main); font-size: 15px;">{{ $t->title }}</strong>
                                <p style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">{{ $t->description }}</p>
                                <span class="badge" style="padding: 2px 6px; font-size: 10px; margin-top: 6px;
                                    @if($t->priority === 'high') background: var(--danger-bg); color: var(--danger);
                                    @elseif($t->priority === 'medium') background: var(--warning-bg); color: var(--warning);
                                    @else background: rgba(255,255,255,0.05); color: var(--text-muted); @endif">
                                    {{ strtoupper($t->priority) }} Priority
                                </span>
                            </td>
                            <td>{{ $t->project->title }}</td>
                            <td>
                                <span style="display: flex; align-items: center; gap: 8px;">
                                    <span class="user-avatar-placeholder" style="width: 24px; height: 24px; font-size: 10px;">
                                        {{ substr($t->assignee->name, 0, 2) }}
                                    </span>
                                    {{ $t->assignee->name }}
                                </span>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($t->due_date)->format('M d, Y') }}</td>
                            <td>
                                @if($t->status === 'completed')
                                    <span class="badge badge-success">Completed</span>
                                @elseif($t->status === 'in_progress')
                                    <span class="badge badge-warning">In Progress</span>
                                @else
                                    <span class="badge badge-info">Pending</span>
                                @endif
                            </td>
                            <td>
                                <form action="{{ route('admin.tasks.update-status', $t->id) }}" method="POST" style="display: flex; gap: 6px;">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="form-input btn-sm" onchange="this.form.submit()" style="padding: 4px 8px; width: auto; font-size: 12px; background-color: rgba(255,255,255,0.05);">
                                        <option value="pending" {{ $t->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="in_progress" {{ $t->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="completed" {{ $t->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 40px 0;">No tasks allocated in ESP system.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Right: Assign task form panel -->
    <div class="content-panel" style="height: fit-content;">
        <div class="panel-header">
            <h3 class="panel-title"><i class='bx bx-plus-circle' style="color: var(--secondary);"></i> Assign New Work</h3>
        </div>

        <form action="{{ route('admin.tasks.assign') }}" method="POST">
            @csrf

            <div class="form-group">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                    <label for="project_id" class="form-label" style="margin-bottom: 0;">Select Project</label>
                    <button type="button" class="btn btn-outline btn-sm" style="padding: 2px 8px; font-size: 11px;" onclick="document.getElementById('add-project-modal').style.display='flex'">+ New Project</button>
                </div>
                <select name="project_id" id="project_id" class="form-input" required>
                    <option value="" disabled selected>Select workspace project...</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}">{{ $p->title }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="assigned_to" class="form-label">Assign to Employee</label>
                <select name="assigned_to" id="assigned_to" class="form-input" required>
                    <option value="" disabled selected>Select employee...</option>
                    @foreach($employees as $e)
                        <option value="{{ $e->id }}">{{ $e->name }} ({{ $e->department }})</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="title" class="form-label">Task Title</label>
                <input type="text" name="title" id="title" class="form-input" placeholder="e.g., Build Auth Middleware" required>
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Task Description</label>
                <textarea name="description" id="description" class="form-input" placeholder="Enter task specifications and guidelines..." rows="3"></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="priority" class="form-label">Priority</label>
                    <select name="priority" id="priority" class="form-input" required>
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="due_date" class="form-label">Due Date</label>
                    <input type="date" name="due_date" id="due_date" class="form-input" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block" style="margin-top: 10px;">Allocate Work</button>
        </form>
    </div>
</div>

<!-- Add Project Modal -->
<div id="add-project-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div class="content-panel" style="width: 100%; max-width: 500px; padding: 25px; position: relative;">
        <button onclick="document.getElementById('add-project-modal').style.display='none'" style="position: absolute; right: 20px; top: 20px; background: none; border: none; font-size: 24px; cursor: pointer; color: var(--text-muted);">&times;</button>
        <h3 style="margin-bottom: 20px; color: var(--text-main);">Create New Project</h3>
        
        <form action="{{ route('admin.projects.store') }}" method="POST">
            @csrf
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-size: 13px; font-weight: 600;">Project Title</label>
                <input type="text" name="title" required class="search-input" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); background: rgba(255,255,255,0.05); color: var(--text-main);" placeholder="e.g. New Marketing Campaign">
            </div>
            
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-size: 13px; font-weight: 600;">Description</label>
                <textarea name="description" class="search-input" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); background: rgba(255,255,255,0.05); color: var(--text-main);" rows="3" placeholder="Project overview..."></textarea>
            </div>
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 5px; font-size: 13px; font-weight: 600;">Assign Employees (Optional)</label>
                <select name="assigned_users[]" class="search-input" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); background: rgba(255,255,255,0.05); color: var(--text-main);" multiple>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->department }})</option>
                    @endforeach
                </select>
                <small style="color: var(--text-muted); font-size: 11px;">Hold Ctrl (Windows) or Cmd (Mac) to select multiple employees.</small>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 5px; font-size: 13px; font-weight: 600;">Budget Allocation ($)</label>
                <input type="number" name="budget" min="0" step="0.01" class="search-input" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); background: rgba(255,255,255,0.05); color: var(--text-main);">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('add-project-modal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Project</button>
            </div>
        </form>
    </div>
</div>

@endsection

