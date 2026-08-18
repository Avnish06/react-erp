@extends('layouts.app')

@section('content')
<div class="app-header">
    <div class="header-title">
        <h1>Workspace Reports</h1>
    </div>
    
    <div class="topbar-right">
        <div class="noti-bell">
            <i class='bx bx-bell'></i>
            <span class="noti-bell-badge">3</span>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <div class="user-avatar-placeholder" style="width: 38px; height: 38px; font-size: 14px;">
                {{ substr(auth()->user()->name, 0, 1) }}
            </div>
            <div style="font-size: 13px; font-weight: 600; color: var(--text-main); text-align: left;">
                <div>{{ auth()->user()->name }}</div>
                <div style="font-size: 10px; color: var(--text-muted); font-weight: normal;">{{ auth()->user()->position ?? auth()->user()->role }}</div>
            </div>
        </div>
    </div>
</div>

<div class="welcome-banner" style="padding: 20px 30px; margin-bottom: 30px;">
    <h2 class="welcome-title">Generated Reports</h2>
    <p class="welcome-quote">Analyze your workspace performance, track project completion, and monitor task distribution.</p>
</div>

<div class="metrics-grid">
    <!-- Project Completion Report -->
    <div class="content-panel" style="flex: 1; grid-column: span 1;">
        <div class="panel-header">
            <h3 class="panel-title"><i class='bx bx-pie-chart-alt-2' style="color: var(--primary);"></i> Project Distribution</h3>
        </div>
        <div style="text-align: center; padding: 20px 0;">
            <div style="position: relative; width: 150px; height: 150px; border-radius: 50%; background: conic-gradient(
                var(--success) 0% {{ $totalProjects > 0 ? ($completedProjects / $totalProjects) * 100 : 0 }}%, 
                var(--warning) {{ $totalProjects > 0 ? ($completedProjects / $totalProjects) * 100 : 0 }}% {{ $totalProjects > 0 ? (($completedProjects + $activeProjects) / $totalProjects) * 100 : 0 }}%, 
                rgba(255,255,255,0.1) {{ $totalProjects > 0 ? (($completedProjects + $activeProjects) / $totalProjects) * 100 : 0 }}% 100%
            ); margin: 0 auto 20px; display: flex; align-items: center; justify-content: center;">
                <div style="width: 110px; height: 110px; border-radius: 50%; background: var(--panel-bg); display: flex; flex-direction: column; align-items: center; justify-content: center;">
                    <span style="font-size: 24px; font-weight: 700; color: var(--text-main);">{{ $totalProjects }}</span>
                    <span style="font-size: 11px; color: var(--text-muted);">Total</span>
                </div>
            </div>
            
            <div style="display: flex; justify-content: center; gap: 20px; font-size: 13px;">
                <div style="display: flex; align-items: center; gap: 6px;">
                    <div style="width: 12px; height: 12px; border-radius: 3px; background: var(--success);"></div>
                    <span>{{ $completedProjects }} Completed</span>
                </div>
                <div style="display: flex; align-items: center; gap: 6px;">
                    <div style="width: 12px; height: 12px; border-radius: 3px; background: var(--warning);"></div>
                    <span>{{ $activeProjects }} Active</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Task Completion Report -->
    <div class="content-panel" style="flex: 1; grid-column: span 1;">
        <div class="panel-header">
            <h3 class="panel-title"><i class='bx bx-list-check' style="color: var(--secondary);"></i> Task Efficiency</h3>
        </div>
        <div style="text-align: center; padding: 20px 0;">
            <div style="position: relative; width: 150px; height: 150px; border-radius: 50%; background: conic-gradient(
                var(--success) 0% {{ $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0 }}%, 
                rgba(255,255,255,0.1) {{ $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0 }}% 100%
            ); margin: 0 auto 20px; display: flex; align-items: center; justify-content: center;">
                <div style="width: 110px; height: 110px; border-radius: 50%; background: var(--panel-bg); display: flex; flex-direction: column; align-items: center; justify-content: center;">
                    <span style="font-size: 24px; font-weight: 700; color: var(--success);">
                        {{ $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0 }}%
                    </span>
                    <span style="font-size: 11px; color: var(--text-muted);">Completed</span>
                </div>
            </div>
            
            <div style="font-size: 14px; font-weight: 600; color: var(--text-main);">
                {{ $completedTasks }} out of {{ $totalTasks }} tasks completed.
            </div>
        </div>
    </div>

    <!-- Performance Classification Report -->
    <div class="content-panel" style="flex: 1; grid-column: span 1;">
        <div class="panel-header">
            <h3 class="panel-title"><i class='bx bx-analyse' style="color: var(--info);"></i> Staff Performance</h3>
        </div>
        
        <div style="padding: 20px 0;">
            @php
                $totalReviews = $highPerformers + $lowPerformers;
                $highPercent = $totalReviews > 0 ? ($highPerformers / $totalReviews) * 100 : 0;
                $lowPercent = $totalReviews > 0 ? ($lowPerformers / $totalReviews) * 100 : 0;
            @endphp
            
            <div style="margin-bottom: 25px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px;">
                    <span style="font-weight: 600;">High Performers</span>
                    <span>{{ $highPerformers }} ({{ round($highPercent) }}%)</span>
                </div>
                <div class="bar-track" style="height: 10px; border-radius: 5px;">
                    <div style="width: {{ $highPercent }}%; background: var(--success); height: 100%; border-radius: 5px;"></div>
                </div>
            </div>
            
            <div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px;">
                    <span style="font-weight: 600;">Low Performers</span>
                    <span>{{ $lowPerformers }} ({{ round($lowPercent) }}%)</span>
                </div>
                <div class="bar-track" style="height: 10px; border-radius: 5px;">
                    <div style="width: {{ $lowPercent }}%; background: var(--danger); height: 100%; border-radius: 5px;"></div>
                </div>
            </div>
            
            @if($totalReviews === 0)
                <p style="text-align: center; color: var(--text-muted); font-size: 13px; margin-top: 20px;">No performance data available.</p>
            @endif
        </div>
    </div>
</div>

<div class="content-panel">
    <div class="panel-header">
        <h3 class="panel-title"><i class='bx bx-export' style="color: var(--primary);"></i> Export Operations</h3>
    </div>
    <div style="display: flex; align-items: center; gap: 15px; padding: 10px 0;">
        <button class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; font-weight: 600;" onclick="alert('PDF generation feature coming soon.')"><i class='bx bxs-file-pdf' style="margin: 0;"></i> Export to PDF</button>
        <button class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; font-weight: 600;" onclick="alert('Excel export feature coming soon.')"><i class='bx bxs-file-export' style="margin: 0;"></i> Export to Excel</button>
    </div>
</div>

@endsection

