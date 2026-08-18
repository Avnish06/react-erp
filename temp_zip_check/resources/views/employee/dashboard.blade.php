@extends('layouts.app')

@section('content')
<div class="app-header">
    <div class="header-title">
        <h1>My Dashboard</h1>
        <p>Welcome back, here is your daily summary.</p>
    </div>
    
    <div class="topbar-right">
        <div class="noti-bell">
            <i class='bx bx-bell'></i>
            <span class="noti-bell-badge">3</span>
        </div>
        <div class="topbar-user" style="display: flex; align-items: center; gap: 10px;">
            <div class="user-avatar-placeholder" style="width: 40px; height: 40px; font-size: 14px;">
                {{ substr(auth()->user()->name, 0, 1) }}
            </div>
            <div style="font-size: 13px; font-weight: 600; color: var(--text-main); text-align: left;">
                <div>{{ auth()->user()->name }}</div>
                <div style="font-size: 11px; color: var(--text-muted); font-weight: normal;">{{ auth()->user()->position ?? auth()->user()->role }}</div>
            </div>
            <i class='bx bx-chevron-down' style="color: var(--text-muted);"></i>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">
        <i class='bx bx-check-circle'></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger">
        <i class='bx bx-error-circle'></i>
        <span>{{ session('error') }}</span>
    </div>
@endif

@php
    $hour = \Carbon\Carbon::now()->format('H');
    if ($hour < 12) {
        $greeting = 'Good morning';
    } elseif ($hour < 17) {
        $greeting = 'Good afternoon';
    } else {
        $greeting = 'Good evening';
    }
@endphp
<!-- Welcome Banner -->
<div class="welcome-banner" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
    <div>
        <span class="welcome-badge">WELCOME BACK</span>
        <h2 class="welcome-title"><span id="dynamic-greeting">{{ $greeting }}</span>, {{ auth()->user()->name }}! 👋</h2>
        <p class="welcome-quote">Have a great day at work. You have {{ $tasks->where('status', 'pending')->count() }} pending tasks today.</p>
        <div style="display: flex; gap: 15px; margin-top: 15px; font-size: 13px; color: #cbd5e1;">
            <span><i class='bx bx-id-card'></i> EMP-{{ auth()->user()->id ?? '1042' }}</span>
            <span><i class='bx bx-briefcase'></i> {{ auth()->user()->position ?? 'Software Engineer' }}</span>
            <span><i class='bx bx-building'></i> Engineering Dept</span>
        </div>
    </div>
    <div style="text-align: right; display: flex; flex-direction: column; align-items: flex-end;">
        <div id="live-clock-dash" style="font-size: 28px; font-weight: 700; color: #ffffff; font-family: monospace; letter-spacing: 2px;">--:--:--</div>
        <div style="color: #cbd5e1; font-size: 13px;">{{ \Carbon\Carbon::now()->format('l, F j, Y') }}</div>
    </div>
</div>

<!-- Quick Statistics using correct CSS classes -->
<div class="metrics-grid">
    <!-- Total Tasks -->
    <div class="metric-card blue">
        <div class="metric-card-icon"><i class='bx bx-task'></i></div>
        <div class="metric-card-details">
            <div class="metric-card-title">Total Tasks</div>
            <div class="metric-card-value">{{ $tasks->count() }}</div>
            <div style="font-size: 11px; margin-top: 4px; color: var(--success);"><i class='bx bx-up-arrow-alt'></i> 12% from last month</div>
        </div>
    </div>
    
    <!-- Pending Tasks -->
    <div class="metric-card amber">
        <div class="metric-card-icon"><i class='bx bx-time'></i></div>
        <div class="metric-card-details">
            <div class="metric-card-title">Pending Tasks</div>
            <div class="metric-card-value">{{ $tasks->where('status', 'pending')->count() }}</div>
            <div style="width: 100%; height: 4px; background: rgba(0,0,0,0.05); border-radius: 4px; margin-top: 8px;">
                <div style="width: 45%; height: 100%; background: var(--warning); border-radius: 4px;"></div>
            </div>
        </div>
    </div>
    
    <!-- Leave Balance -->
    <div class="metric-card rose">
        <div class="metric-card-icon"><i class='bx bx-calendar-x'></i></div>
        <div class="metric-card-details">
            <div class="metric-card-title">Leave Balance</div>
            <div class="metric-card-value">12 Days</div>
            <div style="font-size: 11px; margin-top: 4px;">4 Casual, 8 Sick</div>
        </div>
    </div>
    
    <!-- Productivity -->
    <div class="metric-card purple">
        <div class="metric-card-icon"><i class='bx bx-trending-up'></i></div>
        <div class="metric-card-details">
            <div class="metric-card-title">Productivity</div>
            <div class="metric-card-value">94%</div>
            <div style="font-size: 11px; margin-top: 4px; color: var(--success);"><i class='bx bx-up-arrow-alt'></i> Top 10% in team</div>
        </div>
    </div>
