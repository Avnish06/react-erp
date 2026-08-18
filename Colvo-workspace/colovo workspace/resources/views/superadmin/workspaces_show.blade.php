@extends('layouts.app')

@section('content')
<div class="app-header">
    <div class="header-title" style="display: flex; align-items: center; gap: 15px;">
        <a href="{{ route('superadmin.workspaces') }}" class="btn btn-sm btn-outline" style="border-radius: 50%; width: 36px; height: 36px; padding: 0; display: flex; align-items: center; justify-content: center;">
            <i class='bx bx-arrow-back'></i>
        </a>
        <div>
            <h2 style="margin: 0; color: var(--text-main); font-weight: 700; font-size: 24px;">{{ $company->name }}</h2>
            <p style="margin: 0; color: var(--text-muted); font-size: 13px;">Workspace Details & Active Tasks</p>
        </div>
    </div>
    <div class="topbar-right">
        <div style="display: flex; align-items: center; gap: 10px;">
            <div class="user-avatar-placeholder" style="width: 38px; height: 38px; font-size: 14px; ">
                {{ substr(auth()->user()->name, 0, 1) }}
            </div>
            <div style="font-size: 13px; font-weight: 600; color: var(--text-main); text-align: left;">
                <div>{{ auth()->user()->name }}</div>
                <div style="font-size: 10px; color: var(--text-muted); font-weight: normal;">{{ auth()->user()->role }}</div>
            </div>
        </div>
    </div>
</div>

<div class="metrics-grid" style="margin-bottom: 30px;">
    <div class="metric-card primary">
        <div class="metric-card-icon">
            <i class='bx bx-group'></i>
        </div>
        <div class="metric-card-details">
            <span class="metric-card-title">Total Users</span>
            <span class="metric-card-value">{{ $company->users->count() }}</span>
        </div>
    </div>
    
    <div class="metric-card green">
        <div class="metric-card-icon">
            <i class='bx bx-check-shield'></i>
        </div>
        <div class="metric-card-details">
            <span class="metric-card-title">Admins</span>
            <span class="metric-card-value">{{ $company->users->where('role', 'admin')->count() }}</span>
        </div>
    </div>

    <div class="metric-card amber">
        <div class="metric-card-icon">
            <i class='bx bx-task'></i>
        </div>
        <div class="metric-card-details">
            <span class="metric-card-title">Active Tasks</span>
            <span class="metric-card-value">
                {{ $company->users->flatMap->tasks->where('status', '!=', 'completed')->count() }}
            </span>
        </div>
    </div>
</div>

<div class="content-panel">
    <div class="panel-header">
        <h3 class="panel-title"><i class='bx bx-user-circle' style="color: var(--primary);"></i> Workspace Users & Their Tasks</h3>
    </div>
    
    <div style="padding: 20px;">
        @forelse($company->users as $user)
            <div style="background: var(--bg-main); border: 1px solid var(--border-color); border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 15px; margin-bottom: 15px;">
                    <a href="{{ route('superadmin.impersonate', $user->id) }}" style="display: flex; align-items: center; gap: 15px; text-decoration: none; cursor: pointer;" onmouseover="this.querySelector('.user-name').style.color='var(--primary)'" onmouseout="this.querySelector('.user-name').style.color='var(--text-main)'">
                        <div class="user-avatar-placeholder" style="width: 48px; height: 48px; font-size: 18px; background: var(--primary); color: white;">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                        <div>
                            <div class="user-name" style="font-weight: 700; font-size: 16px; color: var(--text-main); transition: var(--transition-fast);">{{ $user->name }}</div>
                            <div style="font-size: 13px; color: var(--text-muted);">{{ $user->email }}</div>
                        </div>
                    </a>
                    <div>
                        <span class="status-badge" style="
                            {{ $user->role === 'superadmin' ? 'background: #fce7f3; color: #be185d;' : ($user->role === 'admin' ? 'background: #e0e7ff; color: #4338ca;' : 'background: #f1f5f9; color: #475569;') }}">
                            {{ ucfirst($user->role) }}
                        </span>
                    </div>
                </div>

                <div>
                    <h4 style="font-size: 14px; margin: 0 0 10px 0; color: var(--text-main); font-weight: 600;">Currently Running Tasks:</h4>
                    @php
                        $activeTasks = $user->tasks->where('status', '!=', 'completed');
                    @endphp
                    
                    @if($activeTasks->count() > 0)
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            @foreach($activeTasks as $task)
                                <div style="background: white; border: 1px solid var(--border-color); padding: 12px 15px; border-radius: 6px; display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <div style="font-weight: 600; font-size: 14px; color: var(--text-main);">{{ $task->title }}</div>
                                        <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
                                            Due: {{ \Carbon\Carbon::parse($task->due_date)->format('M d, Y') }} &bull; Priority: {{ ucfirst($task->priority) }}
                                        </div>
                                    </div>
                                    <div>
                                        <span class="status-badge {{ $task->status === 'in_progress' ? 'status-pending' : 'status-rejected' }}">
                                            {{ str_replace('_', ' ', ucfirst($task->status)) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div style="font-size: 13px; color: var(--text-muted); background: white; padding: 15px; border-radius: 6px; border: 1px dashed var(--border-color); text-align: center;">
                            No active tasks running for this user.
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div style="padding: 40px; text-align: center; color: var(--text-muted);">
                <i class='bx bx-user-x' style="font-size: 48px; color: #cbd5e1; margin-bottom: 10px;"></i>
                <p>No users found in this workspace.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
