@extends('layouts.app')

@section('content')
<div class="app-header">
    <div class="header-title">
        <h1>Global User Management</h1>
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
    <h2 class="welcome-title">Global User Directory</h2>
    <p class="welcome-quote">Manage roles, permissions, and accounts across all workspaces globally.</p>
</div>

@if(session('success'))
<div class="alert alert-success" style="margin-bottom: 24px;">
    <i class='bx bx-check-circle'></i>
    <span>{{ session('success') }}</span>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger" style="margin-bottom: 24px;">
    <i class='bx bx-error-circle'></i>
    <span>There were some problems adding the user. Please check the form.</span>
</div>
@endif

@if(request('company_id'))
@php
    $activeCompany = $companies->firstWhere('id', request('company_id'));
@endphp
<div class="content-panel" style="margin-bottom: 30px;">
    <div class="panel-header">
        <h3 class="panel-title"><i class='bx bx-user-plus' style="color: var(--primary);"></i> Add User to {{ $activeCompany->name ?? 'Workspace' }}</h3>
    </div>
    <form action="{{ route('superadmin.users.store') }}" method="POST" style="display: flex; gap: 15px; align-items: flex-end; padding: 20px 0 10px 0; flex-wrap: wrap;">
        @csrf
        <input type="hidden" name="company_id" value="{{ request('company_id') }}">
        <div style="flex: 1; min-width: 200px;">
            <label style="display: block; font-size: 13px; font-weight: 500; color: var(--text-muted); margin-bottom: 8px;">Full Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div style="flex: 1; min-width: 200px;">
            <label style="display: block; font-size: 13px; font-weight: 500; color: var(--text-muted); margin-bottom: 8px;">Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div style="flex: 1; min-width: 150px;">
            <label style="display: block; font-size: 13px; font-weight: 500; color: var(--text-muted); margin-bottom: 8px;">Role</label>
            <select name="role" class="form-control" required>
                <option value="employee">Employee</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        <div style="flex: 1; min-width: 150px;">
            <label style="display: block; font-size: 13px; font-weight: 500; color: var(--text-muted); margin-bottom: 8px;">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary" style="height: 42px;">Add User</button>
    </form>
</div>
@endif

<div class="content-panel">
    <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <h3 class="panel-title" style="margin: 0;"><i class='bx bx-group' style="color: var(--primary);"></i> User Directory</h3>
        
        <form method="GET" action="{{ route('superadmin.users') }}" style="display: flex; align-items: center; gap: 10px;" id="filterForm">
            <label style="font-size: 13px; color: var(--text-muted); font-weight: 500;">Filter by Workspace:</label>
            
            <div class="custom-select-wrapper" style="position: relative; min-width: 250px;">
                @php
                    $selectedCompany = $companies->firstWhere('id', request('company_id'));
                @endphp
                <div class="custom-select-trigger" onclick="document.getElementById('custom-options-companies').classList.toggle('show')" style="height: 38px; padding: 6px 12px; border: 1px solid var(--border-color); border-radius: 6px; background: white; display: flex; align-items: center; justify-content: space-between; cursor: pointer;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        @if($selectedCompany && $selectedCompany->logo)
                            <img src="{{ asset('storage/' . $selectedCompany->logo) }}" alt="Logo" style="width: 20px; height: 20px; border-radius: 4px; object-fit: cover;">
                        @elseif($selectedCompany)
                            <div style="width: 20px; height: 20px;  border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: bold;">
                                {{ substr($selectedCompany->name, 0, 1) }}
                            </div>
                        @else
                            <i class='bx bx-buildings' style="font-size: 18px; color: var(--text-muted);"></i>
                        @endif
                        <span style="font-size: 14px; color: var(--text-main);">{{ $selectedCompany ? $selectedCompany->name : 'All Workspaces' }}</span>
                    </div>
                    <i class='bx bx-chevron-down' style="color: var(--text-muted);"></i>
                </div>
                
                <div id="custom-options-companies" class="custom-options" style="position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid var(--border-color); border-radius: 6px; margin-top: 5px; z-index: 100; max-height: 300px; overflow-y: auto; display: none; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                    @foreach($companies as $c)
                        <a href="{{ route('superadmin.users', ['company_id' => $c->id]) }}" style="display: flex; align-items: center; gap: 8px; padding: 10px 12px; text-decoration: none; color: var(--text-main); border-bottom: 1px solid var(--border-color);">
                            @if($c->logo)
                                <img src="{{ asset('storage/' . $c->logo) }}" alt="Logo" style="width: 20px; height: 20px; border-radius: 4px; object-fit: cover;">
                            @else
                                <div style="width: 20px; height: 20px;  border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: bold;">
                                    {{ substr($c->name, 0, 1) }}
                                </div>
                            @endif
                            <span style="font-size: 14px;">{{ $c->name }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            @if(request('company_id'))
                <a href="{{ route('superadmin.users') }}" class="btn btn-sm btn-outline" style="height: 38px; display: flex; align-items: center; padding: 0 10px;">Clear</a>
            @endif
        </form>
        
        <script>
            document.addEventListener('click', function(event) {
                if (!event.target.closest('.custom-select-wrapper')) {
                    const el = document.getElementById('custom-options-companies');
                    if(el) el.classList.remove('show');
                }
            });
        </script>
        <style>
            .custom-options.show { display: block !important; }
            .custom-options a:hover { background: var(--bg-main); }
        </style>
    </div>
    
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Workspace</th>
                    <th>Current Role</th>
                    <th>Change Role</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>
                        <div style="font-weight: 500; color: var(--text-main);">
                            <a href="{{ route('superadmin.users.show', $user->id) }}" style="text-decoration: none; color: inherit;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='inherit'">
                                {{ $user->name }}
                            </a>
                        </div>
                        <div style="font-size: 12px; color: var(--text-muted);">{{ $user->email }}</div>
                    </td>
                    <td>{{ $user->company->name ?? 'No Workspace' }}</td>
                    <td>
                        <span class="status-badge" style="
                            {{ $user->role === 'superadmin' ? 'background: #fce7f3; color: #be185d;' : ($user->role === 'admin' ? 'background: #e0e7ff; color: #4338ca;' : 'background: #f1f5f9; color: #475569;') }}">
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>
                    <td>
                        @if($user->id !== auth()->id())
                        <form action="{{ route('superadmin.users.role', $user->id) }}" method="POST" style="display: flex; gap: 10px;">
                            @csrf
                            @method('PATCH')
                            <select name="role" class="form-control" style="padding: 6px; font-size: 12px; height: auto;">
                                <option value="employee" {{ $user->role === 'employee' ? 'selected' : '' }}>Employee</option>
                                <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-outline" style="padding: 6px 12px;">Update</button>
                        </form>
                        @else
                            <span style="font-size: 12px; color: var(--text-muted); font-style: italic;">Cannot change your own role</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
