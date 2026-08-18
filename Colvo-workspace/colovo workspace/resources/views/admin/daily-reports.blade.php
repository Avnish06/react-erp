@extends('layouts.app')

@section('content')
<div class="app-header">
    <div class="header-title">
        <h1>Daily Reports</h1>
        <p>Review daily activity and progress reports from your team.</p>
    </div>
</div>

<div class="content-panel">
    <div class="panel-header">
        <h3 class="panel-title">Employee Daily Reports</h3>
    </div>
    <div class="table-responsive" style="padding: 20px;">
        @if($dailyReports->isEmpty())
            <div style="text-align: center; color: var(--text-muted); padding: 30px;">
                <i class='bx bx-file-blank' style="font-size: 48px; margin-bottom: 10px;"></i>
                <p>No daily reports have been submitted yet.</p>
            </div>
        @else
            <div style="display: flex; flex-direction: column; gap: 20px;">
                @foreach($dailyReports as $report)
                <div style="border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-main); overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                    
                    <!-- Summary Row (Clickable) -->
                    <div onclick="document.getElementById('report-details-{{ $report->id }}').style.display = document.getElementById('report-details-{{ $report->id }}').style.display === 'none' ? 'block' : 'none'; this.querySelector('.toggle-icon').classList.toggle('bx-chevron-up'); this.querySelector('.toggle-icon').classList.toggle('bx-chevron-down');" 
                         style="cursor: pointer; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; background: var(--bg-card); transition: background 0.2s;"
                         onmouseover="this.style.background='var(--bg-main)'" onmouseout="this.style.background='var(--bg-card)'">
                        
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <div class="user-avatar-placeholder" style="width: 40px; height: 40px; font-size: 15px; border-radius: 50%;">
                                {{ substr($report->user->name, 0, 1) }}
                            </div>
                            <div>
                                <h4 style="margin: 0; font-size: 15px; font-weight: 600; color: var(--text-main);">{{ $report->user->name }}</h4>
                                <span style="font-size: 12px; color: var(--text-muted);">{{ $report->user->department ?? $report->user->role }}</span>
                            </div>
                        </div>
                        
                        <div style="display: flex; align-items: center; gap: 20px;">
                            <div style="text-align: right;">
                                <div style="font-size: 14px; font-weight: 600; color: var(--text-main);">
                                    {{ \Carbon\Carbon::parse($report->report_date)->format('l, F j, Y') }}
                                </div>
                                <div style="font-size: 12px; color: var(--text-muted);">
                                    Submitted at {{ \Carbon\Carbon::parse($report->created_at)->format('g:i A') }}
                                </div>
                            </div>
                            
                            <!-- Stop click propagation on the delete button -->
                            <form action="{{ route('admin.daily-reports.delete', $report->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this report?');" onclick="event.stopPropagation();">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" style="padding: 6px 12px; border-radius: 6px;" title="Delete Report">
                                    <i class='bx bx-trash' style="margin: 0;"></i>
                                </button>
                            </form>
                            
                            <i class='bx bx-chevron-down toggle-icon' style="font-size: 22px; color: var(--text-muted); transition: transform 0.3s;"></i>
                        </div>
                    </div>

                    <!-- Hidden Details Content -->
                    <div id="report-details-{{ $report->id }}" style="display: none; padding: 25px; border-top: 1px solid var(--border-color);">
                        <div style="display: grid; grid-template-columns: 1fr; gap: 20px;">
                            <div>
                                <h5 style="font-size: 13px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;"><i class='bx bx-check-circle' style="color: var(--success);"></i> Tasks Completed Today</h5>
                                <div style="background: var(--bg-card); padding: 15px; border-radius: 6px; font-size: 14px; color: var(--text-main); white-space: pre-wrap; line-height: 1.6;">{{ $report->tasks_completed }}</div>
                            </div>

                            <div>
                                <h5 style="font-size: 13px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;"><i class='bx bx-error-circle' style="color: var(--warning);"></i> Challenges / Blockers</h5>
                                <div style="background: var(--bg-card); padding: 15px; border-radius: 6px; font-size: 14px; color: var(--text-main); white-space: pre-wrap; line-height: 1.6;">{{ $report->challenges ?: 'No challenges reported.' }}</div>
                            </div>

                            <div>
                                <h5 style="font-size: 13px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;"><i class='bx bx-calendar-event' style="color: var(--primary);"></i> Plan for Tomorrow</h5>
                                <div style="background: var(--bg-card); padding: 15px; border-radius: 6px; font-size: 14px; color: var(--text-main); white-space: pre-wrap; line-height: 1.6;">{{ $report->plan_tomorrow }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
