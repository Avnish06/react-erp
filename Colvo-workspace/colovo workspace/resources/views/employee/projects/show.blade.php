@extends('layouts.app')

@section('content')
<div class="app-header">
    <div class="header-title">
        <h1>Project Details: {{ $project->title }}</h1>
    </div>
    
    <div class="topbar-right">
        <a href="{{ route('employee.my-projects.index') }}" class="btn btn-outline" style="display: flex; align-items: center; gap: 8px;">
            <i class='bx bx-arrow-back'></i> Back to Projects
        </a>
    </div>
</div>

<div class="section-grid" style="grid-template-columns: 1fr;">
    <!-- Progress Summary -->
    <div class="content-panel">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div style="flex: 1; padding-right: 30px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px; font-weight: 600;">
                    <span style="color: var(--primary); text-transform: uppercase; font-size: 12px;">My Progress</span>
                    <span id="progress-text" style="color: var(--text-main);">{{ $project->progress }}%</span>
                </div>
                <div style="height: 14px; background: #e2e8f0; border-radius: 7px; overflow: hidden;">
                    <div id="progress-bar" style="height: 100%; width: {{ $project->progress }}%; background: {{ $project->progress == 100 ? 'var(--success)' : 'var(--primary)' }}; border-radius: 7px; transition: width 0.4s ease, background 0.4s ease;"></div>
                </div>
            </div>
            
            <div style="border-left: 1px solid var(--border-color); padding-left: 30px;">
                <h2 style="margin: 0; font-size: 32px; font-weight: 700; color: var(--text-main);">
                    <span id="completed-count" style="color: var(--success);">{{ $project->tasks->where('status', 'completed')->count() }}</span>
                    <span style="color: var(--text-muted); font-size: 24px; margin: 0 5px;">/</span>
                    <span>{{ $project->tasks->count() }}</span>
                </h2>
                <div style="font-size: 12px; color: var(--text-muted); text-transform: uppercase; margin-top: 5px;">Tasks Completed</div>
            </div>
        </div>
    </div>

    <!-- Checklist -->
    <div class="content-panel">
        <div class="panel-header">
            <h3 class="panel-title"><i class='bx bx-check-square' style="color: var(--primary);"></i> Project Checklist</h3>
            <span id="project-status" class="badge {{ $project->progress == 100 ? 'badge-success' : 'badge-info' }}">{{ $project->calculated_status }}</span>
        </div>
        
        <div style="display: flex; flex-direction: column; gap: 12px;">
            @forelse($project->tasks as $task)
                <label class="task-item" style="display: flex; justify-content: space-between; align-items: center; padding: 16px 20px; background: {{ $task->status === 'completed' ? '#f8fafc' : '#ffffff' }}; border: 1px solid var(--border-color); border-radius: 8px; cursor: {{ $task->status === 'completed' ? 'default' : 'pointer' }}; transition: all 0.2s ease;">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <input class="task-checkbox" type="checkbox" data-task-id="{{ $task->id }}" {{ $task->status === 'completed' ? 'checked disabled' : '' }} style="width: 20px; height: 20px; cursor: {{ $task->status === 'completed' ? 'default' : 'pointer' }}; accent-color: var(--success);">
                        <span class="task-title" style="font-size: 16px; transition: all 0.2s ease; {{ $task->status === 'completed' ? 'text-decoration: line-through; color: var(--text-muted); font-weight: normal;' : 'font-weight: 600; color: var(--text-main);' }}">
                            {{ $task->title }}
                        </span>
                    </div>
                    <div>
                        <span class="status-badge" style="display: inline-block; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; transition: all 0.2s ease; {{ $task->status === 'completed' ? 'background: #ecfdf5; color: #065f46;' : 'background: #fffbeb; color: #b45309;' }}">
                            {{ $task->status === 'completed' ? '✔ Completed' : '⏳ Pending' }}
                        </span>
                    </div>
                </label>
            @empty
                <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                    <i class='bx bx-list-minus' style="font-size: 40px; margin-bottom: 10px;"></i>
                    <p>No checklist items found for this project.</p>
                </div>
            @endforelse
        </div>
        
        <div style="margin-top: 25px; text-align: right; border-top: 1px solid var(--border-color); padding-top: 15px;">
            <button id="save-project-btn" class="btn btn-primary" style="padding: 10px 20px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                <i class='bx bx-save'></i> Save & Complete Project
            </button>
        </div>
    </div>
