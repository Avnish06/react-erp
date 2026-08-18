@extends('layouts.app')

@section('content')
<div class="app-header">
    <div class="header-title">
        <h1>My Growth Dashboard</h1>
        <p>Your personal career development and performance tracking.</p>
    </div>
    
    <div class="topbar-right">
        
    </div>
</div>

<style>
    .growth-layout {
        grid-template-columns: 1fr 2fr;
        margin-bottom: 24px;
    }
    .growth-layout-half {
        grid-template-columns: 1fr 1fr;
        margin-bottom: 24px;
    }
    .growth-layout-reverse {
        grid-template-columns: 2fr 1fr;
    }
    .growth-metrics-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
    }
    @media (max-width: 1100px) {
        .growth-layout, .growth-layout-half, .growth-layout-reverse {
            grid-template-columns: 1fr !important;
        }
    }
    @media (max-width: 768px) {
        .growth-metrics-grid {
            grid-template-columns: 1fr !important;
        }
    }
</style>

<div class="section-grid growth-layout">
    <!-- Section 1: Overall Growth Score -->
    <div class="content-panel" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); color: white; display: flex; flex-direction: column; justify-content: center; text-align: center; border: none;">
        <h3 style="font-size: 16px; font-weight: 500; opacity: 0.9; margin-bottom: 10px;">Monthly Growth Score</h3>
        <div style="font-size: 64px; font-weight: 700; line-height: 1;">{{ $growthScore }}<span style="font-size: 24px;">%</span></div>
        <div style="margin-top: 10px;">
            <span class="badge" style="background: rgba(255,255,255,0.2); color: white; border: none; font-size: 12px;">
                @if($growthScore >= 90) Excellent
                @elseif($growthScore >= 75) Good
                @elseif($growthScore >= 50) Average
                @else Needs Improvement
                @endif
            </span>
        </div>
        <div style="margin-top: 20px; font-size: 13px; opacity: 0.8;">
            <i class='bx {{ $growthScore >= $prevGrowthScore ? "bx-trending-up" : "bx-trending-down" }}'></i> 
            {{ abs($growthScore - $prevGrowthScore) }}% from last month
        </div>
    </div>

    <!-- Section 4: Performance Summary -->
    <div class="growth-metrics-grid">
        <div class="metric-card blue" style="flex-direction: column; text-align: center; padding: 15px;">
            <div class="metric-card-icon" style="margin: 0 auto 10px;"><i class='bx bx-task'></i></div>
            <div class="metric-card-details">
                <div class="metric-card-value">{{ $taskCompletionRate }}%</div>
                <div class="metric-card-title" style="margin-top: 5px; font-size: 11px;">Task Rate</div>
            </div>
        </div>
        <div class="metric-card rose" style="flex-direction: column; text-align: center; padding: 15px;">
            <div class="metric-card-icon" style="margin: 0 auto 10px;"><i class='bx bx-calendar-check'></i></div>
            <div class="metric-card-details">
                <div class="metric-card-value">{{ $attendanceRate }}%</div>
                <div class="metric-card-title" style="margin-top: 5px; font-size: 11px;">Attendance</div>
            </div>
        </div>
        <div class="metric-card amber" style="flex-direction: column; text-align: center; padding: 15px;">
            <div class="metric-card-icon" style="margin: 0 auto 10px;"><i class='bx bx-target-lock'></i></div>
            <div class="metric-card-details">
                <div class="metric-card-value">{{ $averageKpi }}%</div>
                <div class="metric-card-title" style="margin-top: 5px; font-size: 11px;">KPI Score</div>
            </div>
        </div>
        <div class="metric-card purple" style="flex-direction: column; text-align: center; padding: 15px;">
            <div class="metric-card-icon" style="margin: 0 auto 10px;"><i class='bx bx-star'></i></div>
            <div class="metric-card-details">
                <div class="metric-card-value">{{ $latestReview ? $latestReview->score . '/5' : 'N/A' }}</div>
                <div class="metric-card-title" style="margin-top: 5px; font-size: 11px;">Manager Rating</div>
            </div>
        </div>
    </div>
