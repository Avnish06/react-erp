@extends('layouts.app')

@section('content')
<div class="app-header">
    <div class="header-title">
        <h1>Global Report</h1>
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

<div class="welcome-banner" style="padding: 20px 30px; margin-bottom: 30px;">
    <h2 class="welcome-title">Platform Reports</h2>
    <p class="welcome-quote">Aggregated data on overall performance across all workspaces.</p>
</div>

<div class="content-panel">
    <div class="panel-header">
        <h3 class="panel-title"><i class='bx bxs-report' style="color: var(--primary);"></i> Workspace Performance</h3>
    </div>
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Workspace</th>
                    <th>Total Users</th>
                    <th>Total Projects</th>
                    <th>Total Tasks</th>
                    <th>Performance Score</th>
                </tr>
            </thead>
            <tbody>
                @foreach($companies as $company)
                <tr>
                    <td>
                        <div style="font-weight: 600; color: var(--text-main);">{{ $company->name }}</div>
                    </td>
                    <td>{{ $company->users_count }}</td>
                    <td>{{ $company->projects_count }}</td>
                    <td>{{ $company->tasks_count }}</td>
                    <td>
                        @php
                            // Mock performance score based on tasks and projects for display
                            $score = $company->tasks_count > 0 ? min(100, round(($company->projects_count / $company->tasks_count) * 100 + 50)) : 0;
                        @endphp
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="flex: 1; background: var(--border-color); height: 8px; border-radius: 4px; overflow: hidden;">
                                <div style="background: var(--primary); height: 100%; width: {{ $score }}%;"></div>
                            </div>
                            <span style="font-size: 12px; font-weight: 600; color: var(--text-muted);">{{ $score }}%</span>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="section-grid" style="margin-top: 30px;">
    <div class="content-panel">
        <div class="panel-header">
            <h3 class="panel-title"><i class='bx bx-trending-up' style="color: var(--primary);"></i> Platform Growth</h3>
        </div>
        <p style="color: var(--text-muted); font-size: 14px; padding: 0 20px;">Total projects over time across all workspaces.</p>
        <div style="height: 200px; display: flex; align-items: flex-end; gap: 10px; margin-top: 20px; padding: 0 20px 20px 20px;">
            @for($i=1; $i<=6; $i++)
            <div style="flex: 1; background: var(--primary); opacity: {{ 0.3 + ($i * 0.1) }}; height: {{ rand(30, 100) }}%; border-radius: 4px 4px 0 0;"></div>
            @endfor
        </div>
        <div style="display: flex; justify-content: space-between; margin-top: 10px; font-size: 11px; color: var(--text-muted); padding: 0 20px 20px 20px;">
            <span>Jan</span><span>Feb</span><span>Mar</span><span>Apr</span><span>May</span><span>Jun</span>
        </div>
    </div>

    <div class="content-panel">
        <div class="panel-header">
            <h3 class="panel-title"><i class='bx bx-pie-chart' style="color: var(--primary);"></i> User Distribution</h3>
        </div>
        <p style="color: var(--text-muted); font-size: 14px; padding: 0 20px;">Proportion of employees by workspace.</p>
        <div style="margin-top: 20px; padding: 0 20px 20px 20px;">
            @foreach($companies as $company)
                @if($company->users_count > 0)
                <div style="margin-bottom: 15px;">
                    <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 5px;">
                        <span>{{ $company->name }}</span>
                        <span style="font-weight: 600;">{{ $company->users_count }}</span>
                    </div>
                    <div style="background: var(--border-color); height: 6px; border-radius: 3px; overflow: hidden;">
                        <div style="background: {{ ['#3b82f6', '#8b5cf6', '#10b981', '#f59e0b'][$loop->index % 4] }}; height: 100%; width: {{ min(100, $company->users_count * 10) }}%;"></div>
                    </div>
                </div>
                @endif
            @endforeach
        </div>
    </div>
</div>
@endsection
