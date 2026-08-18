@extends('layouts.app')

@section('content')
<div class="app-header">
    <div class="header-title">
        <h1>Global Control Center</h1>
        <p>Welcome Super Admin. Oversee all companies and global operations.</p>
    </div>
    <div class="topbar-right">
        <div style="display: flex; align-items: center; gap: 10px;">
            <div class="user-avatar-placeholder" style="width: 40px; height: 40px; font-size: 14px;">
                {{ substr(auth()->user()->name, 0, 1) }}
            </div>
            <div style="font-size: 13px; font-weight: 600; color: var(--text-main); text-align: left;">
                <div>{{ auth()->user()->name }}</div>
                <div style="font-size: 11px; color: var(--text-muted); font-weight: normal;">{{ auth()->user()->role }}</div>
            </div>
        </div>
    </div>
</div>

<div class="metrics-grid">
    <div class="metric-card amber">
        <div class="metric-card-icon">
            <i class='bx bx-buildings'></i>
        </div>
        <div class="metric-card-details">
            <span class="metric-card-title">Total Companies</span>
            <span class="metric-card-value">{{ $companiesCount }}</span>
        </div>
    </div>
    
    <div class="metric-card purple">
        <div class="metric-card-icon">
            <i class='bx bx-group'></i>
        </div>
        <div class="metric-card-details">
            <span class="metric-card-title">Total Global Users</span>
            <span class="metric-card-value">{{ $usersCount }}</span>
        </div>
    </div>
    
    <div class="metric-card blue">
        <div class="metric-card-icon">
            <i class='bx bx-layer'></i>
        </div>
        <div class="metric-card-details">
            <span class="metric-card-title">Total Projects</span>
            <span class="metric-card-value">{{ $projectsCount }}</span>
        </div>
    </div>
</div>


@endsection
