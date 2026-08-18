@extends('layouts.app')

@section('content')
<div class="content-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <div>
        <h1 style="font-size: 24px; font-weight: 700; color: var(--text-main); margin: 0;">Raise Query</h1>
        <p style="color: var(--text-muted); margin: 5px 0 0 0;">Raise a query to the HR/Admin team or manage assigned queries</p>
    </div>
    <button class="btn btn-primary" onclick="document.getElementById('add-query-modal').style.display='flex'">
        <i class='bx bx-plus'></i> New Query
    </button>
</div>

@if(session('success'))
    <div style="background: rgba(46, 213, 115, 0.1); border: 1px solid var(--success); color: var(--success); padding: 15px; border-radius: 12px; margin-bottom: 20px;">
        {{ session('success') }}
    </div>
@endif

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px;">
    <!-- My Queries -->
    <div class="card">
        <div class="card-header">
            <h2 style="font-size: 16px; font-weight: 600; margin: 0;">My Raised Queries</h2>
        </div>
        <div class="card-body">
            @forelse($myQueries as $query)
                <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--border-color); border-radius: 12px; padding: 20px; margin-bottom: 15px;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                        <h3 style="margin: 0; font-size: 15px; font-weight: 600; color: var(--text-main);">{{ $query->subject }}</h3>
                        
                        @if($query->status == 'pending')
                            <span style="background: rgba(255, 165, 2, 0.1); color: #ffa502; border: 1px solid rgba(255, 165, 2, 0.2); padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;">Pending</span>
                        @elseif($query->status == 'in-progress')
                            <span style="background: rgba(55, 66, 250, 0.1); color: #3742fa; border: 1px solid rgba(55, 66, 250, 0.2); padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;">In Progress</span>
                        @else
                            <span style="background: rgba(46, 213, 115, 0.1); color: #2ed573; border: 1px solid rgba(46, 213, 115, 0.2); padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;">Resolved</span>
                        @endif
                    </div>
                    <div style="color: var(--text-muted); font-size: 13px; margin-bottom: 10px;">
                        {{ Str::limit($query->description, 100) }}
                    </div>
                    <div style="font-size: 11px; color: var(--text-muted);">
                        Raised on {{ $query->created_at->format('d M, Y') }}
                    </div>
                </div>
            @empty
                <div style="padding: 30px; text-align: center; color: var(--text-muted);">
                    <i class='bx bx-paper-plane' style="font-size: 32px; margin-bottom: 10px; opacity: 0.5;"></i>
                    <p>You haven't raised any queries yet.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Assigned Queries -->
    <div class="card">
        <div class="card-header">
            <h2 style="font-size: 16px; font-weight: 600; margin: 0;">Queries Assigned to Me</h2>
        </div>
        <div class="card-body">
            @forelse($assignedQueries as $query)
                <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--border-color); border-radius: 12px; padding: 20px; margin-bottom: 15px;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                        <h3 style="margin: 0; font-size: 15px; font-weight: 600; color: var(--text-main);">{{ $query->subject }}</h3>
                        
                        @if($query->status == 'pending')
                            <span style="background: rgba(255, 165, 2, 0.1); color: #ffa502; border: 1px solid rgba(255, 165, 2, 0.2); padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;">Pending</span>
                        @elseif($query->status == 'in-progress')
                            <span style="background: rgba(55, 66, 250, 0.1); color: #3742fa; border: 1px solid rgba(55, 66, 250, 0.2); padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;">In Progress</span>
                        @else
                            <span style="background: rgba(46, 213, 115, 0.1); color: #2ed573; border: 1px solid rgba(46, 213, 115, 0.2); padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;">Resolved</span>
                        @endif
                    </div>
                    <div style="color: var(--text-muted); font-size: 13px; margin-bottom: 15px;">
                        <strong style="color: var(--text-main);">Raised by: {{ $query->user->name }}</strong><br>
                        {{ $query->description }}
                    </div>
                    @if($query->status !== 'resolved')
                        <form action="{{ route('employee.queries.resolve', $query->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-outline" style="color: var(--success); border-color: var(--success);">
                                <i class='bx bx-check'></i> Mark as Resolved
                            </button>
                        </form>
                    @endif
                </div>
            @empty
                <div style="padding: 30px; text-align: center; color: var(--text-muted);">
                    <i class='bx bx-check-shield' style="font-size: 32px; margin-bottom: 10px; opacity: 0.5;"></i>
                    <p>No queries have been assigned to you.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Add Query Modal -->
<div id="add-query-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div class="card" style="width: 100%; max-width: 500px; margin: 20px; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1); animation: slideUp 0.3s ease-out;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 25px 25px 15px 25px; border-bottom: 1px solid rgba(0,0,0,0.05);">
            <h2 style="font-size: 18px; font-weight: 600; margin: 0; color: #333;">Raise New Query</h2>
            <button type="button" onclick="document.getElementById('add-query-modal').style.display='none'" style="background: none; border: none; color: #888; cursor: pointer; font-size: 24px; padding: 0; display: flex; align-items: center; justify-content: center;">
                <i class='bx bx-x'></i>
            </button>
        </div>
        <form action="{{ route('employee.queries.store') }}" method="POST" style="padding: 25px;">
            @csrf
            
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-size: 13px; font-weight: 600; color: #555;">Subject</label>
                <input type="text" name="subject" required class="search-input" style="width: 100%; padding: 10px 15px; border-radius: 8px; border: 1px solid #ddd; background: #fff; color: #333;" placeholder="Brief subject of your query">
            </div>
            
            <div class="form-group" style="margin-bottom: 25px;">
                <label style="display: block; margin-bottom: 5px; font-size: 13px; font-weight: 600; color: #555;">Description</label>
                <textarea name="description" required rows="5" class="search-input" style="width: 100%; padding: 10px 15px; border-radius: 8px; border: 1px solid #ddd; background: #fff; color: #333; resize: vertical;" placeholder="Detailed explanation..."></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('add-query-modal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary">Submit Query</button>
            </div>
        </form>
    </div>
</div>
@endsection
