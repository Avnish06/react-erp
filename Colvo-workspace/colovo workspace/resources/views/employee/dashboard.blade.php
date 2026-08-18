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
                            @php
                                $clockInDisplay = '--:--';
                                if ($todayAttendance && $todayAttendance->clock_in) {
                                    try {
                                        $t = $todayAttendance->clock_in;
                                        // time column comes as HH:MM:SS string
                                        $clockInDisplay = \Carbon\Carbon::createFromFormat('H:i:s', $t)->format('g:i A');
                                    } catch(\Exception $e) {
                                        $clockInDisplay = substr($todayAttendance->clock_in, 0, 5);
                                    }
                                }
                            @endphp
                            <div style="font-weight: 700; font-size: 16px; color: var(--success, #10b981);">{{ $clockInDisplay }}</div>
                        </div>
                        <div style="border: 1px solid var(--border-color); border-radius: 10px; padding: 15px; background: #ffffff; box-shadow: 0 2px 5px rgba(0,0,0,0.02);">
                            <div style="font-size: 10px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; font-weight: 600;">Clock Out</div>
                            @php
                                $clockOutDisplay = '--:--';
                                if ($todayAttendance && $todayAttendance->clock_out) {
                                    try {
                                        $t = $todayAttendance->clock_out;
                                        $clockOutDisplay = \Carbon\Carbon::createFromFormat('H:i:s', $t)->format('g:i A');
                                    } catch(\Exception $e) {
                                        $clockOutDisplay = substr($todayAttendance->clock_out, 0, 5);
                                    }
                                }
                            @endphp
                            <div style="font-weight: 700; font-size: 16px; color: var(--danger, #ef4444);">{{ $clockOutDisplay }}</div>
                        </div>
                    </div>
                    
                    @if(!$todayAttendance)
                        <button type="button" onclick="openAttendanceModal('clock-in')" class="btn btn-primary btn-block" style="padding: 12px; font-size: 14px; border-radius: 10px; font-weight: 600;">
                            <i class='bx bx-fingerprint' style="font-size: 18px;"></i> Clock In Now
                        </button>
                    @elseif($todayAttendance && !$todayAttendance->clock_out)
                        <button type="button" onclick="openAttendanceModal('clock-out')" class="btn btn-block" style="background: var(--danger); color: white; padding: 12px; font-size: 14px; border-radius: 10px; font-weight: 600; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                            <i class='bx bx-power-off' style="font-size: 18px;"></i> Clock Out Now
                        </button>
                    @else
                        <button class="btn btn-outline btn-block" disabled style="padding: 12px; font-size: 14px; border-radius: 10px; border-color: var(--success); color: var(--success); background: var(--success-bg); font-weight: 600;"><i class='bx bx-check-shield' style="font-size: 18px;"></i> Day Completed</button>
                    @endif
                </div>
                
            </div>
        </div>

    <!-- Face Scan Attendance Modal (Dashboard) -->
    <div id="attendance-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(5px);">
        <div style="background: var(--bg-card); width: 100%; max-width: 420px; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 50px rgba(0,0,0,0.25); margin: 20px;">
            <div style="padding: 18px 22px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: linear-gradient(135deg, var(--primary) 0%, #6366f1 100%);">
                <h3 id="attendance-modal-title" style="margin: 0; font-size: 17px; color: white; font-weight: 700;">Mark Attendance</h3>
                <button type="button" id="attendance-modal-close-btn" onclick="closeAttendanceModal()" style="background: rgba(255,255,255,0.2); border: none; width: 30px; height: 30px; border-radius: 50%; font-size: 18px; color: white; cursor: pointer; display: flex; align-items: center; justify-content: center;">&times;</button>
            </div>
            <div style="padding: 22px;">
                <div style="position: relative; width: 100%; aspect-ratio: 4/3; background: #111; border-radius: 12px; overflow: hidden; margin-bottom: 16px; border: 2px solid var(--border-color);">
                    <video id="webcam-preview" autoplay playsinline style="width: 100%; height: 100%; object-fit: cover;"></video>
                    <canvas id="photo-canvas" style="display: none; width: 100%; height: 100%;"></canvas>
                    <div style="position: absolute; top: 10px; left: 10px; background: rgba(0,0,0,0.5); color: white; font-size: 11px; padding: 4px 10px; border-radius: 20px;">
                        <i class='bx bx-webcam'></i> Live Camera
                    </div>
                </div>

                <div id="geo-status" style="margin-bottom: 12px; font-size: 13px; color: var(--text-muted); display: flex; align-items: center; gap: 8px;">
                    <i class='bx bx-loader-alt bx-spin'></i> Fetching location...
                </div>

                <div id="face-auth-status" style="margin-bottom: 16px; font-size: 13px; color: var(--text-muted); display: flex; align-items: center; gap: 8px;">
                    <i class='bx bx-loader-alt bx-spin'></i> Loading AI Models...
                </div>

                <form id="attendance-form" method="POST" action="">
                    @csrf
                    <input type="hidden" name="latitude" id="latitude">
                    <input type="hidden" name="longitude" id="longitude">
                    <input type="hidden" name="photo" id="photo-data">
                    <input type="hidden" name="type" id="attendance-type">

                    <button type="button" id="capture-btn" class="btn btn-primary btn-block" onclick="captureAndSubmit()" disabled style="padding: 12px; display: none; font-weight: 600; border-radius: 10px;">
                        <i class='bx bx-check-shield'></i> <span id="capture-btn-label">Confirm & Submit</span>
                    </button>
                    <button type="button" id="register-face-btn" class="btn btn-block" onclick="registerFace()" style="padding: 12px; display: none; background: var(--warning); color: white; border: none; border-radius: 10px; font-weight: 600; cursor: pointer;">
                        <i class='bx bx-face'></i> Register My Face First
                    </button>
                </form>
            </div>
        </div>
    </div>

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
@push('scripts')
<script src="{{ asset('js/face-api.min.js') }}"></script>
<script>
    let stream = null;
    let modelsLoaded = false;
    let detectionInterval = null;
    let registeredDescriptor = null;
    let isForcedRegistration = false;

    // ─── Parse stored face descriptor ───────────────────────────────────────
    const rawDescriptor = '{!! addslashes(Auth::user()->face_descriptor) !!}';
    if (rawDescriptor && rawDescriptor !== 'null' && rawDescriptor !== '') {
        try {
            const parsed = JSON.parse(rawDescriptor);
            // Support both plain Array and Object (keyed by index)
            const values = Array.isArray(parsed) ? parsed : Object.values(parsed);
            registeredDescriptor = new Float32Array(values);
            console.log('[FaceAPI] Descriptor loaded, length:', registeredDescriptor.length);
        } catch(e) {
            console.error('[FaceAPI] Failed to parse face descriptor:', e);
        }
    } else {
        isForcedRegistration = true;
        document.addEventListener('DOMContentLoaded', () => {
            openAttendanceModal('register');
        });
    }

    // ─── Load face-api models ─────────────────────────────────────────────────
    async function loadModels() {
        if (modelsLoaded) return true;
        const status = document.getElementById('face-auth-status');
        status.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Loading AI Models...';
        try {
            await Promise.all([
                faceapi.nets.ssdMobilenetv1.loadFromUri('/models'),
                faceapi.nets.faceLandmark68Net.loadFromUri('/models'),
                faceapi.nets.faceRecognitionNet.loadFromUri('/models'),
            ]);
            modelsLoaded = true;
            status.innerHTML = '<i class="bx bx-check-circle" style="color:#10b981"></i> <span style="color:#10b981">AI Models Ready — Scanning face...</span>';
            return true;
        } catch(e) {
            console.error('[FaceAPI] Model load error:', e);
            status.innerHTML = '<i class="bx bx-error" style="color:#ef4444"></i> <span style="color:#ef4444">Error loading AI models: ' + e.message + '</span>';
            return false;
        }
    }

    // ─── Open modal ───────────────────────────────────────────────────────────
    function openAttendanceModal(type) {
        const modal      = document.getElementById('attendance-modal');
        const title      = document.getElementById('attendance-modal-title');
        const form       = document.getElementById('attendance-form');
        const typeInput  = document.getElementById('attendance-type');
        const captureLabel = document.getElementById('capture-btn-label');
        const closeBtn   = document.getElementById('attendance-modal-close-btn');

        modal.style.display = 'flex';
        typeInput.value = type;

        if (type === 'clock-in') {
            title.textContent = '🕐 Clock In — Face Scan';
            form.action = '{{ route("employee.clock-in") }}';
            if (captureLabel) captureLabel.textContent = 'Clock In Now';
            closeBtn.style.display = 'flex';
        } else if (type === 'clock-out') {
            title.textContent = '🕐 Clock Out — Face Scan';
            form.action = '{{ route("employee.clock-out") }}';
            if (captureLabel) captureLabel.textContent = 'Clock Out Now';
            closeBtn.style.display = 'flex';
        } else if (type === 'register') {
            title.textContent = '👤 Face Registration Required';
            closeBtn.style.display = 'none';
        }

        // Reset UI state
        document.getElementById('capture-btn').style.display = 'none';
        document.getElementById('capture-btn').disabled = true;
        document.getElementById('register-face-btn').style.display = 'none';
        document.getElementById('face-auth-status').innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Initializing...';
        document.getElementById('geo-status').innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Fetching location...';

        // ── 1. Geofence check first ──────────────────────────────────────────
        const officeLat = {{ $attendanceSetting->office_latitude ?? 'null' }};
        const officeLon = {{ $attendanceSetting->office_longitude ?? 'null' }};

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const userLat = position.coords.latitude;
                    const userLon = position.coords.longitude;
                    document.getElementById('latitude').value = userLat;
                    document.getElementById('longitude').value = userLon;

                    if (officeLat && officeLon) {
                        const R = 6371e3;
                        const φ1 = userLat * Math.PI / 180;
                        const φ2 = officeLat * Math.PI / 180;
                        const Δφ = (officeLat - userLat) * Math.PI / 180;
                        const Δλ = (officeLon - userLon) * Math.PI / 180;
                        const a = Math.sin(Δφ/2)**2 + Math.cos(φ1)*Math.cos(φ2)*Math.sin(Δλ/2)**2;
                        const distance = R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));

                        if (distance > 50) {
                            document.getElementById('geo-status').innerHTML =
                                '<i class="bx bx-error" style="color:#ef4444"></i> <span style="color:#ef4444">Out of Range: ' + Math.round(distance) + 'm away. Must be within 50m of office.</span>';
                            document.getElementById('face-auth-status').innerHTML =
                                '<span style="color:#ef4444">⛔ Geofence check failed. Clock-in blocked.</span>';
                            return; // block webcam
                        }
                    }

                    document.getElementById('geo-status').innerHTML =
                        '<i class="bx bx-check-circle" style="color:#10b981"></i> <span style="color:#10b981">Location verified ✓</span>';

                    // ── 2. Start webcam only after geo OK ────────────────────
                    startWebcam();
                },
                function(error) {
                    let msg = 'Location error: ' + error.message;
                    if (error.code === 1) msg = 'Location permission denied. Please allow GPS access.';
                    if (error.code === 3) msg = 'Location request timed out.';
                    document.getElementById('geo-status').innerHTML = '<i class="bx bx-error"></i> ' + msg;

                    // If office coords not configured, allow without geo
                    if (!officeLat || !officeLon) {
                        document.getElementById('geo-status').innerHTML = '<i class="bx bx-info-circle" style="color:#f59e0b"></i> <span style="color:#f59e0b">Geofence not configured — proceeding.</span>';
                        startWebcam();
                    }
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        } else {
            document.getElementById('geo-status').innerHTML = '<i class="bx bx-error"></i> Geolocation not supported';
            if (!officeLat || !officeLon) startWebcam(); // fallback
        }
    }

    // ─── Start webcam then load models then detect ────────────────────────────
    async function startWebcam() {
        const video = document.getElementById('webcam-preview');
        const status = document.getElementById('face-auth-status');

        try {
            status.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Accessing camera...';
            stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } });
            video.srcObject = stream;

            // Wait for video metadata so dimensions are set
            await new Promise((resolve) => {
                video.onloadedmetadata = () => {
                    video.width  = video.videoWidth  || 640;
                    video.height = video.videoHeight || 480;
                    resolve();
                };
            });

            // Wait for video to actually start playing
            await video.play();

            // Load models (parallel, cached after first load)
            const ok = await loadModels();
            if (!ok) return;

            // Start detection loop
            detectFaceLoop();

        } catch(err) {
            console.error('[FaceAPI] Webcam error:', err);
            status.innerHTML = '<i class="bx bx-error" style="color:#ef4444"></i> Camera error: ' + err.message;
        }
    }

    // ─── Close modal ──────────────────────────────────────────────────────────
    function closeAttendanceModal() {
        document.getElementById('attendance-modal').style.display = 'none';
        if (stream) { stream.getTracks().forEach(t => t.stop()); stream = null; }
        if (detectionInterval) { clearInterval(detectionInterval); detectionInterval = null; }
    }

    // ─── Detection loop ───────────────────────────────────────────────────────
    function detectFaceLoop() {
        const video    = document.getElementById('webcam-preview');
        const status   = document.getElementById('face-auth-status');
        const clockBtn = document.getElementById('capture-btn');
        const regBtn   = document.getElementById('register-face-btn');

        if (detectionInterval) { clearInterval(detectionInterval); detectionInterval = null; }

        let scanning = false; // prevent overlapping async calls

        detectionInterval = setInterval(async () => {
            if (scanning) return;
            if (!modelsLoaded) return;
            if (video.readyState < 2 || video.paused || video.ended) return;
            if (video.videoWidth === 0) return;

            scanning = true;
            try {
                const options = new faceapi.SsdMobilenetv1Options({ minConfidence: 0.5 });
                const det = await faceapi
                    .detectSingleFace(video, options)
                    .withFaceLandmarks()
                    .withFaceDescriptor();

                if (!det) {
                    status.innerHTML = '<i class="bx bx-scan" style="color:#94a3b8"></i> <span style="color:#94a3b8">Scanning... position your face in the frame.</span>';
                    clockBtn.style.display = 'none';
                    regBtn.style.display   = 'none';
                } else if (!registeredDescriptor) {
                    // Registration mode
                    status.innerHTML = '<i class="bx bx-face" style="color:#f59e0b"></i> <span style="color:#f59e0b">Face detected! Click below to register.</span>';
                    clockBtn.style.display = 'none';
                    regBtn.style.display   = 'block';
                    window.currentDescriptor = det.descriptor;
                    clearInterval(detectionInterval);
                    detectionInterval = null;
                } else {
                    // Verification mode
                    const dist = faceapi.euclideanDistance(det.descriptor, registeredDescriptor);
                    console.debug('[FaceAPI] Distance:', dist.toFixed(4));

                    if (dist < 0.65) {
                        status.innerHTML = '<i class="bx bx-check-shield" style="color:#10b981"></i> <span style="color:#10b981">✓ Face Verified! (score: ' + dist.toFixed(2) + ') Submitting...</span>';
                        clockBtn.style.display = 'block';
                        clockBtn.disabled = false;
                        regBtn.style.display   = 'none';
                        clearInterval(detectionInterval);
                        detectionInterval = null;
                        setTimeout(() => captureAndSubmit(), 700);
                    } else {
                        status.innerHTML = '<i class="bx bx-error" style="color:#ef4444"></i> <span style="color:#ef4444">Face mismatch (score: ' + dist.toFixed(2) + '). Please look directly at camera.</span>';
                        clockBtn.style.display = 'none';
                        regBtn.style.display   = 'none';
                    }
                }
            } catch(err) {
                console.error('[FaceAPI] Detection error:', err);
                status.innerHTML = '<i class="bx bx-error" style="color:#ef4444"></i> Detection error. Retrying...';
            } finally {
                scanning = false;
            }
        }, 1200);
    }

    // ─── Register face ────────────────────────────────────────────────────────
    function registerFace() {
        if (!window.currentDescriptor) return;
        const btn = document.getElementById('register-face-btn');
        btn.disabled = true;
        btn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Registering...';

        fetch('{{ route("employee.face.register") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ face_descriptor: JSON.stringify(Array.from(window.currentDescriptor)) })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('face-auth-status').innerHTML =
                    '<i class="bx bx-check-circle" style="color:#10b981"></i> <span style="color:#10b981">Face registered! Reloading page...</span>';
                setTimeout(() => window.location.reload(), 1200);
            } else {
                btn.disabled = false;
                btn.innerHTML = '<i class="bx bx-face"></i> Register My Face First';
                alert('Registration failed: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bx bx-face"></i> Register My Face First';
            alert('Network error: ' + err.message);
        });
    }

    // ─── Capture & submit ─────────────────────────────────────────────────────
    function captureAndSubmit() {
        const video   = document.getElementById('webcam-preview');
        const canvas  = document.getElementById('photo-canvas');
        const ctx     = canvas.getContext('2d');
        const photoIn = document.getElementById('photo-data');
        const form    = document.getElementById('attendance-form');

        canvas.width  = video.videoWidth;
        canvas.height = video.videoHeight;
        ctx.drawImage(video, 0, 0);

        photoIn.value = canvas.toDataURL('image/jpeg', 0.85);

        // Stop camera before submit
        if (stream) stream.getTracks().forEach(t => t.stop());

        video.style.display  = 'none';
        canvas.style.display = 'block';

        document.getElementById('face-auth-status').innerHTML =
            '<i class="bx bx-check-circle" style="color:#10b981"></i> <span style="color:#10b981">Submitting attendance...</span>';

        setTimeout(() => form.submit(), 500);
    }
</script>
@endpush
@endsection

