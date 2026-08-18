@extends('layouts.app')

@section('content')

{{-- ── Page Header ──────────────────────────────────────────── --}}
<div class="app-header">
    <div class="header-title">
        <h1><i class='bx bx-file-blank' style="color:var(--primary); margin-right:8px;"></i>Joining Document</h1>
        <p>View and generate your official joining letter & employment documents.</p>
    </div>
    <div class="topbar-right">
        <div class="noti-bell"><i class='bx bx-bell'></i></div>
        <div style="display:flex; align-items:center; gap:10px;">
            <div class="user-avatar-placeholder" style="width:40px; height:40px; font-size:14px;">
                {{ substr(auth()->user()->name, 0, 1) }}
            </div>
            <div style="font-size:13px; font-weight:600; color:var(--text-main);">
                <div>{{ auth()->user()->name }}</div>
                <div style="font-size:11px; color:var(--text-muted); font-weight:normal;">{{ auth()->user()->position ?? auth()->user()->role }}</div>
            </div>
            <i class='bx bx-chevron-down' style="color:var(--text-muted);"></i>
        </div>
    </div>
</div>

{{-- ── Welcome Banner ──────────────────────────────────────── --}}
<div class="welcome-banner" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:20px;">
    <div>
        <span class="welcome-badge">OFFICIAL DOCUMENT</span>
        <h2 class="welcome-title">👋 Hello, {{ auth()->user()->name }}!</h2>
        <p class="welcome-quote">Your joining letter will be generated based on your profile data and company details.</p>
        <div style="display:flex; gap:18px; margin-top:16px; font-size:13px; color:rgba(255,255,255,0.85); flex-wrap:wrap;">
            <span><i class='bx bx-buildings'></i> {{ $company->name ?? 'Your Company' }}</span>
            <span><i class='bx bx-briefcase'></i> {{ auth()->user()->position ?? 'Employee' }}</span>
            <span><i class='bx bx-building'></i> {{ auth()->user()->department ?? 'Department' }}</span>
        </div>
    </div>
    <div style="text-align:right;">
        <div style="font-size:13px; color:rgba(255,255,255,0.8);">Date</div>
        <div style="font-size:22px; font-weight:700; color:#fff;">{{ \Carbon\Carbon::now()->format('d M Y') }}</div>
    </div>
</div>

{{-- ── Stat Cards ──────────────────────────────────────────── --}}
<div class="metrics-grid">
    <div class="metric-card">
        <div class="metric-card-icon"><i class='bx bx-user'></i></div>
        <div class="metric-card-details">
            <div class="metric-card-title">Employee Name</div>
            <div class="metric-card-value" style="font-size:18px; margin-top:4px;">{{ auth()->user()->name }}</div>
        </div>
    </div>
    <div class="metric-card amber">
        <div class="metric-card-icon"><i class='bx bx-briefcase'></i></div>
        <div class="metric-card-details">
            <div class="metric-card-title">Designation</div>
            <div class="metric-card-value" style="font-size:18px; margin-top:4px;">{{ auth()->user()->position ?? 'N/A' }}</div>
        </div>
    </div>
    <div class="metric-card rose">
        <div class="metric-card-icon"><i class='bx bx-building'></i></div>
        <div class="metric-card-details">
            <div class="metric-card-title">Department</div>
            <div class="metric-card-value" style="font-size:18px; margin-top:4px;">{{ auth()->user()->department ?? 'N/A' }}</div>
        </div>
    </div>
    <div class="metric-card purple">
        <div class="metric-card-icon"><i class='bx bx-buildings'></i></div>
        <div class="metric-card-details">
            <div class="metric-card-title">Company</div>
            <div class="metric-card-value" style="font-size:18px; margin-top:4px;">{{ $company->name ?? 'N/A' }}</div>
        </div>
    </div>
</div>

