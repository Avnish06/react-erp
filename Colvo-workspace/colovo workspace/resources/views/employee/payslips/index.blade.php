@extends('layouts.app')

@section('content')
<div class="app-header">
    <div class="header-title">
        <h1>My Payslips</h1>
        <p>View your salary details and generated payslips</p>
    </div>
</div>

<div class="section-grid" style="grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
    @forelse($payslips as $slip)
        <div class="content-panel" style="padding: 20px; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <div style="font-size: 18px; font-weight: 700; color: var(--text-main);">{{ $slip->month }}</div>
                    <span class="badge badge-success" style="text-transform: uppercase;">{{ $slip->status }}</span>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <div style="font-size: 13px; color: var(--text-muted); margin-bottom: 5px;">Net Salary Received</div>
                    <div style="font-size: 28px; font-weight: 800; color: var(--primary);">₹{{ number_format($slip->net_salary, 2) }}</div>
                </div>
            </div>
            
            <div style="border-top: 1px solid var(--border-color); padding-top: 15px; display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 12px; color: var(--text-muted);">Generated: {{ $slip->created_at->format('M d, Y') }}</span>
                <a href="{{ route('employee.payslips.show', $slip->id) }}" class="btn btn-outline" style="padding: 5px 15px; font-size: 13px;">View Payslip</a>
            </div>
        </div>
    @empty
        <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--text-muted); background: rgba(0,0,0,0.02); border-radius: 12px;">
            <i class='bx bx-receipt' style="font-size: 40px; color: var(--border-color); margin-bottom: 10px;"></i>
            <h3>No Payslips Found</h3>
            <p>You don't have any generated payslips yet.</p>
        </div>
    @endforelse
</div>
@endsection
