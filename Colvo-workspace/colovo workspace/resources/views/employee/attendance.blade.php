@extends('layouts.app')

@section('content')
<div class="app-header">
    <div class="header-title">
        <h1>My Attendance</h1>
    </div>
    
    <div class="topbar-right">
        <a href="{{ route('employee.leave.apply') }}" class="btn btn-outline" style="text-decoration: none;"><i class='bx bx-calendar-event'></i> Apply for Leave</a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success">
    <i class='bx bx-check-circle'></i> {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="alert alert-danger">
    <i class='bx bx-error-circle'></i> {{ session('error') }}
</div>
@endif

<style>
    .attendance-layout {
        grid-template-columns: 1fr 2fr;
    }
    @media (max-width: 1100px) {
        .attendance-layout {
            grid-template-columns: 1fr !important;
        }
    }
    .monthly-summary-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }
    @media (max-width: 768px) {
        .monthly-summary-grid {
            grid-template-columns: 1fr !important;
        }
    }
</style>

<div class="section-grid attendance-layout">
    <!-- Today's Status -->
    <div class="content-panel">
        <div class="panel-header" style="text-align: center; border-bottom: none;">
            <h3 class="panel-title" style="color: var(--primary);">Today's Status</h3>
        </div>
        
        <div style="text-align: center; margin-bottom: 25px;">
            <div id="live-clock-attendance" style="font-size: 42px; font-weight: 700; color: var(--text-main); font-family: monospace; letter-spacing: 2px;">--:--:--</div>
            <div style="color: var(--text-muted); font-size: 14px; margin-top: 5px;">{{ \Carbon\Carbon::now()->format('l, F j, Y') }}</div>
        </div>
        
        <div style="text-align: center; margin-bottom: 25px; font-size: 12px; font-weight: 500;">
            <div style="display: inline-flex; align-items: center; gap: 6px; background: rgba(59, 130, 246, 0.1); color: #3b82f6; padding: 6px 12px; border-radius: 20px; margin-right: 10px;">
                <i class='bx bx-time'></i> Shift: {{ $shiftStart }} - {{ $shiftEnd }}
            </div>
            <div style="display: inline-flex; align-items: center; gap: 6px; background: rgba(245, 158, 11, 0.1); color: #f59e0b; padding: 6px 12px; border-radius: 20px;">
                <i class='bx bx-coffee'></i> Lunch: {{ $lunchStart }} - {{ $lunchEnd }}
            </div>
        </div>
        
        <div style="display: flex; justify-content: center; gap: 15px; margin-bottom: 30px;">
            <div style="background: var(--bg-main); border: 1px solid var(--border-color); padding: 10px 15px; border-radius: 8px; text-align: center; flex: 1;">
                <div style="font-size: 10px; color: var(--text-muted); text-transform: uppercase;">Clock In</div>
                <div style="font-size: 14px; font-weight: 600; color: var(--text-main);">
                    {{ $todayAttendance && $todayAttendance->clock_in ? \Carbon\Carbon::parse($todayAttendance->clock_in)->format('g:i A') : '--:--' }}
                </div>
            </div>
            <div style="background: var(--bg-main); border: 1px solid var(--border-color); padding: 10px 15px; border-radius: 8px; text-align: center; flex: 1;">
                <div style="font-size: 10px; color: var(--text-muted); text-transform: uppercase;">Clock Out</div>
                <div style="font-size: 14px; font-weight: 600; color: var(--text-main);">
                    {{ $todayAttendance && $todayAttendance->clock_out ? \Carbon\Carbon::parse($todayAttendance->clock_out)->format('g:i A') : '--:--' }}
                </div>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 10px;">
            @if(\Carbon\Carbon::now()->isSunday())
                <button class="btn btn-outline btn-block" style="padding: 12px; font-size: 15px; cursor: not-allowed; border-color: #cbd5e1; color: var(--text-muted);" disabled>
                    <i class='bx bx-calendar-x fs-5 me-1'></i> Sunday - Day Off
                </button>
            @else
                @if(!$todayAttendance)
                    <button type="button" class="btn btn-primary btn-block" onclick="openAttendanceModal('clock-in')" style="padding: 12px; font-size: 15px; background: var(--success);"><i class='bx bx-log-in-circle fs-5 me-1'></i> Clock In</button>
                @elseif($todayAttendance && !$todayAttendance->clock_out)
                    <button type="button" class="btn btn-primary btn-block" onclick="openAttendanceModal('clock-out')" style="padding: 12px; font-size: 15px; background: var(--danger);"><i class='bx bx-log-out-circle fs-5 me-1'></i> Clock Out</button>
                    <button class="btn btn-outline btn-block" style="padding: 12px; font-size: 15px;"><i class='bx bx-coffee'></i> Lunch Break ({{ $lunchStart }} - {{ $lunchEnd }})</button>
                @else
                    <button class="btn btn-outline btn-block" style="padding: 12px; font-size: 15px; cursor: not-allowed;" disabled><i class='bx bx-check-shield fs-5 me-1'></i> Day Completed</button>
                @endif
            @endif
        </div>
    </div>

    <!-- Attendance Modal (Webcam & Geo) -->
    <div id="attendance-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
        <div style="background: var(--bg-card); width: 100%; max-width: 400px; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
            <div style="padding: 15px 20px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                <h3 id="attendance-modal-title" style="margin: 0; font-size: 16px;">Mark Attendance</h3>
                <button type="button" onclick="closeAttendanceModal()" style="background: transparent; border: none; font-size: 20px; color: var(--text-muted); cursor: pointer;"><i class='bx bx-x'></i></button>
            </div>
            <div style="padding: 20px;">
                <div style="position: relative; width: 100%; aspect-ratio: 4/3; background: #000; border-radius: 8px; overflow: hidden; margin-bottom: 15px;">
                    <video id="webcam-preview" autoplay playsinline style="width: 100%; height: 100%; object-fit: cover;"></video>
                    <canvas id="photo-canvas" style="display: none; width: 100%; height: 100%;"></canvas>
                </div>
                
                <div id="geo-status" style="margin-bottom: 15px; font-size: 13px; color: var(--text-muted); display: flex; align-items: center; gap: 8px;">
                    <i class='bx bx-loader-alt bx-spin'></i> Fetching location...
                </div>

                <div id="face-auth-status" style="margin-bottom: 15px; font-size: 13px; color: var(--text-muted); display: flex; align-items: center; gap: 8px;">
                    <i class='bx bx-loader-alt bx-spin'></i> Loading AI Models...
                </div>

                <form id="attendance-form" method="POST" action="">
                    @csrf
                    <input type="hidden" name="latitude" id="latitude">
                    <input type="hidden" name="longitude" id="longitude">
                    <input type="hidden" name="photo" id="photo-data">
                    <input type="hidden" name="type" id="attendance-type">
                    
                    <button type="button" id="capture-btn" class="btn btn-primary btn-block" onclick="captureAndSubmit()" disabled style="padding: 10px; display: none;">
                        Clock In
                    </button>
                    <button type="button" id="register-face-btn" class="btn btn-warning btn-block" onclick="registerFace()" style="padding: 10px; display: none;">
                        Register My Face
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Right side: Monthly Summary & Logs -->
    <div style="display: flex; flex-direction: column; gap: 20px;">
        
        <h4 style="font-size: 14px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; font-weight: 600; margin-bottom: 0;">Monthly Summary</h4>
        <div class="monthly-summary-grid">
            <div class="metric-card blue" style="flex-direction: column; text-align: center; padding: 20px;">
                <div class="metric-card-icon" style="margin: 0 auto 10px;"><i class='bx bx-calendar-check'></i></div>
                <div class="metric-card-details">
                    <div class="metric-card-value">{{ $presentDays }}</div>
                    <div class="metric-card-title" style="margin-top: 5px;">Present Days</div>
                </div>
            </div>
            <div class="metric-card rose" style="flex-direction: column; text-align: center; padding: 20px;">
                <div class="metric-card-icon" style="margin: 0 auto 10px;"><i class='bx bx-calendar-x'></i></div>
                <div class="metric-card-details">
                    <div class="metric-card-value">{{ $absentDays }}</div>
                    <div class="metric-card-title" style="margin-top: 5px;">Absent / Leave</div>
                </div>
            </div>
            <div class="metric-card amber" style="flex-direction: column; text-align: center; padding: 20px;">
                <div class="metric-card-icon" style="margin: 0 auto 10px;"><i class='bx bx-time-five'></i></div>
                <div class="metric-card-details">
                    <div class="metric-card-value">4</div>
                    <div class="metric-card-title" style="margin-top: 5px;">Late Entries</div>
                </div>
            </div>
            <div class="metric-card purple" style="flex-direction: column; text-align: center; padding: 20px;">
                <div class="metric-card-icon" style="margin: 0 auto 10px;"><i class='bx bx-pie-chart-alt-2'></i></div>
                <div class="metric-card-details">
                    <div class="metric-card-value">90%</div>
                    <div class="metric-card-title" style="margin-top: 5px;">Attendance Rate</div>
                </div>
            </div>
        </div>

        <div class="content-panel">
            <div class="panel-header">
                <h3 class="panel-title">Attendance History</h3>
            </div>
            <div class="table-responsive">
                <table id="attendanceTable" class="custom-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Clock In</th>
                            <th>Clock Out</th>
                            <th>Total Hours</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attendances as $att)
                        <tr>
                            <td>
                                <div style="font-weight: 600; color: var(--text-main);">{{ \Carbon\Carbon::parse($att->date)->format('M d, Y') }}</div>
                                <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">{{ \Carbon\Carbon::parse($att->date)->format('l') }}</div>
                            </td>
                            <td>
                                @if($att->clock_in)
                                    <span style="font-weight: 500; color: var(--text-main);">{{ \Carbon\Carbon::parse($att->clock_in)->format('g:i A') }}</span>
                                @else
                                    <span style="color: var(--text-muted);">--:--</span>
                                @endif
                            </td>
                            <td>
                                @if($att->clock_out)
                                    <span style="font-weight: 500; color: var(--text-main);">{{ \Carbon\Carbon::parse($att->clock_out)->format('g:i A') }}</span>
                                @else
                                    <span style="color: var(--text-muted);">--:--</span>
                                @endif
                            </td>
                            <td>
                                @if($att->clock_in && $att->clock_out)
                                    @php
                                        $in = \Carbon\Carbon::parse($att->clock_in);
                                        $out = \Carbon\Carbon::parse($att->clock_out);
                                        $diff = $in->diff($out);
                                    @endphp
                                    <span class="badge" style="background: var(--bg-main); color: var(--text-main); border: 1px solid var(--border-color);">{{ $diff->format('%h hrs %i mins') }}</span>
                                @else
                                    <span style="color: var(--text-muted);">--</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $att->status == 'present' ? 'success' : ($att->status == 'late' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($att->status) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- DataTables CSS for Theme Customization -->
<link href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css" rel="stylesheet">
<style>
    .dataTables_wrapper .dataTables_length, .dataTables_wrapper .dataTables_filter, .dataTables_wrapper .dataTables_info, .dataTables_wrapper .dataTables_processing, .dataTables_wrapper .dataTables_paginate {
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
    .dataTables_wrapper .dataTables_length select {
        border: 1px solid var(--border-color);
        background: var(--bg-card);
        color: var(--text-main);
        border-radius: 6px;
        padding: 4px;
        outline: none;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        color: var(--text-muted) !important;
        border-color: transparent !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current, .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: var(--primary) !important;
        color: white !important;
        border-color: var(--primary) !important;
        border-radius: 6px;
    }
    table.dataTable.no-footer {
        border-bottom: 1px solid var(--border-color);
    }
    table.dataTable thead th, table.dataTable thead td {
        border-bottom: 1px solid var(--border-color);
    }
</style>
@endsection

@push('scripts')
<script src="{{ asset('js/face-api.min.js') }}"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
    let stream = null;

    function openAttendanceModal(type) {
        const modal = document.getElementById('attendance-modal');
        const title = document.getElementById('attendance-modal-title');
        const form = document.getElementById('attendance-form');
        const typeInput = document.getElementById('attendance-type');
        const geoStatus = document.getElementById('geo-status');
        const captureBtn = document.getElementById('capture-btn');
        
        modal.style.display = 'flex';
        typeInput.value = type;
        
        if (type === 'clock-in') {
            title.textContent = 'Clock In';
            form.action = '{{ route("employee.clock-in") }}';
        } else {
            title.textContent = 'Clock Out';
            form.action = '{{ route("employee.clock-out") }}';
        }

        // Start webcam
        startWebcam();
        
        // Get Location
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                document.getElementById('latitude').value = position.coords.latitude;
                document.getElementById('longitude').value = position.coords.longitude;
                geoStatus.innerHTML = '<i class="bx bx-check-circle"></i> Location acquired';
                geoStatus.style.color = '#10b981';
                captureBtn.disabled = false;
            }, function(error) {
                geoStatus.innerHTML = '<i class="bx bx-error"></i> Error getting location: ' + error.message;
                geoStatus.style.color = '#ef4444';
                captureBtn.disabled = true;
            });
        } else {
            geoStatus.innerHTML = '<i class="bx bx-error"></i> Geolocation not supported';
            geoStatus.style.color = '#ef4444';
            captureBtn.disabled = true;
        }
    }

    function closeAttendanceModal() {
        document.getElementById('attendance-modal').style.display = 'none';
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
    }

    const userFaceDescriptorStr = '{!! addslashes(Auth::user()->face_descriptor) !!}';
    let modelsLoaded = false;
    let detectionInterval;
    let registeredDescriptor = null;

    if (userFaceDescriptorStr && userFaceDescriptorStr !== 'null' && userFaceDescriptorStr !== '') {
        try {
            const arr = JSON.parse(userFaceDescriptorStr);
            registeredDescriptor = new Float32Array(Object.values(arr));
        } catch(e) { console.error('Failed to parse user face descriptor'); }
    }

    async function loadModels() {
        if(modelsLoaded) return;
        const status = document.getElementById('face-auth-status');
        try {
            await faceapi.nets.ssdMobilenetv1.loadFromUri('/models');
            await faceapi.nets.faceLandmark68Net.loadFromUri('/models');
            await faceapi.nets.faceRecognitionNet.loadFromUri('/models');
            modelsLoaded = true;
            status.innerHTML = '<i class="bx bx-check-circle"></i> AI Models Ready';
            status.style.color = '#10b981';
        } catch(e) {
            console.error(e);
            status.innerHTML = '<i class="bx bx-error"></i> Error loading AI models';
            status.style.color = '#ef4444';
        }
    }

    async function startWebcam() {
        const video = document.getElementById('webcam-preview');
        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: true });
            video.srcObject = stream;
            
            await loadModels();
            
            video.addEventListener('play', () => {
                detectFaceLoop();
            });
        } catch (err) {
            console.error("Error accessing webcam: ", err);
            document.getElementById('geo-status').innerHTML += '<br><i class="bx bx-error"></i> Error accessing webcam';
        }
    }

    async function detectFaceLoop() {
        const video = document.getElementById('webcam-preview');
        const status = document.getElementById('face-auth-status');
        const clockBtn = document.getElementById('capture-btn');
        const regBtn = document.getElementById('register-face-btn');

        if(detectionInterval) clearInterval(detectionInterval);
        
        detectionInterval = setInterval(async () => {
            if(!modelsLoaded || video.paused || video.ended) return;
            
            const detections = await faceapi.detectSingleFace(video).withFaceLandmarks().withFaceDescriptor();
            
            if (detections) {
                if (!registeredDescriptor) {
                    status.innerHTML = '<i class="bx bx-face"></i> Face detected. Please register.';
                    status.style.color = '#f59e0b';
                    clockBtn.style.display = 'none';
                    regBtn.style.display = 'block';
                    window.currentDescriptor = detections.descriptor;
                } else {
                    const distance = faceapi.euclideanDistance(detections.descriptor, registeredDescriptor);
                    if (distance < 0.65) {
                        status.innerHTML = '<i class="bx bx-check-shield"></i> Face Verified!';
                        status.style.color = '#10b981';
                        if(!clockBtn.disabled) {
                            clockBtn.style.display = 'block';
                            regBtn.style.display = 'none';
                        }
                    } else {
                        status.innerHTML = '<i class="bx bx-error"></i> Face mismatch (' + distance.toFixed(2) + ')';
                        status.style.color = '#ef4444';
                        clockBtn.style.display = 'none';
                        regBtn.style.display = 'none';
                    }
                }
            } else {
                status.innerHTML = '<i class="bx bx-scan"></i> Scanning face...';
                status.style.color = 'var(--text-muted)';
                clockBtn.style.display = 'none';
                regBtn.style.display = 'none';
            }
        }, 1000);
    }

    function registerFace() {
        if(!window.currentDescriptor) return;
        
        fetch('{{ route("employee.face.register") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ face_descriptor: JSON.stringify(Array.from(window.currentDescriptor)) })
        }).then(res => res.json()).then(data => {
            if(data.success) {
                alert('Face registered successfully! Please reload the page to clock in.');
                window.location.reload();
            } else {
                alert('Error registering face');
            }
        });
    }

    function captureAndSubmit() {
        const video = document.getElementById('webcam-preview');
        const canvas = document.getElementById('photo-canvas');
        const context = canvas.getContext('2d');
        const form = document.getElementById('attendance-form');
        const photoDataInput = document.getElementById('photo-data');

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        const dataUrl = canvas.toDataURL('image/jpeg');
        photoDataInput.value = dataUrl;

        // Visual feedback
        video.style.display = 'none';
        canvas.style.display = 'block';

        setTimeout(() => {
            form.submit();
        }, 500);
    }

    setInterval(() => {
        const d = new Date();
        const el = document.getElementById('live-clock-attendance');
        if(el) {
            el.innerText = d.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit', second:'2-digit'});
        }
    }, 1000);

    $(document).ready(function() {
        $('#attendanceTable').DataTable({
            "pageLength": 10,
            "ordering": false,
            "language": {
                "search": "",
                "searchPlaceholder": "Search records..."
            }
        });
    });
</script>
@endpush
