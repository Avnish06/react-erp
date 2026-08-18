@extends('layouts.app')

@section('content')
<div class="app-header">
    <div class="header-title">
        <h1>Leave Management</h1>
        <p>Review and approve or reject employee leave requests.</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">
        <i class='bx bx-check-circle'></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

<div class="content-panel">
    <div class="panel-header">
        <h3 class="panel-title">All Leave Requests</h3>
    </div>
    <div class="table-responsive">
        <table class="custom-table" id="leavesTable">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Leave Type</th>
                    <th>Duration</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($leaves as $leave)
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div class="user-avatar-placeholder" style="width: 32px; height: 32px; font-size: 12px; border-radius: 50%;">
                                {{ substr($leave->user->name, 0, 1) }}
                            </div>
                            <div>
                                <div style="font-weight: 600; color: var(--text-main);">{{ $leave->user->name }}</div>
                                <div style="font-size: 11px; color: var(--text-muted);">{{ $leave->user->role }}</div>
                            </div>
                        </div>
                    </td>
                    <td><span style="font-weight: 500;">{{ ucfirst($leave->type) }} Leave</span></td>
                    <td>
                        <div style="color: var(--text-main); font-size: 13px;">
                            {{ \Carbon\Carbon::parse($leave->start_date)->format('M d, Y') }}
                            @if($leave->end_date && $leave->end_date != $leave->start_date)
                                - {{ \Carbon\Carbon::parse($leave->end_date)->format('M d, Y') }}
                            @endif
                        </div>
                        @php
                            $days = \Carbon\Carbon::parse($leave->start_date)->diffInDays(\Carbon\Carbon::parse($leave->end_date)) + 1;
                        @endphp
                        <div style="font-size: 11px; color: var(--text-muted);">{{ $days }} Day(s)</div>
                    </td>
                    <td style="max-width: 200px;">
                        <span style="font-size: 12px; color: var(--text-muted);">{{ $leave->reason ?: 'No reason provided' }}</span>
                    </td>
                    <td>
                        <span class="badge badge-{{ $leave->status == 'approved' ? 'success' : ($leave->status == 'rejected' ? 'danger' : 'warning') }}">
                            {{ ucfirst($leave->status) }}
                        </span>
                    </td>
                    <td>
                        @if($leave->status == 'pending')
                            <div style="display: flex; gap: 5px;">
                                <form action="{{ route('admin.leaves.approve', $leave->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-sm" style="background: var(--success); border-color: var(--success); padding: 5px 10px; font-size: 12px;">Approve</button>
                                </form>
                                <form action="{{ route('admin.leaves.reject', $leave->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-outline btn-sm" style="color: var(--danger); border-color: var(--danger); padding: 5px 10px; font-size: 12px;">Reject</button>
                                </form>
                            </div>
                        @else
                            <span style="font-size: 12px; color: var(--text-muted);">Action taken</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<link href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css" rel="stylesheet">
<style>
    .dataTables_wrapper .dataTables_length, .dataTables_wrapper .dataTables_filter, .dataTables_wrapper .dataTables_info, .dataTables_wrapper .dataTables_paginate {
        color: var(--text-muted);
        font-size: 13px;
        margin-bottom: 15px;
    }
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid var(--border-color);
        background: var(--bg-card);
        color: var(--text-main);
        border-radius: 6px;
        padding: 4px 10px;
        outline: none;
    }
</style>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        $('#leavesTable').DataTable({
            "pageLength": 10,
            "ordering": false,
            "language": {
                "search": "",
                "searchPlaceholder": "Search leaves..."
            }
        });
    });
</script>
@endpush
