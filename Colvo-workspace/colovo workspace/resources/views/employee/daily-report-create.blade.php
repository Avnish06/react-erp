@extends('layouts.app')

@section('content')
<div class="app-header">
    <div class="header-title">
        <h1>Submit Daily Report</h1>
        <p>Log your daily progress, tasks completed, and plans for tomorrow.</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">
        <i class='bx bx-check-circle'></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger">
        <i class='bx bx-error-circle'></i>
        <span>{{ session('error') }}</span>
    </div>
@endif

<div class="content-panel" style="max-width: 700px;">
    <div class="panel-header" style="display: flex; align-items: center; gap: 15px;">
        <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(14, 165, 233, 0.1); color: var(--info); display: flex; align-items: center; justify-content: center;">
            <i class='bx bx-file' style="font-size: 28px;"></i>
        </div>
        <div>
            <h3 class="panel-title" style="margin: 0;">Daily Report Form</h3>
            <p style="margin: 5px 0 0 0; font-size: 13px; color: var(--text-muted);">Please fill out the details accurately. You can only submit one report per day.</p>
        </div>
    </div>
    
    <form action="{{ route('employee.daily-report') }}" method="POST" style="padding: 24px;">
        @csrf
        <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label" style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-main);">What tasks did you complete today? <span style="color: var(--danger);">*</span></label>
            <textarea name="tasks_completed" required rows="4" class="form-input" style="width: 100%; border: 1px solid var(--border-color); border-radius: 8px; padding: 12px; font-family: inherit; font-size: 14px; background: var(--bg-main); color: var(--text-main);" placeholder="e.g. - Finished the API integration..."></textarea>
        </div>
        
        <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label" style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-main);">Any challenges or blockers?</label>
            <textarea name="challenges" rows="3" class="form-input" style="width: 100%; border: 1px solid var(--border-color); border-radius: 8px; padding: 12px; font-family: inherit; font-size: 14px; background: var(--bg-main); color: var(--text-main);" placeholder="e.g. - Waiting for approval on UI designs..."></textarea>
            <small style="color: var(--text-muted); font-size: 12px; margin-top: 5px; display: block;">Leave blank if there were no blockers today.</small>
        </div>
        
        <div class="form-group" style="margin-bottom: 25px;">
            <label class="form-label" style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-main);">What is your plan for tomorrow? <span style="color: var(--danger);">*</span></label>
            <textarea name="plan_tomorrow" required rows="3" class="form-input" style="width: 100%; border: 1px solid var(--border-color); border-radius: 8px; padding: 12px; font-family: inherit; font-size: 14px; background: var(--bg-main); color: var(--text-main);" placeholder="e.g. - Will start working on the frontend..."></textarea>
        </div>
        
        <style>
            .form-actions {
                display: flex;
                justify-content: flex-end;
                gap: 15px;
                border-top: 1px solid var(--border-color);
                padding-top: 20px;
            }
            @media (max-width: 480px) {
                .form-actions {
                    flex-direction: column;
                }
            }
        </style>
        
        <div class="form-actions">
            <a href="{{ route('employee.dashboard') }}" class="btn btn-outline" style="text-decoration: none; padding: 12px 24px; flex: 1; text-align: center; justify-content: center;">Cancel</a>
            <button type="submit" class="btn btn-primary" style="padding: 12px 24px; flex: 1; text-align: center; justify-content: center; white-space: nowrap;">Submit Report</button>
        </div>
    </form>
</div>
@endsection
