@extends('layouts.app')

@section('content')
<div class="app-header">
    <div class="header-title">
        <h1>Project Details: {{ $project->title }}</h1>
    </div>
    
    <div class="topbar-right">
        <a href="{{ route('admin.projects-monitoring.index') }}" class="btn btn-outline" style="display: flex; align-items: center; gap: 8px;">
            <i class='bx bx-arrow-back'></i> Back to Dashboard
        </a>
    </div>
</div>

<div class="section-grid" style="grid-template-columns: 1fr 2fr;">
    <!-- Left Column: Overview -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        <div class="content-panel">
            <div class="panel-header">
                <h3 class="panel-title"><i class='bx bx-info-circle' style="color: var(--primary);"></i> Project Overview</h3>
                <span class="badge {{ $project->progress == 100 ? 'badge-success' : 'badge-warning' }}">
                    {{ $project->calculated_status }}
                </span>
            </div>
            
            <div style="margin-bottom: 20px;">
                <strong style="display: block; font-size: 11px; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px;">Assigned Employee</strong>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div class="user-avatar-placeholder" style="width: 40px; height: 40px; font-size: 16px; background: var(--primary); color: white;">
                        @if($project->users->count() > 0)
                            {{ strtoupper(substr($project->users->first()->name, 0, 1)) }}
                        @else
                            ?
                        @endif
                    </div>
                    <div>
                        <h6 style="margin: 0; font-size: 15px; color: var(--text-main);">
                            @if($project->users->count() > 0)
                                {{ $project->users->first()->name }}
                            @else
                                <span style="color: var(--danger);">Unassigned</span>
                            @endif
                        </h6>
                    </div>
                </div>
            </div>
            
            <hr style="border-top: 1px solid var(--border-color); margin: 15px 0;">
            
            <div style="margin-bottom: 15px;">
                <strong style="display: block; font-size: 11px; color: var(--text-muted); text-transform: uppercase; margin-bottom: 5px;">Deadline</strong>
                <p style="margin: 0; font-size: 14px; font-weight: 500;">{{ $project->deadline ? \Carbon\Carbon::parse($project->deadline)->format('F d, Y') : 'No deadline set' }}</p>
            </div>

            <div style="margin-bottom: 20px;">
                <strong style="display: block; font-size: 11px; color: var(--text-muted); text-transform: uppercase; margin-bottom: 5px;">Last Updated</strong>
                <p style="margin: 0; font-size: 14px; font-weight: 500;">{{ $project->last_activity_at ? \Carbon\Carbon::parse($project->last_activity_at)->format('F d, Y, g:i A') : 'Never' }}</p>
            </div>
            
            <hr style="border-top: 1px solid var(--border-color); margin: 15px 0;">

            <div style="margin-bottom: 20px;">
                <strong style="display: block; font-size: 11px; color: var(--text-muted); text-transform: uppercase; margin-bottom: 10px;">Progress Statistics</strong>
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px;">
                    <span>Overall Progress</span>
                    <span style="font-weight: bold; color: {{ $project->progress == 100 ? 'var(--success)' : 'var(--primary)' }};">{{ $project->progress }}%</span>
                </div>
                <div style="height: 10px; background: #e2e8f0; border-radius: 5px; overflow: hidden;">
                    <div style="height: 100%; width: {{ $project->progress }}%; background: {{ $project->progress == 100 ? 'var(--success)' : 'var(--primary)' }}; border-radius: 5px; transition: width 0.3s ease;"></div>
                </div>
            </div>
            
            <div style="display: flex; justify-content: space-between; text-align: center; background: rgba(0,0,0,0.02); padding: 15px; border-radius: 8px;">
                <div style="flex: 1; border-right: 1px solid var(--border-color);">
                    <div style="font-size: 11px; font-weight: bold; color: var(--text-muted); text-transform: uppercase; margin-bottom: 5px;">Total</div>
                    <div style="font-size: 20px; font-weight: bold; color: var(--text-main);">{{ $project->tasks->count() }}</div>
                </div>
                <div style="flex: 1; border-right: 1px solid var(--border-color);">
                    <div style="font-size: 11px; font-weight: bold; color: var(--success); text-transform: uppercase; margin-bottom: 5px;">Completed</div>
                    <div style="font-size: 20px; font-weight: bold; color: var(--success);">{{ $project->tasks->where('status', 'completed')->count() }}</div>
                </div>
                <div style="flex: 1;">
                    <div style="font-size: 11px; font-weight: bold; color: var(--warning); text-transform: uppercase; margin-bottom: 5px;">Pending</div>
                    <div style="font-size: 20px; font-weight: bold; color: var(--warning);">{{ $project->tasks->where('status', 'pending')->count() }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Checklist -->
    <div style="display: flex; flex-direction: column;">
        <div class="content-panel" style="height: 100%;">
            <div class="panel-header">
                <h3 class="panel-title"><i class='bx bx-list-check' style="color: var(--primary);"></i> Checklist Details (Tasks)</h3>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 10px;">
                @forelse($project->tasks as $task)
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px; background: {{ $task->status === 'completed' ? 'rgba(16, 185, 129, 0.05)' : '#ffffff' }}; border: 1px solid {{ $task->status === 'completed' ? '#a7f3d0' : 'var(--border-color)' }}; border-radius: 8px; transition: all 0.2s ease;">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            @if($task->status === 'completed')
                                <i class='bx bxs-check-circle' style="color: var(--success); font-size: 24px;"></i>
                                <span style="text-decoration: line-through; color: var(--text-muted); font-size: 15px;">{{ $task->title }}</span>
                            @else
                                <i class='bx bx-hourglass' style="color: var(--warning); font-size: 24px;"></i>
                                <span style="font-weight: 600; color: var(--text-main); font-size: 15px;">{{ $task->title }}</span>
                            @endif
                        </div>
                        <div>
                            @if($task->status === 'completed')
                                <span style="display: inline-block; padding: 6px 12px; background: #ecfdf5; color: #065f46; border-radius: 20px; font-size: 12px; font-weight: 600;">
                                    ✔ Completed {{ $task->completed_at ? 'on '.\Carbon\Carbon::parse($task->completed_at)->format('M d') : '' }}
                                </span>
                            @else
                                <span style="display: inline-block; padding: 6px 12px; background: #fffbeb; color: #b45309; border-radius: 20px; font-size: 12px; font-weight: 600;">
                                    ⏳ Pending
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                        <i class='bx bx-list-minus' style="font-size: 40px; margin-bottom: 10px;"></i>
                        <p>No checklist items found for this project.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