</div>

<div class="section-grid">
    <!-- Left Column -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        <!-- Quick Actions & Attendance -        <div class="content-panel">
            <div class="panel-header">
                <h3 class="panel-title">Attendance & Quick Actions</h3>
            </div>
            
            <style>
                .attendance-grid {
                    display: grid;
                    grid-template-columns: 1fr;
                    gap: 20px;
                    align-items: center;
                }
                .attendance-left-col {
                    text-align: center;
                    padding-right: 0;
                    border-right: none;
                }
                @media (min-width: 768px) {
                    .attendance-grid {
                        grid-template-columns: 1fr 1fr; /* Or remove 1fr 1fr if the right column is permanently empty, but leaving it as it was originally for desktop */
                    }
                    .attendance-left-col {
                        border-right: 1px solid var(--border-color);
                        padding-right: 20px;
                    }
                }
                @media (min-width: 768px) and (max-width: 1024px) {
                    .attendance-grid {
                        grid-template-columns: 1fr;
                    }
                    .attendance-left-col {
                        border-right: none;
                        padding-right: 0;
                        border-bottom: 1px solid var(--border-color);
                        padding-bottom: 20px;
                    }
                }
            </style>

            <div class="attendance-grid">
                <div class="attendance-left-col">
                    <h4 style="color: var(--primary); font-weight: 700; margin-bottom: 20px; text-align: left;">Today's Status</h4>
                    
                    <div id="attendance-live-clock" style="font-size: 34px; font-weight: 800; color: var(--text-main); font-family: 'Outfit', sans-serif; letter-spacing: 2px; margin-bottom: 5px;">--:--:--</div>
                    <div style="color: var(--text-muted); font-size: 14px; margin-bottom: 25px;">{{ \Carbon\Carbon::now()->format('l, F j, Y') }}</div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                        <div style="border: 1px solid var(--border-color); border-radius: 10px; padding: 15px; background: #ffffff; box-shadow: 0 2px 5px rgba(0,0,0,0.02);">
                            <div style="font-size: 10px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; font-weight: 600;">Clock In</div>
                            <div style="font-weight: 700; font-size: 16px; color: var(--text-main);">{{ $todayAttendance && $todayAttendance->clock_in ? \Carbon\Carbon::parse($todayAttendance->clock_in)->format('g:i A') : '--:--' }}</div>
                        </div>
                        <div style="border: 1px solid var(--border-color); border-radius: 10px; padding: 15px; background: #ffffff; box-shadow: 0 2px 5px rgba(0,0,0,0.02);">
                            <div style="font-size: 10px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; font-weight: 600;">Clock Out</div>
                            <div style="font-weight: 700; font-size: 16px; color: var(--text-main);">{{ $todayAttendance && $todayAttendance->clock_out ? \Carbon\Carbon::parse($todayAttendance->clock_out)->format('g:i A') : '--:--' }}</div>
                        </div>
                    </div>
                    
                    @if(!$todayAttendance)
                        <form action="{{ route('employee.clock-in') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-block" style="padding: 12px; font-size: 14px; border-radius: 10px; font-weight: 600;"><i class='bx bx-fingerprint' style="font-size: 18px;"></i> Clock In Now</button>
                        </form>
                    @elseif($todayAttendance && !$todayAttendance->clock_out)
                        <form action="{{ route('employee.clock-out') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-block" style="background: var(--danger); color: white; padding: 12px; font-size: 14px; border-radius: 10px; font-weight: 600; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;"><i class='bx bx-power-off' style="font-size: 18px;"></i> Clock Out Now</button>
                        </form>
                    @else
                        <button class="btn btn-outline btn-block" disabled style="padding: 12px; font-size: 14px; border-radius: 10px; border-color: var(--success); color: var(--success); background: var(--success-bg); font-weight: 600;"><i class='bx bx-check-shield' style="font-size: 18px;"></i> Day Completed</button>
                    @endif
                </div>
                
            </div>
        </div>iv>

        <!-- My Tasks -->
        <div class="content-panel">
            <div class="panel-header">
                <h3 class="panel-title">My Tasks</h3>
                <a href="{{ route('employee.tasks') }}" class="btn btn-sm btn-outline">View All</a>
            </div>
            
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Task Name</th>
                            <th>Due Date</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tasks->take(4) as $t)
                        <tr>
                            <td>
                                <strong style="color: var(--text-main); font-size: 14px;">{{ $t->title }}</strong>
                                <p style="font-size: 12px; color: var(--text-muted); margin-top: 4px; margin-bottom: 0;">{{ Str::limit($t->description, 30) }}</p>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($t->due_date)->format('M d') }}</td>
                            <td>
                                <span class="badge badge-{{ $t->priority === 'high' ? 'danger' : ($t->priority === 'medium' ? 'warning' : 'info') }}">
                                    {{ ucfirst($t->priority) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge" style="background: var(--bg-main); color: var(--text-main); border: 1px solid var(--border-color);">
                                    {{ ucfirst(str_replace('_', ' ', $t->status)) }}
                                </span>
                            </td>
                            <td>
                                <form action="{{ route('employee.tasks.update', $t->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="form-input btn-sm" onchange="this.form.submit()" style="padding: 4px 8px; width: auto; font-size: 12px;">
                                        <option value="pending" {{ $t->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="in_progress" {{ $t->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="completed" {{ $t->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 30px;">No pending tasks.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        <!-- Today's Schedule -->
        <div class="content-panel">
            <div class="panel-header">
                <h3 class="panel-title">Today's Schedule</h3>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 15px; position: relative; padding-left: 20px; border-left: 2px solid var(--border-color);">
                @forelse($schedules as $schedule)
                    <div style="position: relative;">
                        <div style="position: absolute; left: -26px; top: 0; width: 10px; height: 10px; border-radius: 50%; border: 2px solid var(--bg-card);
                            background: 
                                {{ $schedule->color == 'purple' ? '#8b5cf6' : 
                                  ($schedule->color == 'blue' ? '#3b82f6' : 
                                  ($schedule->color == 'orange' ? '#f59e0b' : 
                                  ($schedule->color == 'green' ? '#10b981' : '#ef4444'))) }};">
                        </div>
                        <div style="font-weight: 600; font-size: 13px; color: var(--text-main);">{{ $schedule->time_string }} - {{ $schedule->title }}</div>
                        @if($schedule->subtitle)
                            <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">{{ $schedule->subtitle }}</div>
                        @endif
                    </div>
                @empty
                    <div style="color: var(--text-muted); font-size: 13px; font-style: italic;">No schedules set for today.</div>
                @endforelse
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="content-panel">
            <div class="panel-header">
                <h3 class="panel-title">Recent Activities</h3>
                <a href="#" style="font-size: 12px; color: var(--primary); text-decoration: none;">View All</a>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <div style="display: flex; gap: 15px; align-items: flex-start;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: var(--success-bg); color: var(--success); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class='bx bx-check'></i>
                    </div>
                    <div>
                        <div style="font-weight: 600; font-size: 13px; color: var(--text-main);">Task Completed</div>
                        <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">You completed "API Integration"</div>
                        <div style="font-size: 10px; color: var(--text-muted); margin-top: 4px;">2 hours ago</div>
                    </div>
                </div>
                
                <div style="display: flex; gap: 15px; align-items: flex-start;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: var(--info-bg); color: var(--info); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class='bx bx-calendar-star'></i>
                    </div>
                    <div>
                        <div style="font-weight: 600; font-size: 13px; color: var(--text-main);">Leave Approved</div>
                        <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">Your leave request for Friday was approved.</div>
                        <div style="font-size: 10px; color: var(--text-muted); margin-top: 4px;">Yesterday</div>
                    </div>
                </div>
                
                <div style="display: flex; gap: 15px; align-items: flex-start;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(79, 70, 229, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class='bx bx-task'></i>
                    </div>
                    <div>
                        <div style="font-weight: 600; font-size: 13px; color: var(--text-main);">New Task Assigned</div>
                        <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">Admin assigned a new task to you.</div>
                        <div style="font-size: 10px; color: var(--text-muted); margin-top: 4px;">Yesterday</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Clock script -->
<script>
    setInterval(() => {
        const d = new Date();
        const timeString = d.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit', second:'2-digit'});
        
        const elDash = document.getElementById('live-clock-dash');
        if(elDash) {
            elDash.innerText = timeString;
        }

        // Dynamic greeting based on local time
        const hour = d.getHours();
        let greeting = 'Good morning';
        if (hour >= 12 && hour < 17) {
            greeting = 'Good afternoon';
        } else if (hour >= 17) {
            greeting = 'Good evening';
        }
        
        const elGreeting = document.getElementById('dynamic-greeting');
        if(elGreeting && elGreeting.innerText !== greeting) {
            elGreeting.innerText = greeting;
        }
        
        const elWidget = document.getElementById('attendance-live-clock');
        if(elWidget) {
            elWidget.innerText = timeString;
        }
    }, 1000);
</script>
@endsection

