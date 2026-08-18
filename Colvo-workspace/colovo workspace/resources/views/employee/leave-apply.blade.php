@extends('layouts.app')

@section('content')
<div class="app-header">
    <div class="header-title">
        <h1>Apply for Leave</h1>
        <p>Submit a new leave request for approval.</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">
        <i class='bx bx-check-circle'></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger">
        <i class='bx bx-error-circle'></i>
        <span>{{ session('error') }}</span>
    </div>
@endif

<style>
    .leave-layout {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
        margin-top: 20px;
    }
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
        margin-top: 20px;
    }
    @media (max-width: 1100px) {
        .leave-layout {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 480px) {
        .form-actions {
            flex-direction: column;
        }
    }
</style>

<div class="leave-layout">
    <!-- Left Column: Form -->
    <div class="content-panel">
        <div class="panel-header">
            <h3 class="panel-title">Leave Request Form</h3>
        </div>
        
        <form action="{{ route('employee.leave.request') }}" method="POST" style="padding: 20px;">
            @csrf
            <div class="form-group">
                <label class="form-label">Leave Type</label>
                <select name="type" class="form-input" required>
                    <option value="casual">Casual Leave</option>
                    <option value="sick">Sick Leave</option>
                    <option value="annual">Annual Leave</option>
                    <option value="unpaid">Unpaid Leave</option>
                </select>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">End Date (Optional)</label>
                    <input type="date" name="end_date" class="form-input">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Reason (Optional)</label>
                <textarea name="reason" class="form-input" rows="4" placeholder="Briefly describe the reason for your leave..."></textarea>
            </div>
            <div class="form-actions">
                <a href="{{ route('employee.dashboard') }}" class="btn btn-outline" style="text-decoration: none; flex: 1; text-align: center; justify-content: center;">Cancel</a>
                <button type="submit" class="btn btn-primary" style="padding: 10px 25px; flex: 1; text-align: center; justify-content: center; white-space: nowrap;">Submit Request</button>
            </div>
        </form>
    </div>

    <!-- Right Column: History -->
    <div class="content-panel">
        <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 class="panel-title">Your Leave History</h3>
            <div style="font-size: 13px; color: var(--text-muted);">Recent Requests</div>
        </div>
        <div style="padding: 20px;">
            @if($leaves->isEmpty())
                <div style="text-align: center; padding: 40px 20px; color: var(--text-muted);">
                    <i class='bx bx-history' style="font-size: 48px; color: var(--border-color); margin-bottom: 10px;"></i>
                    <p>No leave requests found.</p>
                </div>
            @else
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    @foreach($leaves as $leave)
                    <div style="background: var(--bg-main); border: 1px solid var(--border-color); border-radius: 12px; padding: 15px; display: flex; justify-content: space-between; align-items: center; transition: all 0.3s ease;">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <div style="width: 45px; height: 45px; border-radius: 10px; background: rgba(14, 165, 233, 0.1); color: var(--info); display: flex; align-items: center; justify-content: center; font-size: 20px;">
                                @if($leave->type === 'sick') <i class='bx bx-plus-medical'></i>
                                @elseif($leave->type === 'casual') <i class='bx bx-calendar-event'></i>
                                @elseif($leave->type === 'annual') <i class='bx bx-sun'></i>
                                @else <i class='bx bx-calendar-minus'></i> @endif
                            </div>
                            <div>
                                <h4 style="margin: 0; font-size: 14px; color: var(--text-main); text-transform: capitalize;">{{ $leave->type }} Leave</h4>
                                <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
                                    {{ \Carbon\Carbon::parse($leave->start_date)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($leave->end_date)->format('M d, Y') }}
                                </div>
                            </div>
                        </div>
                        <div>
                            @if($leave->status === 'approved')
                                <span class="badge" style="background: rgba(16, 185, 129, 0.1); color: var(--success); padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;"><i class='bx bx-check'></i> Approved</span>
                            @elseif($leave->status === 'rejected')
                                <span class="badge" style="background: rgba(239, 68, 68, 0.1); color: var(--danger); padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;"><i class='bx bx-x'></i> Rejected</span>
                            @else
                                <span class="badge" style="background: rgba(245, 158, 11, 0.1); color: var(--warning); padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;"><i class='bx bx-time-five'></i> Pending</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
