@extends('layouts.app')

@section('content')
<div class="app-header">
    <div class="header-title">
        <h1>Admin Profile</h1>
        <p>Manage your account settings and change password.</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">
        <i class='bx bx-check-circle'></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <i class='bx bx-error-circle'></i>
        <ul style="margin-left: 20px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="dashboard-grid">
    <!-- Profile Info Section -->
    <div class="content-panel">
        <div class="panel-header">
            <h3 class="panel-title">User Information</h3>
        </div>
        <div class="panel-body">
            <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 25px;">
                <div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--secondary)); display: flex; align-items: center; justify-content: center; color: white; font-size: 32px; font-weight: bold;">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <div>
                    <h2 style="font-size: 20px; color: var(--text-main); margin-bottom: 5px;">{{ $user->name }}</h2>
                    <span class="badge badge-primary">{{ ucfirst($user->role) }}</span>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" class="form-input" value="{{ $user->name }}" readonly style="background: var(--bg-main);">
                </div>
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-input" value="{{ $user->email }}" readonly style="background: var(--bg-main);">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <input type="text" class="form-input" value="{{ ucfirst($user->role) }}" readonly style="background: var(--bg-main);">
                </div>
                <div class="form-group">
                    <label class="form-label">Account Created</label>
                    <input type="text" class="form-input" value="{{ $user->created_at->format('M d, Y') }}" readonly style="background: var(--bg-main);">
                </div>
            </div>
        </div>
    </div>

    <!-- Change Password Section -->
    <div class="content-panel" id="password-section">
        <div class="panel-header">
            <h3 class="panel-title">Change Password</h3>
        </div>
        <div class="panel-body">
            <form action="{{ route('admin.profile.password') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Current Password</label>
                    <input type="password" name="current_password" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <input type="password" name="new_password" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="new_password_confirmation" class="form-input" required>
                </div>
                <div style="margin-top: 20px;">
                    <button type="submit" class="btn btn-primary"><i class='bx bx-lock-alt'></i> Update Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
