@extends('layouts.app')

@section('content')
<div class="app-header">
    <div class="header-title">
        <h1>Workspace Dashboard</h1>
        <p>Monitor your company's metrics, teams, and ongoing projects.</p>
    </div>
    <div class="topbar-right">
        <div class="noti-bell">
            <i class='bx bx-bell'></i>
            <span class="noti-bell-badge">1</span>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <div class="user-avatar-placeholder" style="width: 40px; height: 40px; font-size: 14px;">
                {{ substr(auth()->user()->name, 0, 1) }}
            </div>
            <div style="font-size: 13px; font-weight: 600; color: var(--text-main); text-align: left;">
                <div>{{ auth()->user()->name }}</div>
                <div style="font-size: 11px; color: var(--text-muted); font-weight: normal;">{{ auth()->user()->position ?? auth()->user()->role }}</div>
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

<!-- Welcome Banner -->
<div class="welcome-banner">
    <span class="welcome-badge">Welcome Back</span>
    <h2 class="welcome-title">Good evening, {{ auth()->user()->name }}! 👋</h2>
    <p class="welcome-quote">"Dream big and dare to fail."</p>
</div>

<!-- Metrics Cards Section -->
<div class="metrics-grid">
    <!-- Total Employees -->
    <div class="metric-card amber">
        <div class="metric-card-icon">
            <i class='bx bx-group'></i>
        </div>
        <div class="metric-card-details">
            <span class="metric-card-title">Total Employees</span>
            <span class="metric-card-value">{{ $totalEmployees }}</span>
        </div>
    </div>

    <!-- Total Projects -->
    <div class="metric-card rose">
        <div class="metric-card-icon">
            <i class='bx bx-briefcase-alt'></i>
        </div>
        <div class="metric-card-details">
            <span class="metric-card-title">Total Projects</span>
            <span class="metric-card-value">{{ $totalProjects }}</span>
        </div>
    </div>

    <!-- Active Reviews -->
    <div class="metric-card purple">
        <div class="metric-card-icon">
            <i class='bx bx-medal'></i>
        </div>
        <div class="metric-card-details">
            <span class="metric-card-title">High Performers</span>
            <span class="metric-card-value">{{ $highPerformers }}</span>
        </div>
    </div>

    <!-- Ongoing Tasks -->
    <div class="metric-card blue">
        <div class="metric-card-icon">
            <i class='bx bx-task'></i>
        </div>
        <div class="metric-card-details">
            <span class="metric-card-title">Ongoing Tasks</span>
            <span class="metric-card-value">{{ $inProgressTasks }}</span>
        </div>
    </div>
</div>

