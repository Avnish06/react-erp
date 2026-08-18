@extends('layouts.app')

@section('content')
<div class="app-header">
    <div class="header-title">
        <h1>Company Schedule Management</h1>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 24px; padding: 15px; background: #ecfdf5; color: #065f46; border-radius: 8px; border: 1px solid #a7f3d0;">
        <i class='bx bx-check-circle'></i> {{ session('success') }}
    </div>
@endif
@if ($errors->any())
    <div class="alert alert-danger" style="margin-bottom: 24px; padding: 15px; background: #fef2f2; color: #991b1b; border-radius: 8px; border: 1px solid #fecaca;">
        <ul style="margin-bottom: 0; padding-left: 20px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="section-grid" style="grid-template-columns: 1fr 2fr;">
    <!-- Add New Schedule Form -->
    <div class="content-panel">
        <div class="panel-header">
            <h3 class="panel-title">Add Schedule Item</h3>
        </div>
        
        <form action="{{ route('admin.schedule.store') }}" method="POST">
            @csrf
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-size: 13px; font-weight: 600;">Time</label>
                <input type="text" name="time_string" placeholder="e.g. 10:00 AM" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px;">
            </div>
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-size: 13px; font-weight: 600;">Title</label>
                <input type="text" name="title" placeholder="e.g. Daily Standup" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-size: 13px; font-weight: 600;">Subtitle / Location</label>
                <input type="text" name="subtitle" placeholder="e.g. Google Meet • Team Alpha" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 5px; font-size: 13px; font-weight: 600;">Dot Color</label>
                <select name="color" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; background: white;">
                    <option value="purple">Purple</option>
                    <option value="blue">Blue</option>
                    <option value="orange">Orange</option>
                    <option value="green">Green</option>
                    <option value="red">Red</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                <i class='bx bx-plus'></i> Add to Schedule
            </button>
        </form>
    </div>

    <!-- Current Schedule List -->
    <div class="content-panel">
        <div class="panel-header">
            <h3 class="panel-title">Current Schedule</h3>
        </div>

        <div style="display: flex; flex-direction: column; gap: 15px;">
            @forelse($schedules as $schedule)
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px; border: 1px solid var(--border-color); border-radius: 8px;">
                    <div style="display: flex; align-items: flex-start; gap: 15px;">
                        <div style="width: 12px; height: 12px; border-radius: 50%; margin-top: 5px; 
                            background-color: 
                                {{ $schedule->color == 'purple' ? '#8b5cf6' : 
                                  ($schedule->color == 'blue' ? '#3b82f6' : 
                                  ($schedule->color == 'orange' ? '#f59e0b' : 
                                  ($schedule->color == 'green' ? '#10b981' : '#ef4444'))) }};">
                        </div>
                        <div>
                            <h4 style="margin: 0 0 5px 0; font-size: 15px; color: var(--text-main);">{{ $schedule->time_string }} – {{ $schedule->title }}</h4>
                            <span style="color: var(--text-muted); font-size: 13px;">{{ $schedule->subtitle ?? 'No subtitle' }}</span>
                        </div>
                    </div>
                    
                    <form action="{{ route('admin.schedule.destroy', $schedule->id) }}" method="POST" onsubmit="return confirm('Delete this schedule item?');" style="margin:0;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline" style="padding: 5px 10px; color: #dc2626; border-color: #fecaca; background: #fef2f2;">
                            <i class='bx bx-trash'></i>
                        </button>
                    </form>
                </div>
            @empty
                <div style="text-align: center; padding: 30px; color: var(--text-muted);">
                    <p>No schedule items have been added yet.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
