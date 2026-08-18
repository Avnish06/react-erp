@extends('layouts.app')

@section('content')
<div class="content-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <div>
        <h1 style="font-size: 24px; font-weight: 700; color: var(--text-main); margin: 0;">Announcements</h1>
        <p style="color: var(--text-muted); margin: 5px 0 0 0;">Manage announcements across all workspaces</p>
    </div>

</div>

@if(session('success'))
    <div style="background: rgba(46, 213, 115, 0.1); border: 1px solid var(--success); color: var(--success); padding: 15px; border-radius: 12px; margin-bottom: 20px;">
        {{ session('success') }}
    </div>
@endif

<div class="card" style="margin-bottom: 25px;">
    <div class="card-header">
        <h2 style="font-size: 16px; font-weight: 600; margin: 0;">All Announcements</h2>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border-color); background: rgba(0,0,0,0.2);">
                        <th style="padding: 15px; text-align: left; font-size: 13px; color: var(--text-muted); font-weight: 600;">Title</th>
                        <th style="padding: 15px; text-align: left; font-size: 13px; color: var(--text-muted); font-weight: 600;">Company</th>
                        <th style="padding: 15px; text-align: left; font-size: 13px; color: var(--text-muted); font-weight: 600;">Date</th>
                        <th style="padding: 15px; text-align: right; font-size: 13px; color: var(--text-muted); font-weight: 600;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($announcements as $announcement)
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 15px;">
                                <div style="font-weight: 500; color: var(--text-main);">{{ $announcement->title }}</div>
                                <div style="font-size: 12px; color: var(--text-muted); margin-top: 5px;">{{ Str::limit($announcement->description, 50) }}</div>
                            </td>
                            <td style="padding: 15px;">
                                <span style="background: rgba(255, 255, 255, 0.1); padding: 4px 10px; border-radius: 20px; font-size: 12px;">{{ $announcement->company->name }}</span>
                            </td>
                            <td style="padding: 15px; color: var(--text-muted); font-size: 14px;">
                                {{ $announcement->created_at->format('d M, Y h:i A') }}
                            </td>
                            <td style="padding: 15px; text-align: right;">
                                <form action="{{ route('superadmin.announcements.destroy', $announcement->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this announcement?');" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline" style="color: var(--danger); border-color: var(--danger);">
                                        <i class='bx bx-trash'></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="padding: 30px; text-align: center; color: var(--text-muted);">
                                <i class='bx bx-broadcast' style="font-size: 48px; margin-bottom: 10px; opacity: 0.5;"></i>
                                <p>No announcements found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
