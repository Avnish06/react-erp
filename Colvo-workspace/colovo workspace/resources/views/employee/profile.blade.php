@extends('layouts.app')

@section('content')
<div class="app-header">
    <div class="header-title">
        <h1>My Profile</h1>
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


    <form action="{{ route('employee.profile.details') }}" method="POST" enctype="multipart/form-data" style="display: contents;">
        @csrf
        
        <!-- Personal Details Section -->
        <div class="content-panel" style="margin-bottom: 24px;">
            <div class="panel-header">
                <h3 class="panel-title">Personal Details & Documents</h3>
            </div>
            <div class="panel-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Father's Name</label>
                        <input type="text" name="father_name" class="form-input" value="{{ old('father_name', $user->detail->father_name ?? '') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Mother's Name</label>
                        <input type="text" name="mother_name" class="form-input" value="{{ old('mother_name', $user->detail->mother_name ?? '') }}">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Father's Occupation</label>
                        <input type="text" name="father_occupation" class="form-input" value="{{ old('father_occupation', $user->detail->father_occupation ?? '') }}">
                    </div>
                </div>

                <h4 style="margin-top:20px; margin-bottom:15px; color:var(--text-main); font-size:16px;">Documents</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">10th Marksheet @if(isset($user->detail->marksheet_10th_path)) <a href="{{ asset('storage/'.$user->detail->marksheet_10th_path) }}" target="_blank" style="font-size:12px; color:var(--primary);">(View Current)</a> @endif</label>
                        <input type="file" name="marksheet_10th" class="form-input" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                    <div class="form-group">
                        <label class="form-label">12th Marksheet @if(isset($user->detail->marksheet_12th_path)) <a href="{{ asset('storage/'.$user->detail->marksheet_12th_path) }}" target="_blank" style="font-size:12px; color:var(--primary);">(View Current)</a> @endif</label>
                        <input type="file" name="marksheet_12th" class="form-input" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Passport Size Photo @if(isset($user->detail->passport_photo_path)) <a href="{{ asset('storage/'.$user->detail->passport_photo_path) }}" target="_blank" style="font-size:12px; color:var(--primary);">(View Current)</a> @endif</label>
                        <input type="file" name="passport_photo" class="form-input" accept=".jpg,.jpeg,.png">
                    </div>
                </div>
            </div>
        </div>

        <!-- Bank Details Section -->
        <div class="content-panel" style="margin-bottom: 24px;">
            <div class="panel-header">
                <h3 class="panel-title">Bank Information</h3>
            </div>
            <div class="panel-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Bank Name</label>
                        <input type="text" name="bank_name" class="form-input" value="{{ old('bank_name', $user->detail->bank_name ?? '') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Bank Account Number</label>
                        <input type="text" name="bank_account_no" class="form-input" value="{{ old('bank_account_no', $user->detail->bank_account_no ?? '') }}">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Bank IFSC Code</label>
                        <input type="text" name="bank_ifsc" class="form-input" value="{{ old('bank_ifsc', $user->detail->bank_ifsc ?? '') }}">
                    </div>
                </div>
                
                <div style="margin-top: 20px;">
                    <button type="submit" class="btn btn-primary"><i class='bx bx-save'></i> Save Details</button>
                </div>
            </div>
        </div>
    </form>

    <!-- Change Password Section -->
    <div class="content-panel" id="password-section">
        <div class="panel-header">
            <h3 class="panel-title">Change Password</h3>
        </div>
        <div class="panel-body">
            <form action="{{ route('employee.profile.password') }}" method="POST">
                @csrf
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-input" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="new_password_confirmation" class="form-input" required>
                    </div>
                </div>
                <div style="margin-top: 20px;">
                    <button type="submit" class="btn btn-primary"><i class='bx bx-lock-alt'></i> Update Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
