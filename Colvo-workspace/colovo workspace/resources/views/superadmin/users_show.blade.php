@extends('layouts.app')

@section('content')
<div class="app-header">
    <div class="header-title">
        <h1>User Activity Profile</h1>
    </div>
    <div class="topbar-right">
        <a href="{{ route('superadmin.users') }}" class="btn btn-outline">
            <i class='bx bx-arrow-back'></i> Back to Users
        </a>
    </div>
</div>

<div class="content-panel" style="margin-bottom: 30px; display: flex; align-items: center; gap: 20px; padding: 30px;">
    <div style="width: 80px; height: 80px; background: var(--primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: bold; box-shadow: 0 4px 15px var(--primary-glow);">
        {{ substr($user->name, 0, 1) }}
    </div>
    <div style="flex: 1;">
        <h2 style="margin: 0 0 5px 0; color: var(--text-main); font-size: 24px;">{{ $user->name }}</h2>
        <div style="display: flex; gap: 15px; color: var(--text-muted); font-size: 14px;">
            <span><i class='bx bx-envelope'></i> {{ $user->email }}</span>
            <span><i class='bx bx-buildings'></i> {{ $user->company->name ?? 'Global User' }}</span>
            <span style="color: var(--primary); font-weight: 500;"><i class='bx bx-badge-check'></i> {{ ucfirst($user->role) }}</span>
        </div>
    </div>
</div>

<div class="dashboard-grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));">
    <!-- Tasks Activity -->
    <div class="content-panel">
        <div class="panel-header">
            <h3 class="panel-title"><i class='bx bx-task' style="color: var(--primary);"></i> Recent Tasks</h3>
        </div>
        <div style="padding: 20px;">
            @if($user->tasks->count() > 0)
                <ul style="list-style: none; padding: 0; margin: 0;">
                @foreach($user->tasks->take(5) as $task)
                    <li style="padding: 10px 0; border-bottom: 1px solid var(--border-color);">
                        <div style="font-weight: 500; color: var(--text-main);">{{ $task->title }}</div>
                        <div style="font-size: 12px; color: var(--text-muted); display: flex; justify-content: space-between; margin-top: 4px;">
                            <span>{{ $task->project->name ?? 'N/A' }}</span>
                            <span class="status-badge status-{{ strtolower($task->status) }}">{{ ucfirst($task->status) }}</span>
                        </div>
                    </li>
                @endforeach
                </ul>
            @else
                <p style="color: var(--text-muted); margin: 0; font-size: 14px;">No tasks assigned.</p>
            @endif
        </div>
    </div>

    <!-- Attendance Activity -->
    <div class="content-panel">
        <div class="panel-header">
            <h3 class="panel-title"><i class='bx bx-time-five' style="color: var(--primary);"></i> Recent Attendance</h3>
        </div>
        <div style="padding: 20px;">
            @if($user->attendances->count() > 0)
                <ul style="list-style: none; padding: 0; margin: 0;">
                @foreach($user->attendances as $att)
                    <li style="padding: 10px 0; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between;">
                        <span style="font-size: 14px; color: var(--text-main);">{{ \Carbon\Carbon::parse($att->date)->format('M d, Y') }}</span>
                        <span class="status-badge {{ $att->status === 'present' ? 'status-approved' : ($att->status === 'absent' ? 'status-rejected' : 'status-pending') }}">
                            {{ ucfirst($att->status) }}
                        </span>
                    </li>
                @endforeach
                </ul>
            @else
                <p style="color: var(--text-muted); margin: 0; font-size: 14px;">No attendance records found.</p>
            @endif
        </div>
    </div>

    <!-- Leaves Activity -->
    <div class="content-panel">
        <div class="panel-header">
            <h3 class="panel-title"><i class='bx bx-calendar-event' style="color: var(--primary);"></i> Recent Leaves</h3>
        </div>
        <div style="padding: 20px;">
            @if($user->leaves->count() > 0)
                <ul style="list-style: none; padding: 0; margin: 0;">
                @foreach($user->leaves as $leave)
                    <li style="padding: 10px 0; border-bottom: 1px solid var(--border-color);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                            <span style="font-size: 14px; font-weight: 500; color: var(--text-main);">{{ ucfirst($leave->type) }}</span>
                            <span class="status-badge {{ $leave->status === 'approved' ? 'status-approved' : ($leave->status === 'rejected' ? 'status-rejected' : 'status-pending') }}">
                                {{ ucfirst($leave->status) }}
                            </span>
                        </div>
                        <div style="font-size: 12px; color: var(--text-muted);">
                            {{ \Carbon\Carbon::parse($leave->start_date)->format('M d') }} - {{ \Carbon\Carbon::parse($leave->end_date)->format('M d, Y') }}
                        </div>
                    </li>
                @endforeach
                </ul>
            @else
                <p style="color: var(--text-muted); margin: 0; font-size: 14px;">No leave requests found.</p>
            @endif
        </div>
    </div>

    <!-- Payroll Activity -->
    <div class="content-panel">
        <div class="panel-header">
            <h3 class="panel-title"><i class='bx bx-receipt' style="color: var(--primary);"></i> Recent Payslips</h3>
        </div>
        <div style="padding: 20px;">
            @if($user->payrolls->count() > 0)
                <ul style="list-style: none; padding: 0; margin: 0;">
                @foreach($user->payrolls as $payroll)
                    <li style="padding: 10px 0; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="font-size: 14px; font-weight: 500; color: var(--text-main);">{{ \Carbon\Carbon::parse($payroll->created_at)->format('F Y') }}</div>
                            <div style="font-size: 12px; color: var(--text-muted);">${{ number_format($payroll->net_pay, 2) }}</div>
                        </div>
                        <span class="status-badge status-approved">Paid</span>
                    </li>
                @endforeach
                </ul>
            @else
                <p style="color: var(--text-muted); margin: 0; font-size: 14px;">No payslips generated yet.</p>
            @endif
        </div>
    </div>
</div>
@endsection
