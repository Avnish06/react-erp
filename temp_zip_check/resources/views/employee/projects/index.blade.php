@extends('layouts.app')

@section('content')
<div class="app-header">
    <div class="header-title">
        <h1>My Assigned Projects</h1>
    </div>
</div>

<style>
    .projects-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 24px;
    }
    @media (max-width: 768px) {
        .projects-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="projects-grid">
    @forelse($projects as $project)
        <div class="content-panel" style="display: flex; flex-direction: column; height: 100%;">
            <div class="panel-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 15px; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
                <h3 class="panel-title" style="word-break: break-word; flex: 1; min-width: 0;">
                    <a href="{{ route('employee.my-projects.show', $project->id) }}" style="color: var(--primary); text-decoration: none;">
                        <i class='bx bx-briefcase-alt-2' style="margin-right: 5px;"></i> {{ $project->title }}
                    </a>
                </h3>
                <span class="badge {{ $project->progress == 100 ? 'badge-success' : 'badge-warning' }}">
                    {{ $project->calculated_status }}
                </span>
            </div>
            
            <div style="flex-grow: 1;">
                <div style="margin-bottom: 15px; font-size: 13px;">
                    <strong style="color: var(--text-muted);">Deadline:</strong>
                    <span style="font-weight: 500; color: var(--text-main); margin-left: 8px;">
                        {{ $project->deadline ? \Carbon\Carbon::parse($project->deadline)->format('M d, Y') : 'N/A' }}
                    </span>
                </div>

                <div style="margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px; font-weight: 600;">
                        <span style="color: var(--text-muted);">My Progress</span>
                        <span style="color: {{ $project->progress == 100 ? 'var(--success)' : 'var(--primary)' }};">{{ $project->progress }}%</span>
                    </div>
                    <div style="height: 10px; background: #e2e8f0; border-radius: 5px; overflow: hidden;">
                        <div style="height: 100%; width: {{ $project->progress }}%; background: {{ $project->progress == 100 ? 'var(--success)' : 'var(--primary)' }}; border-radius: 5px; transition: width 0.3s ease;"></div>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; background: rgba(0,0,0,0.02); padding: 10px 15px; border-radius: 6px;">
                    <span style="color: var(--text-muted);">
                        <strong style="color: var(--success); font-size: 14px;">{{ $project->tasks->where('status', 'completed')->count() }}</strong> completed out of 
                        <strong style="color: var(--text-main);">{{ $project->tasks->count() }}</strong> tasks
                    </span>
                </div>
            </div>
            
            <div style="border-top: 1px solid var(--border-color); padding-top: 15px; margin-top: 15px; text-align: right;">
                <a href="{{ route('employee.my-projects.show', $project->id) }}" class="btn btn-outline" style="width: 100%; justify-content: center;">
                    View Checklist <i class='bx bx-right-arrow-alt' style="margin-left: 5px; font-size: 18px;"></i>
                </a>
            </div>
        </div>
    @empty
        <div class="content-panel" style="grid-column: 1 / -1; text-align: center; padding: 40px;">
            <i class='bx bx-briefcase' style="font-size: 48px; color: var(--text-muted); margin-bottom: 15px;"></i>
            <h3 style="color: var(--text-main); margin-bottom: 10px;">No Projects Assigned</h3>
            <p style="color: var(--text-muted);">You have not been assigned to any projects yet.</p>
        </div>
    @endforelse
</div>
@endsection