<div class="section-grid">
    <!-- Left Panel: Workspace Reports (2/3 width) -->
    <div style="display: flex; flex-direction: column; gap: 30px;">
        
        <!-- Project Progress Monitoring -->
        <div class="content-panel" style="overflow-x: auto;">
            <div class="panel-header">
                <h3 class="panel-title"><i class='bx bx-pie-chart-alt-2' style="color: var(--primary);"></i> Project Progress Monitoring</h3>
                <a href="{{ route('admin.projects-monitoring.index') }}" class="btn btn-outline btn-sm">Manage Projects</a>
            </div>
            
            <div class="table-responsive">
                <table class="custom-table" style="min-width: 900px;">
                    <thead>
                        <tr>
                            <th>Project Name</th>
                            <th>Assigned Employee</th>
                            <th>Tasks (Total/Done/Pend)</th>
                            <th>Progress</th>
                            <th>Current Status</th>
                            <th>Deadline / Last Update</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projects as $p)
                            <tr>
                                <td style="font-weight: 600;">
                                    <a href="{{ route('admin.projects-monitoring.show', $p->id) }}" style="text-decoration: none; color: inherit;">
                                        {{ $p->title }}
                                    </a>
                                </td>
                                <td>
                                    @if($p->users->count() > 0)
                                        <span style="display: flex; align-items: center; gap: 8px;">
                                            <span class="user-avatar-placeholder" style="width: 24px; height: 24px; font-size: 10px;">
                                                {{ substr($p->users->first()->name, 0, 2) }}
                                            </span>
                                            {{ $p->users->first()->name }}
                                        </span>
                                    @else
                                        <span style="color: #ef4444;">Unassigned</span>
                                    @endif
                                </td>
                                <td>
                                    <span style="font-weight: bold;" title="Total">{{ $p->tasks->count() }}</span> | 
                                    <span style="color: #10b981; font-weight: bold;" title="Completed">{{ $p->tasks->where('status', 'completed')->count() }}</span> | 
                                    <span style="color: #f59e0b; font-weight: bold;" title="Pending">{{ $p->tasks->where('status', 'pending')->count() }}</span>
                                </td>
                                <td style="min-width: 150px;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div style="flex: 1; height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden;">
                                            <div style="height: 100%; width: {{ $p->progress }}%; background: {{ $p->progress == 100 ? '#10b981' : '#3b82f6' }}; border-radius: 4px;"></div>
                                        </div>
                                        <span style="font-size: 12px; font-weight: bold;">{{ $p->progress }}%</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ $p->progress == 100 ? 'badge-success' : 'badge-info' }}">{{ $p->calculated_status }}</span>
                                </td>
                                <td style="font-size: 12px; color: var(--text-muted); line-height: 1.4;">
                                    <div><strong>DL:</strong> {{ $p->deadline ? \Carbon\Carbon::parse($p->deadline)->format('M d, Y') : 'N/A' }}</div>
                                    <div><strong>LU:</strong> {{ $p->last_activity_at ? \Carbon\Carbon::parse($p->last_activity_at)->diffForHumans() : 'Never' }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 20px;">No projects created yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Task Allocation Reports -->
        <div class="content-panel">
            <div class="panel-header">
                <h3 class="panel-title"><i class='bx bx-list-check' style="color: var(--secondary);"></i> Workspace Task Reports</h3>
                <a href="{{ route('admin.tasks') }}" class="btn btn-primary btn-sm">Assign Work</a>
            </div>
            
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Task</th>
                            <th>Assigned To</th>
                            <th>Project</th>
                            <th>Due Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tasks->take(5) as $t)
                            <tr>
                                <td style="font-weight: 500;">{{ $t->title }}</td>
                                <td>
                                    <span style="display: flex; align-items: center; gap: 8px;">
                                        <span class="user-avatar-placeholder" style="width: 24px; height: 24px; font-size: 10px;">
                                            {{ substr($t->assignee->name, 0, 2) }}
                                        </span>
                                        {{ $t->assignee->name }}
                                    </span>
                                </td>
                                <td>{{ $t->project->title }}</td>
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
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align: center; color: var(--text-muted);">No tasks allocated.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Annual Financial Earning & Expenditure Summary -->
        <div class="content-panel">
            <div class="panel-header">
                <h3 class="panel-title"><i class='bx bx-wallet' style="color: var(--success);"></i> Annual Financial Summary & Performance</h3>
                <a href="{{ route('admin.finance') }}" class="btn btn-outline btn-sm">Update Financials</a>
            </div>

            <div class="finance-bars">
                @forelse($financialRecords as $f)
                    <div class="finance-bar-row">
                        <div class="finance-bar-labels">
                            <span class="finance-bar-title">Fiscal Year {{ $f->year }}</span>
                            <span class="finance-bar-stats">
                                Earnings: <strong style="color: var(--success);">${{ number_format($f->earnings, 0) }}</strong> | 
                                Expenditure: <strong style="color: var(--secondary);">${{ number_format($f->expenditures, 0) }}</strong>
                            </span>
                        </div>
                        
                        @php
                            $total = $f->earnings + $f->expenditures;
                            $earnPercent = $total > 0 ? ($f->earnings / $total) * 100 : 0;
                            $expPercent = $total > 0 ? ($f->expenditures / $total) * 100 : 0;
                        @endphp

                        <div class="bar-track" style="margin-bottom: 8px;">
                            <div class="bar-fill-earnings" style="width: {{ $earnPercent }}%;"></div>
                        </div>
                        <div class="bar-track">
                            <div class="bar-fill-expenditures" style="width: {{ $expPercent }}%;"></div>
                        </div>
                        <p style="font-size: 12px; color: var(--text-muted); margin-top: 10px; font-style: italic;">
                            {{ $f->summary }}
                        </p>
                    </div>
                @empty
                    <p style="text-align: center; color: var(--text-muted); padding: 20px 0;">No financial summaries inputted.</p>
                @endforelse
            </div>
        </div>

    </div>

    <!-- Right Panel: HR Logs & Performance reviews (1/3 width) -->
    <div style="display: flex; flex-direction: column; gap: 30px;">
        
        <!-- Employee Performance Statistics -->
        <div class="content-panel">
            <div class="panel-header">
                <h3 class="panel-title"><i class='bx bx-analyse' style="color: var(--primary);"></i> Performance Overview</h3>
                <a href="{{ route('admin.performance') }}" class="btn btn-outline btn-sm">Reviews</a>
            </div>

            <div style="display: flex; justify-content: space-around; align-items: center; margin-bottom: 24px; padding: 15px; background: rgba(255, 255, 255, 0.02); border-radius: 16px; border: 1px solid var(--border-color);">
                <div style="text-align: center;">
                    <div style="font-size: 28px; font-weight: 700; color: var(--success);">{{ $highPerformers }}</div>
                    <span style="font-size: 12px; color: var(--text-muted); text-transform: uppercase;">High Performers</span>
                </div>
                <div style="border-left: 1px solid var(--border-color); height: 40px;"></div>
                <div style="text-align: center;">
                    <div style="font-size: 28px; font-weight: 700; color: var(--danger);">{{ $lowPerformers }}</div>
                    <span style="font-size: 12px; color: var(--text-muted); text-transform: uppercase;">Low Performers</span>
                </div>
            </div>

            <div class="activity-list">
                <div style="font-size: 13px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px;">Recent Evaluations</div>
                @forelse($reviews->take(3) as $rev)
                    <div class="activity-item">
                        <div class="activity-dot {{ $rev->classification === 'high_performer' ? 'completed' : 'danger' }}" style="{{ $rev->classification === 'low_performer' ? 'color: var(--danger); border-color: var(--danger); background: var(--danger-bg);' : '' }}">
                            <i class="bx {{ $rev->classification === 'high_performer' ? 'bx-trending-up' : 'bx-trending-down' }}"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">{{ $rev->user->name }} (Score: {{ $rev->score }}/10)</div>
                            <div class="activity-desc">{{ Str::limit($rev->evaluation, 60) }}</div>
                            <div class="activity-time">{{ \Carbon\Carbon::parse($rev->created_at)->diffForHumans() }}</div>
                        </div>
                    </div>
                @empty
                    <p style="font-size: 13px; color: var(--text-muted); text-align: center;">No evaluations logged.</p>
                @endforelse
            </div>
        </div>

        <!-- Promotions & Recognitions awarded -->
        <div class="content-panel">
            <div class="panel-header">
                <h3 class="panel-title"><i class='bx bx-trophy' style="color: var(--warning);"></i> Promotion & Recognition</h3>
                <a href="{{ route('admin.promotions') }}" class="btn btn-outline btn-sm">Award</a>
            </div>

            <div class="activity-list">
                @forelse($promotions->take(4) as $p)
                    <div class="activity-item">
                        <div class="activity-dot award">
                            <i class="bx {{ $p->type === 'salary_hike' ? 'bx-coin-stack' : ($p->type === 'promotion' ? 'bx-shield-quarter' : 'bx-gift') }}"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">{{ $p->title }}</div>
                            <div class="activity-desc">{{ $p->user->name }} - {{ $p->detail }}</div>
                            <div class="activity-time">{{ \Carbon\Carbon::parse($p->date_awarded)->format('M d, Y') }}</div>
                        </div>
                    </div>
                @empty
                    <p style="font-size: 13px; color: var(--text-muted); text-align: center; padding: 10px 0;">No awards recorded yet.</p>
                @endforelse
            </div>
        </div>

        <!-- Recent Workspace Attendance logs -->
        <div class="content-panel">
            <div class="panel-header">
                <h3 class="panel-title"><i class='bx bx-calendar-check' style="color: var(--info);"></i> Attendance Log</h3>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 12px; max-height: 250px; overflow-y: auto; padding-right: 5px;">
                @forelse($attendances->take(6) as $att)
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; background: rgba(255, 255, 255, 0.02); border-radius: 12px; border: 1px solid var(--border-color); font-size: 13px;">
                        <div>
                            <span style="font-weight: 600;">{{ $att->user->name }}</span>
                            <div style="color: var(--text-muted); font-size: 11px;">Clocked: {{ $att->clock_in ?? '--' }} to {{ $att->clock_out ?? '--' }}</div>
                        </div>
                        <div>
                            @if($att->status === 'present')
                                <span class="badge badge-success" style="padding: 2px 8px; font-size: 10px;">Present</span>
                            @elseif($att->status === 'late')
                                <span class="badge badge-warning" style="padding: 2px 8px; font-size: 10px;">Late</span>
                            @else
                                <span class="badge badge-danger" style="padding: 2px 8px; font-size: 10px;">Absent</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p style="font-size: 13px; color: var(--text-muted); text-align: center;">No attendance logs seeded.</p>
                @endforelse
            </div>
        </div>

    </div>
</div>
@endsection

