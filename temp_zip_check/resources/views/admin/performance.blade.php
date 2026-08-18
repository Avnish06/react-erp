@extends('layouts.app')

@section('content')
<div class="app-header">
    <div class="header-title">
        <h1>Performance & Growth</h1>
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
    <!-- Left: Existing Performance Reviews Logs -->
    <div class="content-panel">
        <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 class="panel-title"><i class='bx bx-analyse' style="color: var(--primary);"></i> Evaluation Logs</h3>
            <form action="{{ route('admin.performance') }}" method="GET" style="display: flex; gap: 10px; align-items: center;">
                <x-employee-select :employees="$employees" :selected="request('employee_id')" id="perfEmpSelect" name="employee_id" onchange="this.form.submit()" placeholder="All Employees" padding="6px 12px" width="200px" />
            </form>
        </div>
        
        <div style="display: flex; flex-direction: column; gap: 20px;">
            @forelse($reviews as $rev)
                <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--border-color); border-radius: 16px; padding: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                        <div>
                            <h4 style="font-size: 16px; font-weight: 600;">{{ $rev->user->name }}</h4>
                            <span style="font-size: 12px; color: var(--text-muted);">
                                {{ $rev->user->position ?? 'Developer' }} | Review Period: <strong>{{ $rev->review_period }}</strong>
                            </span>
                        </div>
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <span style="font-size: 14px; font-weight: 700; background: rgba(255,255,255,0.05); padding: 4px 10px; border-radius: 8px; border: 1px solid var(--border-color);">
                                Score: {{ $rev->score }}/10
                            </span>
                            @if($rev->classification === 'high_performer')
                                <span class="badge badge-success"><i class='bx bx-trending-up' style="margin-right: 4px;"></i> High Performer</span>
                            @else
                                <span class="badge badge-danger"><i class='bx bx-trending-down' style="margin-right: 4px;"></i> Low Performer</span>
                            @endif
                        </div>
                    </div>
                    
                    <p style="font-size: 14px; color: var(--text-main); margin-bottom: 12px; font-style: italic;">
                        "{{ $rev->evaluation }}"
                    </p>
                    
                    <div style="border-top: 1px dashed var(--border-color); padding-top: 12px; margin-top: 12px; display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: var(--primary);">
                            {{ $rev->classification === 'high_performer' ? 'Growth / Promotion Plan:' : 'Improvement Plan:' }}
                        </span>
                        <span style="font-size: 13px; color: var(--text-muted);">{{ $rev->action_plan }}</span>
                    </div>

                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 12px; text-align: right;">
                        Evaluated by {{ $rev->reviewer->name }} on {{ \Carbon\Carbon::parse($rev->created_at)->format('M d, Y') }}
                    </div>
                </div>
            @empty
                <p style="text-align: center; color: var(--text-muted); padding: 40px 0;">No performance reviews logged yet.</p>
            @endforelse
        </div>
    </div>

    <!-- Right: Evaluate Employee Form -->
    <div class="content-panel" style="height: fit-content;">
        <div class="panel-header">
            <h3 class="panel-title"><i class='bx bx-medal' style="color: var(--secondary);"></i> Log Evaluation</h3>
        </div>

        <form action="{{ route('admin.performance.create') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="user_id" class="form-label">Employee</label>
                <select name="user_id" id="user_id" class="form-input" required>
                    <option value="" disabled selected>Select employee...</option>
                    @foreach($employees as $e)
                        <option value="{{ $e->id }}">{{ $e->name }} ({{ $e->position ?? 'Developer' }})</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="review_period" class="form-label">Review Period</label>
                <input type="text" name="review_period" id="review_period" class="form-input" placeholder="e.g., Yearly 2026, Q2 2026" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="score" class="form-label">Score (1 - 10)</label>
                    <input type="number" name="score" id="score" class="form-input" min="1" max="10" placeholder="e.g. 9" required>
                </div>

                <div class="form-group">
                    <label for="classification" class="form-label">Performance Group</label>
                    <select name="classification" id="classification" class="form-input" required>
                        <option value="high_performer" selected>High Performer (Growth / Promotion)</option>
                        <option value="low_performer">Low Performer (Improvement Plan)</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="evaluation" class="form-label">Evaluation Details</label>
                <textarea name="evaluation" id="evaluation" class="form-input" placeholder="Write overall review and review remarks..." rows="4" required></textarea>
            </div>

            <div class="form-group">
                <label for="action_plan" class="form-label">Action Plan (Promotion details or Improvement steps)</label>
                <textarea name="action_plan" id="action_plan" class="form-input" placeholder="e.g., Hike salary, enlist in developer mentorship programs..." rows="3"></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-block" style="margin-top: 10px;">Log Review & Action Plan</button>
        </form>
    </div>
</div>
@endsection

