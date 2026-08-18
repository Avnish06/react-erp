@extends('layouts.app')

@section('content')
<div class="content-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <div>
        <h1 style="font-size: 24px; font-weight: 700; color: var(--text-main); margin: 0;">Employee Queries</h1>
        <p style="color: var(--text-muted); margin: 5px 0 0 0;">Manage queries raised by employees in your workspace</p>
    </div>
</div>

@if(session('success'))
    <div style="background: rgba(46, 213, 115, 0.1); border: 1px solid var(--success); color: var(--success); padding: 15px; border-radius: 12px; margin-bottom: 20px;">
        {{ session('success') }}
    </div>
@endif

<div class="card" style="margin-bottom: 25px;">
    <div class="card-header">
        <h2 style="font-size: 16px; font-weight: 600; margin: 0;">Query List</h2>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border-color); background: rgba(0,0,0,0.2);">
                        <th style="padding: 15px; text-align: left; font-size: 13px; color: var(--text-muted); font-weight: 600;">Query Details</th>
                        <th style="padding: 15px; text-align: left; font-size: 13px; color: var(--text-muted); font-weight: 600;">Raised By</th>
                        <th style="padding: 15px; text-align: left; font-size: 13px; color: var(--text-muted); font-weight: 600;">Status</th>
                        <th style="padding: 15px; text-align: left; font-size: 13px; color: var(--text-muted); font-weight: 600;">Assignee</th>
                        <th style="padding: 15px; text-align: right; font-size: 13px; color: var(--text-muted); font-weight: 600;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($queries as $query)
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 15px;">
                                <div style="font-weight: 500; color: var(--text-main);">{{ $query->subject }}</div>
                                <div style="font-size: 12px; color: var(--text-muted); margin-top: 5px;">{{ Str::limit($query->description, 60) }}</div>
                            </td>
                            <td style="padding: 15px;">
                                <div style="font-weight: 500; color: var(--text-main);">{{ $query->user->name }}</div>
                                <div style="font-size: 12px; color: var(--text-muted);">{{ $query->created_at->format('d M, Y') }}</div>
                            </td>
                            <td style="padding: 15px;">
                                @if($query->status == 'pending')
                                    <span style="background: rgba(255, 165, 2, 0.1); color: #ffa502; border: 1px solid rgba(255, 165, 2, 0.2); padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;">Pending</span>
                                @elseif($query->status == 'in-progress')
                                    <span style="background: rgba(55, 66, 250, 0.1); color: #3742fa; border: 1px solid rgba(55, 66, 250, 0.2); padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;">In Progress</span>
                                @else
                                    <span style="background: rgba(46, 213, 115, 0.1); color: #2ed573; border: 1px solid rgba(46, 213, 115, 0.2); padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;">Resolved</span>
                                @endif
                            </td>
                            <td style="padding: 15px;">
                                @if($query->assignee)
                                    <div style="font-weight: 500; color: var(--text-main);">{{ $query->assignee->name }}</div>
                                @else
                                    <span style="color: var(--text-muted); font-size: 12px;">Unassigned</span>
                                @endif
                            </td>
                            <td style="padding: 15px; text-align: right;">
                                <button class="btn btn-sm btn-outline" onclick="openAssignModal({{ $query->id }})" style="margin-bottom: 5px;">Assign</button>
                                
                                <form action="{{ route('admin.queries.status', $query->id) }}" method="POST" style="display: inline-block;">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" onchange="this.form.submit()" class="search-input" style="padding: 4px 8px; border-radius: 4px; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-main); font-size: 12px; cursor: pointer;">
                                        <option value="pending" {{ $query->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="in-progress" {{ $query->status == 'in-progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="resolved" {{ $query->status == 'resolved' ? 'selected' : '' }}>Resolved</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding: 30px; text-align: center; color: var(--text-muted);">
                                <i class='bx bx-question-mark' style="font-size: 48px; margin-bottom: 10px; opacity: 0.5;"></i>
                                <p>No queries found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Assign Modal -->
<div id="assign-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div class="card" style="width: 100%; max-width: 400px; margin: 20px; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1); animation: slideUp 0.3s ease-out;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 25px 25px 15px 25px; border-bottom: 1px solid rgba(0,0,0,0.05);">
            <h2 style="font-size: 18px; font-weight: 600; margin: 0; color: #333;">Assign Query</h2>
            <button type="button" onclick="document.getElementById('assign-modal').style.display='none'" style="background: none; border: none; color: #888; cursor: pointer; font-size: 24px; padding: 0; display: flex; align-items: center; justify-content: center;">
                <i class='bx bx-x'></i>
            </button>
        </div>
        <form id="assign-form" method="POST" style="padding: 25px;">
            @csrf
            @method('PATCH')
            
            <div class="form-group" style="margin-bottom: 25px;">
                <label style="display: block; margin-bottom: 5px; font-size: 13px; font-weight: 600; color: #555;">Select Employee</label>
                <select name="assigned_to" required class="search-input" style="width: 100%; padding: 10px 15px; border-radius: 8px; border: 1px solid #ddd; background: #fff; color: #333;">
                    <option value="">Select an employee...</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                    @endforeach
                </select>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('assign-modal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary">Assign</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAssignModal(queryId) {
        document.getElementById('assign-form').action = `/admin/queries/${queryId}/assign`;
        document.getElementById('assign-modal').style.display = 'flex';
    }
</script>
@endsection
