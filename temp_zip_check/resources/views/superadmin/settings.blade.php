@extends('layouts.app')

@section('content')
<div class="app-header">
    <div class="header-title">
        <h1>System Settings</h1>
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
    <h2 class="welcome-title">Platform Configuration</h2>
    <p class="welcome-quote">Manage global preferences, email servers, and system-wide configurations.</p>
</div>

@if(session('success'))
<div class="alert alert-success" style="margin-bottom: 24px;">
    <i class='bx bx-check-circle'></i>
    <span>{{ session('success') }}</span>
</div>
@endif

<div class="section-grid">
    <div class="content-panel">
        <div class="panel-header">
            <h3 class="panel-title"><i class='bx bx-slider-alt' style="color: var(--primary);"></i> General Settings</h3>
        </div>
        
        <form action="{{ route('superadmin.settings.update') }}" method="POST" style="padding: 20px;">
            @csrf
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 13px; font-weight: 500; color: var(--text-muted); margin-bottom: 8px;">Platform Name</label>
                <input type="text" name="app_name" class="form-control" value="Colovo Workspace" placeholder="e.g. My HRMS Platform">
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 13px; font-weight: 500; color: var(--text-muted); margin-bottom: 8px;">Support Email</label>
                <input type="email" name="support_email" class="form-control" value="support@colovo.com" placeholder="support@company.com">
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 13px; font-weight: 500; color: var(--text-muted); margin-bottom: 8px;">Default Currency</label>
                <select name="currency" class="form-control">
                    <option value="INR" selected>Indian Rupee (₹)</option>
                    <option value="USD">US Dollar ($)</option>
                    <option value="EUR">Euro (€)</option>
                    <option value="GBP">British Pound (£)</option>
                </select>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                    <input type="checkbox" name="allow_registration" checked style="width: 18px; height: 18px; accent-color: var(--primary);">
                    <span style="font-size: 14px; color: var(--text-main); font-weight: 500;">Allow New Workspace Registrations</span>
                </label>
                <p style="font-size: 12px; color: var(--text-muted); margin-top: 4px; margin-left: 28px;">If checked, external users can create their own workspaces.</p>
            </div>
            
            <button type="submit" class="btn btn-primary" style="margin-top: 10px;">Save General Settings</button>
        </form>
    </div>

    <div style="display: flex; flex-direction: column; gap: 30px;">
        <div class="content-panel">
            <div class="panel-header">
                <h3 class="panel-title"><i class='bx bx-envelope' style="color: var(--primary);"></i> SMTP / Email Server</h3>
            </div>
            
            <form action="{{ route('superadmin.settings.update') }}" method="POST" style="padding: 20px;">
                @csrf
                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-size: 13px; font-weight: 500; color: var(--text-muted); margin-bottom: 8px;">Mail Mailer</label>
                    <input type="text" name="mail_mailer" class="form-control" value="smtp" placeholder="smtp">
                </div>
                <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                    <div style="flex: 1;">
                        <label style="display: block; font-size: 13px; font-weight: 500; color: var(--text-muted); margin-bottom: 8px;">Mail Host</label>
                        <input type="text" name="mail_host" class="form-control" value="sandbox.smtp.mailtrap.io">
                    </div>
                    <div style="width: 100px;">
                        <label style="display: block; font-size: 13px; font-weight: 500; color: var(--text-muted); margin-bottom: 8px;">Port</label>
                        <input type="text" name="mail_port" class="form-control" value="2525">
                    </div>
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-size: 13px; font-weight: 500; color: var(--text-muted); margin-bottom: 8px;">Mail Username</label>
                    <input type="text" name="mail_username" class="form-control" value="**************">
                </div>
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 13px; font-weight: 500; color: var(--text-muted); margin-bottom: 8px;">Mail Password</label>
                    <input type="password" name="mail_password" class="form-control" value="password">
                </div>
                <button type="submit" class="btn btn-primary" style="margin-top: 5px;">Update SMTP Config</button>
            </form>
        </div>
        
        <div class="content-panel">
            <div class="panel-header" style="background: var(--danger); border-radius: 12px 12px 0 0;">
                <h3 class="panel-title" style="color: white;"><i class='bx bx-error-circle'></i> Danger Zone</h3>
            </div>
            <div style="padding: 20px;">
                <p style="font-size: 14px; color: var(--text-muted); margin-bottom: 20px;">Actions here can have severe consequences on the platform. Proceed with extreme caution.</p>
                <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 15px; border-bottom: 1px solid var(--border-color); margin-bottom: 15px;">
                    <div>
                        <div style="font-weight: 600; font-size: 14px; color: var(--text-main);">Clear Application Cache</div>
                        <div style="font-size: 12px; color: var(--text-muted);">Clears route, view, and config caches.</div>
                    </div>
                    <button class="btn btn-outline" style="border-color: var(--danger); color: var(--danger);">Clear Cache</button>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-weight: 600; font-size: 14px; color: var(--text-main);">Maintenance Mode</div>
                        <div style="font-size: 12px; color: var(--text-muted);">Take the entire platform down for maintenance.</div>
                    </div>
                    <button class="btn btn-primary" style="background: var(--danger); border-color: var(--danger);">Enable</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