</div>

<div class="section-grid" style="grid-template-columns: 1fr; margin-bottom: 24px;">
    <!-- Section 2: Monthly Growth Analytics -->
    <div class="content-panel">
        <div class="panel-header">
            <h3 class="panel-title">Monthly Growth Analytics</h3>
        </div>
        <div style="height: 300px; width: 100%;">
            <canvas id="growthChart"></canvas>
        </div>
    </div>
</div>

<div class="section-grid growth-layout-half">
    <!-- Section 3: Promotion Readiness -->
    <div class="content-panel">
        <div class="panel-header">
            <h3 class="panel-title">Promotion Readiness</h3>
        </div>
        <div style="text-align: center; margin-bottom: 20px;">
            <div style="font-size: 36px; font-weight: 700; color: var(--primary);">65%</div>
            <div style="font-size: 12px; color: var(--text-muted);">Ready for Senior Exec</div>
            <div style="width: 100%; height: 6px; background: var(--bg-main); border-radius: 6px; margin-top: 10px;">
                <div style="width: 65%; height: 100%; background: var(--primary); border-radius: 6px;"></div>
            </div>
        </div>
        <h4 style="font-size: 12px; color: var(--text-muted); text-transform: uppercase; margin-bottom: 10px; font-weight: 600;">Criteria Checklist</h4>
        <div style="font-size: 12px; color: var(--text-main); display: flex; flex-direction: column; gap: 8px;">
            <div style="display: flex; gap: 8px;"><i class='bx bx-check-circle' style="color: var(--success); font-size: 16px;"></i> 1 Year Tenure</div>
            <div style="display: flex; gap: 8px;"><i class='bx bx-check-circle' style="color: var(--success); font-size: 16px;"></i> KPI Score > 85%</div>
            <div style="display: flex; gap: 8px;"><i class='bx bx-x-circle' style="color: var(--danger); font-size: 16px;"></i> 2 Advanced Certifications</div>
        </div>
        <div style="margin-top: 20px; font-size: 11px; color: var(--warning); background: rgba(245, 158, 11, 0.1); padding: 8px; border-radius: 4px; border: 1px solid rgba(245, 158, 11, 0.2);">
            <i class='bx bx-time'></i> Est. Timeline: Q4 2026
        </div>
    </div>

    <!-- Section 7: Manager Feedback -->
    <div class="content-panel">
        <div class="panel-header">
            <h3 class="panel-title">Manager Feedback</h3>
        </div>
        @if($latestReview)
        <div style="font-size: 13px; color: var(--text-main); line-height: 1.6;">
            <p><strong>Review Period:</strong> {{ $latestReview->review_period }}</p>
            <div style="margin-top: 15px;">
                <span class="badge badge-success" style="margin-bottom: 8px;">Strengths</span>
                <p style="color: var(--text-muted);">Excellent problem-solving skills, meets deadlines consistently, and great teamwork.</p>
            </div>
            <div style="margin-top: 15px;">
                <span class="badge badge-warning" style="margin-bottom: 8px;">Areas for Improvement</span>
                <p style="color: var(--text-muted);">Could improve documentation habits and take more initiative in architectural discussions.</p>
            </div>
            <div style="margin-top: 15px; font-style: italic; border-left: 2px solid var(--primary); padding-left: 10px; color: var(--text-muted);">
                "Great quarter overall. Keep up the good work and focus on the technical certifications."
            </div>
        </div>
        @else
        <div style="text-align: center; color: var(--text-muted); padding: 30px;">
            <i class='bx bx-message-square-error' style="font-size: 40px; margin-bottom: 10px; opacity: 0.5;"></i>
            <p>No feedback recorded yet.</p>
        </div>
        @endif
    </div>
</div>

