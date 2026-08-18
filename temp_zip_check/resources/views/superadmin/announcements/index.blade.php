@extends('layouts.app')

@section('content')
<div class="content-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <div>
        <h1 style="font-size: 24px; font-weight: 700; color: var(--text-main); margin: 0;">Announcements</h1>
        <p style="color: var(--text-muted); margin: 5px 0 0 0;">Manage announcements across all workspaces</p>
    </div>
    <button class="btn btn-primary" onclick="document.getElementById('add-announcement-modal').style.display='flex'">
        <i class='bx bx-plus'></i> New Announcement
    </button>
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

<!-- Add Announcement Modal -->
<div id="add-announcement-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div class="card" style="width: 100%; max-width: 500px; margin: 20px; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1); animation: slideUp 0.3s ease-out;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 25px 25px 15px 25px; border-bottom: 1px solid rgba(0,0,0,0.05);">
            <h2 style="font-size: 18px; font-weight: 600; margin: 0; color: #333;">Create Announcement</h2>
            <button type="button" onclick="document.getElementById('add-announcement-modal').style.display='none'" style="background: none; border: none; color: #888; cursor: pointer; font-size: 24px; padding: 0; display: flex; align-items: center; justify-content: center;">
                <i class='bx bx-x'></i>
            </button>
        </div>
        <form action="{{ route('superadmin.announcements.store') }}" method="POST" style="padding: 25px;">
            @csrf
            
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-size: 13px; font-weight: 600; color: #555;">Target Company</label>
                <x-company-select :companies="$companies" id="announcementCompanySelect" name="company_id" placeholder="Select a company..." padding="10px 15px" />
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-size: 13px; font-weight: 600; color: #555;">Title</label>
                <input type="text" name="title" required class="search-input" style="width: 100%; padding: 10px 15px; border-radius: 8px; border: 1px solid #ddd; background: #fff; color: #333;">
            </div>
            
            <div class="form-group" style="margin-bottom: 25px;">
                <label style="display: block; margin-bottom: 5px; font-size: 13px; font-weight: 600; color: #555;">Description</label>
                <textarea name="description" required rows="4" class="search-input" style="width: 100%; padding: 10px 15px; border-radius: 8px; border: 1px solid #ddd; background: #fff; color: #333; resize: vertical;"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('add-announcement-modal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Announcement</button>
            </div>
        </form>
    </div>
</div>
@endsection
