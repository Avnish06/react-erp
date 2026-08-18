@extends('layouts.app')

@section('content')
<div class="app-header">
    <div class="header-title">
        <h1>Assign Project & Checklist</h1>
    </div>
    
    <div class="topbar-right">
        <a href="{{ route('admin.projects-monitoring.index') }}" class="btn btn-outline" style="display: flex; align-items: center; gap: 8px;">
            <i class='bx bx-arrow-back'></i> Back to Dashboard
        </a>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger" style="margin-bottom: 24px; padding: 15px; background: #fef2f2; color: #991b1b; border-radius: 8px; border: 1px solid #fecaca;">
        <ul style="margin-bottom: 0; padding-left: 20px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="content-panel" style="max-width: 900px; margin: 0 auto;">
    <form action="{{ route('admin.projects-monitoring.store') }}" method="POST">
        @csrf
        
        <h4 style="margin-bottom: 20px; color: var(--primary); border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
            <i class='bx bx-info-circle'></i> 1. Project Details
        </h4>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
            <div>
                <label for="title" style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-main); font-size: 13px;">Project Name</label>
                <input type="text" id="title" name="title" value="{{ old('title') }}" required placeholder="e.g. Employee Management System" class="form-input">
            </div>
            
            <div>
                <label for="assigned_user" style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-main); font-size: 13px;">Assign Employee</label>
                <select id="assigned_user" name="assigned_user" required class="form-input">
                    <option value="">Select an employee...</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" {{ old('assigned_user') == $employee->id ? 'selected' : '' }}>
                            {{ $employee->name }} ({{ $employee->department ?? 'N/A' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="deadline" style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-main); font-size: 13px;">Deadline (Optional)</label>
                <input type="date" id="deadline" name="deadline" value="{{ old('deadline') }}" class="form-input">
            </div>
        </div>

        <h4 style="margin-bottom: 10px; color: var(--primary); border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
            <i class='bx bx-list-check'></i> 2. Dynamic Checklist (Tasks)
        </h4>
        <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 20px;">Every checklist point represents one project task. The progress percentage will be calculated automatically based on these points.</p>
        
        <div id="checklist-container" style="margin-bottom: 20px;">
            <div class="checklist-item" style="display: flex; gap: 10px; margin-bottom: 15px; align-items: center;">
                <div style="background: var(--bg-body); padding: 10px; border-radius: 6px; border: 1px solid var(--border-color); color: var(--text-muted);">
                    <i class='bx bx-check-square'></i>
                </div>
                <input type="text" name="tasks[]" placeholder="Task description (e.g. Requirement Analysis)" required class="form-input" style="flex: 1;">
                <button class="remove-task-btn" type="button" onclick="removeTask(this)" style="display: none; background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; padding: 10px 15px; border-radius: 6px; cursor: pointer;">
                    <i class='bx bx-trash'></i>
                </button>
            </div>
            
            <div class="checklist-item" style="display: flex; gap: 10px; margin-bottom: 15px; align-items: center;">
                <div style="background: var(--bg-body); padding: 10px; border-radius: 6px; border: 1px solid var(--border-color); color: var(--text-muted);">
                    <i class='bx bx-check-square'></i>
                </div>
                <input type="text" name="tasks[]" placeholder="Task description (e.g. UI Design)" required class="form-input" style="flex: 1;">
                <button class="remove-task-btn" type="button" onclick="removeTask(this)" style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; padding: 10px 15px; border-radius: 6px; cursor: pointer;">
                    <i class='bx bx-trash'></i>
                </button>
            </div>
        </div>

        <div style="margin-bottom: 30px;">
            <button type="button" class="btn btn-outline" onclick="addTask()" style="display: flex; align-items: center; gap: 5px;">
                <i class='bx bx-plus'></i> Add Another Task Point
            </button>
        </div>

        <hr style="border-top: 1px solid var(--border-color); margin: 20px 0;">

        <div style="text-align: right;">
            <button type="submit" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; font-weight: 600;">
                <i class='bx bx-save'></i> Create Project & Assign Checklist
            </button>
        </div>
    </form>
</div>

<script>
    function addTask() {
        const container = document.getElementById('checklist-container');
        
        const div = document.createElement('div');
        div.className = 'checklist-item';
        div.style.display = 'flex';
        div.style.gap = '10px';
        div.style.marginBottom = '15px';
        div.style.alignItems = 'center';
        
        div.innerHTML = `
            <div style="background: var(--bg-body); padding: 10px; border-radius: 6px; border: 1px solid var(--border-color); color: var(--text-muted);">
                <i class='bx bx-check-square'></i>
            </div>
            <input type="text" name="tasks[]" placeholder="Task description (e.g. Database Design)" required class="form-input" style="flex: 1;">
            <button class="remove-task-btn" type="button" onclick="removeTask(this)" style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; padding: 10px 15px; border-radius: 6px; cursor: pointer;">
                <i class='bx bx-trash'></i>
            </button>
        `;
        container.appendChild(div);
        
        updateRemoveButtons();
    }

    function removeTask(button) {
        const container = document.getElementById('checklist-container');
        const itemsCount = container.querySelectorAll('.checklist-item').length;
        
        if (itemsCount > 1) {
            button.closest('.checklist-item').remove();
            updateRemoveButtons();
        }
    }

    function updateRemoveButtons() {
        const container = document.getElementById('checklist-container');
        const items = container.querySelectorAll('.checklist-item');
        const buttons = container.querySelectorAll('.remove-task-btn');
        
        buttons.forEach(btn => {
            if (items.length > 1) {
                btn.style.display = 'block';
            } else {
                btn.style.display = 'none';
            }
        });
    }
</script>
@endsection
