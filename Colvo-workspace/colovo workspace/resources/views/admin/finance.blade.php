@extends('layouts.app')

@section('content')
<div class="app-header">
    <div class="header-title">
        <h1>Financial Logs</h1>
    </div>
    
    <div class="topbar-right">
        <div class="noti-bell">
            <i class='bx bx-bell'></i>
            <span class="noti-bell-badge">1</span>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <div class="user-avatar-placeholder" style="width: 38px; height: 38px; font-size: 14px; ">
                {{ substr(auth()->user()->name, 0, 1) }}
            </div>
            <div style="font-size: 13px; font-weight: 600; color: var(--text-main); text-align: left;">
                <div>{{ auth()->user()->name }}</div>
                <div style="font-size: 10px; color: var(--text-muted); font-weight: normal;">{{ auth()->user()->position ?? auth()->user()->role }}</div>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 24px;">
        <i class='bx bx-check-circle'></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

<div class="section-grid">
    <!-- Left: Historical Financial Records Table -->
    <div class="content-panel">
        <div class="panel-header">
            <h3 class="panel-title"><i class='bx bx-coin-stack' style="color: var(--primary);"></i> Financial Logs</h3>
        </div>
        
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Year</th>
                        <th>Total Earnings</th>
                        <th>Total Expenditure</th>
                        <th>Net Balance</th>
                        <th>Summary / Allocations</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($financialRecords as $f)
                        @php
                            $balance = $f->earnings - $f->expenditures;
                        @endphp
                        <tr>
                            <td style="font-weight: 700; font-size: 16px;">{{ $f->year }}</td>
                            <td style="color: var(--success); font-weight: 600;">₹{{ number_format($f->earnings, 2) }}</td>
                            <td style="color: var(--secondary); font-weight: 600;">₹{{ number_format($f->expenditures, 2) }}</td>
                            <td style="font-weight: 700; color: {{ $balance >= 0 ? 'var(--success)' : 'var(--danger)' }}">
                                ₹{{ number_format($balance, 2) }}
                            </td>
                            <td style="font-size: 13px; color: var(--text-muted); max-width: 250px;">{{ $f->summary }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 40px 0;">No financial records logged.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Right: Add / Update Financial Record Form -->
    <div class="content-panel" style="height: fit-content;">
        <div class="panel-header">
            <h3 class="panel-title"><i class='bx bx-plus-circle' style="color: var(--secondary);"></i> Log Year Record</h3>
        </div>

        <form action="{{ route('admin.finance.store') }}" method="POST">
            @csrf

            <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                <div class="form-group" style="flex: 1; margin-bottom: 0;">
                    <label for="year" class="form-label">Fiscal Year</label>
                    <input type="number" name="year" id="year" class="form-input" min="2000" max="2050" placeholder="e.g. 2026" value="{{ date('Y') }}" required>
                </div>

                <div class="form-group" style="flex: 1; margin-bottom: 0;">
                    <label for="earnings" class="form-label">Total Earning (Yearly)</label>
                    <input type="number" name="earnings" id="earnings" class="form-input" min="0" step="0.01" placeholder="e.g. 1500000.00" required>
                </div>
            </div>

            <div class="form-group">
                <label for="expenditures" class="form-label">Total Expenditure (Yearly)</label>
                <input type="number" name="expenditures" id="expenditures" class="form-input" min="0" step="0.01" placeholder="e.g. 950000.00" required>
            </div>

            <div class="form-group">
                <label for="summary" class="form-label">Annual Summary / Department Notes</label>
                <textarea name="summary" id="summary" class="form-input" placeholder="e.g. Outlined department expansion and cloud server provisioning fees..." rows="4"></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-block" style="margin-top: 10px;">Save Record</button>
        </form>
    </div>
</div>
@endsection

