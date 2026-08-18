@extends('layouts.app')

@section('content')
<div class="app-header">
    <div class="header-title">
        <h1>Payroll Management</h1>
        <p>Manage employee salaries and generate payslips</p>
    </div>
    <div class="topbar-right">
        <form method="GET" action="{{ route('admin.payroll.index') }}" style="display: flex; gap: 10px; align-items: center;">
            <input type="month" name="month" value="{{ \Carbon\Carbon::parse($month)->format('Y-m') }}" class="search-input" onchange="this.form.submit()" style="padding: 10px 15px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-card); cursor: pointer; outline: none; height: auto;">
        </form>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 24px; padding: 15px; background: #ecfdf5; color: #065f46; border-radius: 8px; border: 1px solid #a7f3d0;">
        {{ session('success') }}
    </div>
@endif

<div class="content-panel">
    <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3 class="panel-title">Employee Salary Generation - {{ $month }}</h3>
        <div style="font-size: 13px; color: var(--text-muted); background: var(--bg-body); padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border-color);">
            <strong>Month Details:</strong> Total Days: {{ $totalDays ?? 0 }} | Sundays Excluded: {{ $sundays ?? 0 }}
        </div>
    </div>
    <div style="overflow-x: auto;">
        <table class="table" style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border-color);">
                    <th style="padding: 12px; font-size: 13px; color: var(--text-muted);">Employee</th>
                    <th style="padding: 12px; font-size: 13px; color: var(--text-muted);">Base Salary</th>
                    <th style="padding: 12px; font-size: 13px; color: var(--text-muted);">Total Days</th>
                    <th style="padding: 12px; font-size: 13px; color: var(--text-muted);">Sundays</th>
                    <th style="padding: 12px; font-size: 13px; color: var(--text-muted);">Working Days</th>
                    <th style="padding: 12px; font-size: 13px; color: var(--text-muted);">Present Days</th>
                    <th style="padding: 12px; font-size: 13px; color: var(--text-muted);">Net Salary</th>
                    <th style="padding: 12px; font-size: 13px; color: var(--text-muted);">Status</th>
                    <th style="padding: 12px; font-size: 13px; color: var(--text-muted);">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $employee)
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: 15px 12px;">
                            <div style="font-weight: 600; color: var(--text-main);">{{ $employee->name }}</div>
                            <div style="font-size: 11px; color: var(--text-muted);">{{ $employee->email }}</div>
                        </td>
                        <td style="padding: 15px 12px; font-weight: 500;">₹{{ number_format($employee->salary, 2) }}</td>
                        <td style="padding: 15px 12px;">{{ $totalDays ?? 0 }}</td>
                        <td style="padding: 15px 12px; color: var(--danger);">{{ $sundays ?? 0 }}</td>
                        <td style="padding: 15px 12px; font-weight: 600;">{{ $employee->working_days }}</td>
                        <td style="padding: 15px 12px; color: var(--success); font-weight: 600;">{{ $employee->present_days }}</td>
                        <td style="padding: 15px 12px; font-weight: 700; color: var(--primary);">₹{{ number_format($employee->calculated_salary, 2) }}</td>
                        <td style="padding: 15px 12px;">
                            @if($employee->payroll)
                                <span class="badge badge-success">Generated</span>
                            @else
                                <span class="badge badge-warning">Pending</span>
                            @endif
                        </td>
                        <td style="padding: 15px 12px;">
                            @if($employee->payroll)
                                <a href="{{ route('admin.payroll.show', $employee->payroll->id) }}" class="btn btn-outline" style="padding: 5px 10px; font-size: 12px;">View Slip</a>
                            @else
                                <form action="{{ route('admin.payroll.store') }}" method="POST" style="margin: 0; display: flex; flex-direction: column; gap: 8px;">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{ $employee->id }}">
                                    <input type="hidden" name="month" value="{{ $month }}">
                                    <input type="hidden" name="salary" value="{{ $employee->salary }}">
                                    <input type="hidden" name="net_salary" value="{{ $employee->calculated_salary }}">
                                    <div style="display: flex; gap: 5px;">
                                        <input type="number" name="bonus" placeholder="Bonus ₹" style="width: 75px; padding: 4px; font-size: 11px; border: 1px solid var(--border-color); border-radius: 4px;">
                                        <input type="number" name="deductions" placeholder="Deduct ₹" style="width: 75px; padding: 4px; font-size: 11px; border: 1px solid var(--border-color); border-radius: 4px;">
                                    </div>
                                    <button type="submit" class="btn btn-primary" style="padding: 5px 10px; font-size: 12px; width: 100%;" onclick="return confirm('Generate Payslip for {{ $employee->name }} with Base Net Salary ₹{{ $employee->calculated_salary }} (plus any entered bonus/deductions)?\n\nThis is based on {{ $employee->present_days }} present days out of {{ $employee->working_days }} working days (Sundays excluded).\n\nAn email notification will be sent to the employee.')">Generate Slip</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="padding: 20px; text-align: center; color: var(--text-muted);">No employees found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
