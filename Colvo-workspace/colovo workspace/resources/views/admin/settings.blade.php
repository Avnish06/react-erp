@extends('layouts.app')

@section('content')
<div class="app-header">
    <div class="header-title">
        <h1>Workspace Settings</h1>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 24px; padding: 15px; background: #ecfdf5; color: #065f46; border-radius: 8px; border: 1px solid #a7f3d0;">
        <i class='bx bx-check-circle'></i> {{ session('success') }}
    </div>
@endif
@if ($errors->any())
    <div class="alert alert-danger" style="margin-bottom: 24px; padding: 15px; background: #fef2f2; color: #991b1b; border-radius: 8px; border: 1px solid #fecaca;">
        <ul style="margin-bottom: 0; padding-left: 20px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="section-grid" style="grid-template-columns: 1fr;">
    <div class="content-panel">
        <div class="panel-header">
            <h3 class="panel-title">Shift Configuration</h3>
        </div>
        
        <form action="{{ route('admin.settings.store') }}" method="POST">
            @csrf
            
            @php
                $settings = $company->settings ?? [];
                $shiftStart = $settings['shift_start'] ?? '10:00 AM';
                $shiftEnd = $settings['shift_end'] ?? '06:00 PM';
                $lunchStart = $settings['lunch_start'] ?? '02:00 PM';
                $lunchEnd = $settings['lunch_end'] ?? '03:00 PM';
                
                $attendanceSetting = $company->attendanceSetting;
                $officeLat = $attendanceSetting ? $attendanceSetting->office_latitude : '';
                $officeLng = $attendanceSetting ? $attendanceSetting->office_longitude : '';
                $radius = $attendanceSetting ? $attendanceSetting->allowed_radius : 100;
            @endphp
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 5px; font-size: 13px; font-weight: 600;">Shift Start Time</label>
                    <input type="text" name="shift_start" value="{{ $shiftStart }}" placeholder="e.g. 10:10 AM" required class="form-input">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-size: 13px; font-weight: 600;">Shift End Time</label>
                    <input type="text" name="shift_end" value="{{ $shiftEnd }}" placeholder="e.g. 06:10 PM" required class="form-input">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
                <div>
                    <label style="display: block; margin-bottom: 5px; font-size: 13px; font-weight: 600;">Lunch Start Time</label>
                    <input type="text" name="lunch_start" value="{{ $lunchStart }}" placeholder="e.g. 02:00 PM" required class="form-input">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-size: 13px; font-weight: 600;">Lunch End Time</label>
                    <input type="text" name="lunch_end" value="{{ $lunchEnd }}" placeholder="e.g. 03:00 PM" required class="form-input">
                </div>
            </div>

            <hr style="border: 0; border-top: 1px solid #e2e8f0; margin-bottom: 30px;">

            <div class="panel-header" style="margin-bottom: 20px;">
                <h3 class="panel-title">Attendance & Geofence Settings</h3>
                <p style="font-size: 12px; color: #64748b; margin-top: 5px;">Set your office location coordinates. Employees can only mark attendance within the allowed radius.</p>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 5px; font-size: 13px; font-weight: 600;">Office Latitude</label>
                    <input type="text" id="office_latitude" name="office_latitude" value="{{ $officeLat }}" placeholder="e.g. 28.704060" class="form-input">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-size: 13px; font-weight: 600;">Office Longitude</label>
                    <input type="text" id="office_longitude" name="office_longitude" value="{{ $officeLng }}" placeholder="e.g. 77.102493" class="form-input">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-size: 13px; font-weight: 600;">Allowed Radius (meters)</label>
                    <input type="number" name="allowed_radius" value="{{ $radius }}" placeholder="e.g. 100" class="form-input">
                </div>
            </div>

            <div style="margin-bottom: 30px;">
                <button type="button" class="btn btn-sm" onclick="getCurrentLocation()" style="background: #e2e8f0; color: #0f172a; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; font-size: 13px; display: inline-flex; align-items: center; gap: 5px;">
                    <i class='bx bx-map-pin'></i> Get My Current Location
                </button>
                <span id="location-status" style="margin-left: 10px; font-size: 12px; color: #64748b;"></span>
            </div>

            <button type="submit" class="btn btn-primary" style="padding: 10px 20px; border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                <i class='bx bx-save'></i> Save All Settings
            </button>
        </form>
    </div>
</div>

<script>
    function getCurrentLocation() {
        const status = document.getElementById('location-status');
        status.textContent = "Getting location...";
        status.style.color = "#3b82f6";
        
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                document.getElementById('office_latitude').value = position.coords.latitude;
                document.getElementById('office_longitude').value = position.coords.longitude;
                status.textContent = "Location updated successfully!";
                status.style.color = "#10b981";
                
                setTimeout(() => { status.textContent = ""; }, 3000);
            }, function(error) {
                status.textContent = "Error getting location: " + error.message;
                status.style.color = "#ef4444";
            });
        } else {
            status.textContent = "Geolocation is not supported by this browser.";
            status.style.color = "#ef4444";
        }
    }
</script>
</div>
@endsection