{{-- ── Main Content ─────────────────────────────────────────── --}}
<style>
    .docs-layout {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 24px;
        margin-bottom: 24px;
    }
    @media (max-width: 1100px) {
        .docs-layout {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="docs-layout">

    {{-- Left: Company Info --}}
    <div class="content-panel">
        <div class="panel-header">
            <h3 class="panel-title"><i class='bx bx-buildings' style="color:var(--primary);"></i> Company Info</h3>
        </div>
        <div style="padding:4px 0;">
            @if($company && $company->logo)
            <div style="text-align:center; margin-bottom:16px;">
                <img src="{{ asset('storage/' . $company->logo) }}" alt="Logo" style="height:60px; object-fit:contain;">
            </div>
            @endif
            <div style="display:flex; flex-direction:column; gap:10px; font-size:13px;">
                <div style="display:flex; align-items:flex-start; gap:10px; padding:10px; background:var(--bg-main); border-radius:10px;">
                    <i class='bx bx-buildings' style="color:var(--primary); font-size:18px; flex-shrink:0; margin-top:1px;"></i>
                    <div>
                        <div style="font-size:11px; color:var(--text-muted); font-weight:600; text-transform:uppercase; letter-spacing:0.4px;">Company</div>
                        <div style="font-weight:600; color:var(--text-main);">{{ $company->name ?? 'N/A' }}</div>
                    </div>
                </div>
                <div style="display:flex; align-items:flex-start; gap:10px; padding:10px; background:var(--bg-main); border-radius:10px;">
                    <i class='bx bx-map' style="color:var(--primary); font-size:18px; flex-shrink:0; margin-top:1px;"></i>
                    <div>
                        <div style="font-size:11px; color:var(--text-muted); font-weight:600; text-transform:uppercase; letter-spacing:0.4px;">Address</div>
                        <div style="font-weight:500; color:var(--text-main);">{{ $company->address ?? 'N/A' }}</div>
                    </div>
                </div>
                <div style="display:flex; align-items:flex-start; gap:10px; padding:10px; background:var(--bg-main); border-radius:10px;">
                    <i class='bx bx-envelope' style="color:var(--primary); font-size:18px; flex-shrink:0; margin-top:1px;"></i>
                    <div>
                        <div style="font-size:11px; color:var(--text-muted); font-weight:600; text-transform:uppercase; letter-spacing:0.4px;">Email</div>
                        <div style="font-weight:500; color:var(--text-main);">{{ $company->email ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Right: Documents Available --}}
    <div class="content-panel">
        <div class="panel-header">
            <h3 class="panel-title"><i class='bx bx-file' style="color:var(--primary);"></i> Available Documents</h3>
            <p style="font-size:12px; color:var(--text-muted); margin-top:4px;">Click on any document to view your generated official copy.</p>
        </div>
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(240px, 1fr)); gap:12px; padding:10px 0;">
            @foreach([
                ['id' => 'offer_letter', 'icon' => 'bx-envelope-open',  'label' => 'Offer Letter',        'color' => 'var(--primary)'],
                ['id' => 'appointment_letter', 'icon' => 'bx-id-card',         'label' => 'Appointment Letter',  'color' => 'var(--success)'],
                ['id' => 'employment_contract', 'icon' => 'bx-file-blank',      'label' => 'Employment Contract', 'color' => 'var(--info)'],
                ['id' => 'nda', 'icon' => 'bx-lock-alt',        'label' => 'NDA',                 'color' => 'var(--warning)'],
                ['id' => 'leave_policy', 'icon' => 'bx-calendar-check',  'label' => 'Leave Policy',        'color' => 'var(--danger)'],
            ] as $docItem)
            <form action="{{ route('employee.joining-document.generate') }}" method="POST" target="_blank" style="margin:0;">
                @csrf
                <input type="hidden" name="document_type" value="{{ $docItem['id'] }}">
                <button type="submit" style="width:100%; border:none; text-align:left; padding:0; background:transparent; cursor:pointer; outline:none;">
                    <div style="display:flex; align-items:center; gap:12px; padding:16px; border:1px solid var(--border-color); border-radius:12px; background:var(--bg-main); transition:all 0.2s;"
                         onmouseover="this.style.borderColor='var(--primary)'; this.style.background='var(--primary-glow)';"
                         onmouseout="this.style.borderColor='var(--border-color)'; this.style.background='var(--bg-main)';">
                        <div style="width:40px; height:40px; border-radius:10px; background:rgba(0,0,0,0.03); display:flex; align-items:center; justify-content:center;">
                            <i class='bx {{ $docItem["icon"] }}' style="font-size:20px; color:{{ $docItem['color'] }};"></i>
                        </div>
                        <div style="flex:1;">
                            <div style="font-size:14px; font-weight:600; color:var(--text-main);">{{ $docItem['label'] }}</div>
                            <div style="font-size:11px; color:var(--text-muted); margin-top:2px;">View Document</div>
                        </div>
                        <i class='bx bx-right-arrow-alt' style="color:var(--text-muted); font-size:18px;"></i>
                    </div>
                </button>
            </form>
            @endforeach
        </div>
        <div style="margin-top:20px; text-align:right; border-top:1px solid var(--border-color); padding-top:16px;">
            <form action="{{ route('employee.joining-document.generate') }}" method="POST" target="_blank" style="margin:0; display:inline-block;">
                @csrf
                <input type="hidden" name="document_type" value="all">
                <button type="submit" class="btn btn-sm btn-outline" style="font-size:13px; border-radius:8px;">
                    <i class='bx bx-download'></i> Download All Documents
                </button>
            </form>
        </div>
    </div>

</div>


@endsection
