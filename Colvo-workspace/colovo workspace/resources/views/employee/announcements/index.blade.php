@extends('layouts.app')

@section('content')
<div class="content-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <div>
        <h1 style="font-size: 24px; font-weight: 700; color: var(--text-main); margin: 0;">Announcements</h1>
        <p style="color: var(--text-muted); margin: 5px 0 0 0;">Important updates and announcements</p>
    </div>
</div>

<div class="card" style="margin-bottom: 25px;">
    <div class="card-header">
        <h2 style="font-size: 16px; font-weight: 600; margin: 0;">Workspace Announcements</h2>
    </div>
    <div class="card-body">
        @forelse($announcements as $announcement)
            <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--border-color); border-radius: 12px; padding: 20px; margin-bottom: 15px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                    <h3 style="margin: 0; font-size: 16px; font-weight: 600; color: var(--primary);">{{ $announcement->title }}</h3>
                    <span style="font-size: 12px; color: var(--text-muted); background: rgba(0,0,0,0.2); padding: 4px 10px; border-radius: 20px;">
                        <i class='bx bx-time'></i> {{ $announcement->created_at->format('d M, Y') }}
                    </span>
                </div>
                <div style="color: var(--text-main); line-height: 1.6; font-size: 14px;">
                    {!! nl2br(e($announcement->description)) !!}
                </div>
            </div>
        @empty
            <div style="padding: 40px; text-align: center; color: var(--text-muted);">
                <i class='bx bx-broadcast' style="font-size: 48px; margin-bottom: 10px; opacity: 0.5;"></i>
                <p>No announcements found for your workspace.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