<div class="section-grid growth-layout-reverse">
    <!-- Section 9: Work Analytics (Table) -->
    <div class="content-panel">
        <div class="panel-header">
            <h3 class="panel-title">Work Analytics Summary</h3>
        </div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Metric</th>
                        <th>This Month</th>
                        <th>Lifetime Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Tasks Completed</strong></td>
                        <td>{{ $completedTasks }}</td>
                        <td>{{ $totalTasks }}</td>
                    </tr>
                    <tr>
                        <td><strong>Attendance Rate</strong></td>
                        <td>{{ $attendanceRate }}%</td>
                        <td>-</td>
                    </tr>
                    <tr>
                        <td><strong>Projects Delivered</strong></td>
                        <td>{{ $completedProjects }}</td>
                        <td>{{ $totalProjects }}</td>
                    </tr>
                    <tr>
                        <td><strong>Leaves Taken</strong></td>
                        <td>{{ $leavesTaken ?? 0 }} Days</td>
                        <td>{{ $leavesTaken ?? 0 }} Days</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 6: Achievements & Recognition -->
    <div class="content-panel">
        <div class="panel-header">
            <h3 class="panel-title">Achievements</h3>
        </div>
        <div>
            @if($promotions->isEmpty())
                <div style="text-align: center; padding: 50px 0; color: var(--text-muted);">
                    <i class='bx bx-award' style="font-size: 40px; margin-bottom: 15px; opacity: 0.5;"></i>
                    <p style="margin: 0; font-size: 13px;">No achievements logged yet.</p>
                </div>
            @else
                <div style="position: relative; padding-left: 20px; border-left: 2px solid var(--border-color);">
                    @foreach($promotions as $promo)
                    <div style="position: relative; margin-bottom: 25px;">
                        <span style="position: absolute; top: 0; left: -26px; width: 10px; height: 10px; border-radius: 50%; background: {{ $promo->type == 'promotion' ? 'var(--primary)' : ($promo->type == 'pip' ? 'var(--danger)' : 'var(--success)') }}; border: 2px solid var(--bg-card);"></span>
                        
                        <div style="background: var(--bg-main); border: 1px solid var(--border-color); border-radius: 10px; padding: 15px;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 5px;">
                                <div style="font-weight: 600; font-size: 13px; color: var(--text-main);">{{ $promo->title }}</div>
                                <span style="font-size: 10px; color: var(--text-muted);">{{ \Carbon\Carbon::parse($promo->date_awarded)->format('M Y') }}</span>
                            </div>
                            <span class="badge badge-{{ $promo->type == 'promotion' ? 'primary' : ($promo->type == 'pip' ? 'danger' : 'success') }}" style="font-size: 9px; margin-bottom: 10px;">
                                {{ str_replace('_', ' ', strtoupper($promo->type)) }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('growthChart').getContext('2d');
        
        // Mock data for the last 6 months
        const labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
        const productivityData = [75, 78, 80, 85, 82, {{ $growthScore }}];
        const attendanceData = [95, 96, 94, 98, 97, {{ $attendanceRate }}];
        const taskData = [60, 65, 70, 75, 80, {{ $taskCompletionRate }}];

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Productivity Trend',
                        data: productivityData,
                        borderColor: '#4f46e5',
                        backgroundColor: 'rgba(79, 70, 229, 0.1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Attendance Trend',
                        data: attendanceData,
                        borderColor: '#10b981',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        tension: 0.4,
                        borderDash: [5, 5]
                    },
                    {
                        label: 'Task Completion',
                        data: taskData,
                        borderColor: '#f59e0b',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            color: '#94a3b8',
                            font: { size: 11, family: 'Inter' },
                            usePointStyle: true,
                            boxWidth: 8
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false, drawBorder: false },
                        ticks: { color: '#94a3b8', font: { size: 11 } }
                    },
                    y: {
                        grid: { color: 'rgba(148, 163, 184, 0.1)', drawBorder: false },
                        ticks: { color: '#94a3b8', font: { size: 11 }, stepSize: 20 },
                        min: 0,
                        max: 100
                    }
                }
            }
        });
    });
</script>
@endpush