</div>

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentProgress = {{ $project->progress }};
    const checkboxes = document.querySelectorAll('.task-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const taskId = this.dataset.taskId;
            const isCompleted = this.checked;
            const newStatus = isCompleted ? 'completed' : 'pending';
            
            // Update UI immediately (optimistic)
            const listItem = this.closest('.task-item');
            const taskTitle = listItem.querySelector('.task-title');
            const statusBadge = listItem.querySelector('.status-badge');
            
            if (isCompleted) {
                listItem.style.background = '#f8fafc';
                taskTitle.style.textDecoration = 'line-through';
                taskTitle.style.color = 'var(--text-muted)';
                taskTitle.style.fontWeight = 'normal';
                
                statusBadge.style.background = '#ecfdf5';
                statusBadge.style.color = '#065f46';
                statusBadge.innerHTML = '✔ Completed';
            } else {
                listItem.style.background = '#ffffff';
                taskTitle.style.textDecoration = 'none';
                taskTitle.style.color = 'var(--text-main)';
                taskTitle.style.fontWeight = '600';
                
                statusBadge.style.background = '#fffbeb';
                statusBadge.style.color = '#b45309';
                statusBadge.innerHTML = '⏳ Pending';
            }

            // Calculate local progress visually
            let checkedCount = 0;
            checkboxes.forEach(cb => { if(cb.checked) checkedCount++; });
            let visualProgress = checkboxes.length > 0 ? Math.round((checkedCount / checkboxes.length) * 100) : 0;
            
            document.getElementById('progress-text').innerText = visualProgress + '% (Unsaved)';
            document.getElementById('progress-text').style.color = 'var(--warning)';
            document.getElementById('completed-count').innerText = checkedCount;
            
            const progressBar = document.getElementById('progress-bar');
            progressBar.style.width = visualProgress + '%';
            if(visualProgress == 100) {
                progressBar.style.background = 'var(--success)';
            } else {
                progressBar.style.background = 'var(--primary)';
            }
        });
    });

    document.getElementById('save-project-btn').addEventListener('click', function() {
        const tasks = [];
        checkboxes.forEach(cb => {
            tasks.push({
                id: cb.dataset.taskId,
                status: cb.checked ? 'completed' : 'pending'
            });
        });

        // Show a loading state
        const btn = this;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="bx bx-loader bx-spin"></i> Saving...';
        btn.disabled = true;

        fetch(`/employee/my-projects/{{ $project->id }}/tasks/batch`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ tasks: tasks })
        })
        .then(response => response.json())
        .then(data => {
            btn.innerHTML = originalText;
            btn.disabled = false;

            if(data.success) {
                currentProgress = data.progress;
                // Update progress bar & text
                document.getElementById('progress-text').innerText = data.progress + '%';
                document.getElementById('progress-text').style.color = 'var(--text-main)';
                
                const progressBar = document.getElementById('progress-bar');
                progressBar.style.width = data.progress + '%';
                
                if(data.progress == 100) {
                    progressBar.style.background = 'var(--success)';
                    document.getElementById('project-status').className = 'badge badge-success';
                    Swal.fire({
                        title: 'Project Completed!',
                        text: 'All tasks have been finished. Great job!',
                        icon: 'success',
                        confirmButtonColor: '#10b981',
                        confirmButtonText: 'Awesome!'
                    });
                } else {
                    progressBar.style.background = 'var(--primary)';
                    document.getElementById('project-status').className = 'badge badge-info';
                    Swal.fire({
                        title: 'Progress Saved',
                        text: 'Your checklist progress has been saved.',
                        icon: 'success',
                        confirmButtonColor: '#10b981',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }

                document.getElementById('completed-count').innerText = data.completed_tasks;
                document.getElementById('project-status').innerText = data.calculated_status;

                // Disable checkboxes that are now completed
                checkboxes.forEach(cb => {
                    if (cb.checked) {
                        cb.disabled = true;
                        cb.style.cursor = 'default';
                        cb.closest('.task-item').style.cursor = 'default';
                    }
                });
            }
        })
        .catch(error => {
            btn.innerHTML = originalText;
            btn.disabled = false;
            console.error('Error:', error);
            Swal.fire('Error', 'Failed to save progress.', 'error');
        });
    });
});
</script>
@endsection
