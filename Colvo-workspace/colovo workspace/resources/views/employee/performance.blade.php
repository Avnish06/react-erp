@extends('layouts.app')

@section('content')
<div class="app-header">
    <div class="header-title">
        <h1>My Performance</h1>
    </div>
    
    <div class="topbar-right">
        <a href="#" class="btn btn-secondary" style="white-space: nowrap;"><i class='bx bx-bullseye'></i> My Goals</a>
    </div>
</div>

<style>
    .performance-layout {
        grid-template-columns: 1fr 2fr;
    }
    .performance-layout-reverse {
        grid-template-columns: 2fr 1fr;
        margin-top: 20px;
    }
    .performance-metrics-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
    }
    @media (max-width: 1100px) {
        .performance-layout, .performance-layout-reverse {
            grid-template-columns: 1fr !important;
        }
    }
    @media (max-width: 768px) {
        .performance-metrics-grid {
            grid-template-columns: 1fr !important;
        }
    }
</style>

<div class="section-grid performance-layout">
    <!-- Left side: Overall Rating -->
    <div class="content-panel" style="display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; border-color: var(--primary);">
        <h3 style="color: var(--text-muted); font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 20px;">Overall Rating</h3>
        <div style="font-size: 72px; font-weight: 800; color: var(--primary); line-height: 1; margin-bottom: 10px;">A-</div>
        <div style="color: var(--text-main); font-weight: 600; font-size: 16px; margin-bottom: 20px;">Top 15% in Department</div>
        <div class="badge badge-success" style="font-size: 12px; padding: 6px 12px;">
            <i class='bx bx-up-arrow-alt'></i> Improved from B+ last quarter
        </div>
    </div>
    
    <!-- Right side: KPI Metrics -->
    <div class="performance-metrics-grid">
        <div class="metric-card blue" style="flex-direction: column; align-items: flex-start; padding: 20px;">
            <div style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; margin-bottom: 10px;">KPI Score</div>
            <h3 style="font-size: 24px; font-weight: 700; color: var(--text-main); margin-bottom: 10px;">92/100</h3>
            <div style="width: 100%; height: 4px; background: rgba(0,0,0,0.05); border-radius: 4px;">
                <div style="width: 92%; height: 100%; background: var(--success); border-radius: 4px;"></div>
            </div>
        </div>
        
        <div class="metric-card amber" style="flex-direction: column; align-items: flex-start; padding: 20px;">
            <div style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; margin-bottom: 10px;">Productivity</div>
            <h3 style="font-size: 24px; font-weight: 700; color: var(--text-main); margin-bottom: 10px;">88%</h3>
            <div style="width: 100%; height: 4px; background: rgba(0,0,0,0.05); border-radius: 4px;">
                <div style="width: 88%; height: 100%; background: var(--primary); border-radius: 4px;"></div>
            </div>
        </div>
        
        <div class="metric-card rose" style="flex-direction: column; align-items: flex-start; padding: 20px;">
            <div style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; margin-bottom: 10px;">Quality Score</div>
            <h3 style="font-size: 24px; font-weight: 700; color: var(--text-main); margin-bottom: 10px;">95%</h3>
            <div style="width: 100%; height: 4px; background: rgba(0,0,0,0.05); border-radius: 4px;">
                <div style="width: 95%; height: 100%; background: var(--info); border-radius: 4px;"></div>
            </div>
        </div>
        
        <div class="metric-card purple" style="flex-direction: column; align-items: flex-start; padding: 20px;">
            <div style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; margin-bottom: 10px;">Teamwork</div>
            <h3 style="font-size: 24px; font-weight: 700; color: var(--text-main); margin-bottom: 10px;">4.8/5</h3>
            <div style="color: var(--warning); font-size: 14px;">
                <i class='bx bxs-star'></i><i class='bx bxs-star'></i><i class='bx bxs-star'></i><i class='bx bxs-star'></i><i class='bx bxs-star-half'></i>
            </div>
        </div>
        
        <div class="metric-card blue" style="flex-direction: column; align-items: flex-start; padding: 20px;">
            <div style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; margin-bottom: 10px;">Communication</div>
            <h3 style="font-size: 24px; font-weight: 700; color: var(--text-main); margin-bottom: 10px;">4.5/5</h3>
            <div style="color: var(--warning); font-size: 14px;">
                <i class='bx bxs-star'></i><i class='bx bxs-star'></i><i class='bx bxs-star'></i><i class='bx bxs-star'></i><i class='bx bxs-star-half'></i>
            </div>
        </div>
        
        <div class="metric-card rose" style="flex-direction: column; align-items: flex-start; padding: 20px;">
            <div style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; margin-bottom: 10px;">Attendance Score</div>
            <h3 style="font-size: 24px; font-weight: 700; color: var(--text-main); margin-bottom: 10px;">98%</h3>
            <div style="width: 100%; height: 4px; background: rgba(0,0,0,0.05); border-radius: 4px;">
                <div style="width: 98%; height: 100%; background: var(--success); border-radius: 4px;"></div>
            </div>
        </div>
    </div>
</div>

<div class="section-grid performance-layout-reverse">
    <!-- Chart -->
    <div class="content-panel">
        <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 class="panel-title">Performance Trend</h3>
            <select class="form-input btn-sm" style="padding: 4px 8px; width: auto; font-size: 12px;">
                <option>2026</option>
                <option>2025</option>
            </select>
        </div>
        <div style="position: relative; height: 300px; width: 100%;">
            <canvas id="performanceChart"></canvas>
        </div>
    </div>

    <!-- Manager Reviews -->
    <div class="content-panel">
        <div class="panel-header">
            <h3 class="panel-title">Manager Reviews</h3>
        </div>
        
        <div style="display: flex; flex-direction: column; gap: 15px;">
            @if($reviews->isEmpty())
                <div style="text-align: center; padding: 40px 0; color: var(--text-muted);">
                    <i class='bx bx-edit' style="font-size: 40px; margin-bottom: 10px; opacity: 0.5;"></i>
                    <p style="margin: 0; font-size: 13px;">No reviews have been submitted yet.</p>
                </div>
            @else
                @foreach($reviews->take(3) as $review)
                <div style="background: var(--bg-main); border: 1px solid var(--border-color); border-radius: 12px; padding: 15px;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                        <div>
                            <div style="font-weight: 600; font-size: 14px; color: var(--text-main);">{{ $review->review_period }}</div>
                            <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">{{ optional($review->reviewer)->name ?? 'Manager' }} • {{ $review->created_at->format('M d, Y') }}</div>
                        </div>
                        <span class="badge badge-{{ $review->performance_score >= 80 ? 'success' : ($review->performance_score >= 50 ? 'warning' : 'danger') }}" style="font-size: 12px;">
                            {{ $review->performance_score }}/100
                        </span>
                    </div>
                    <p style="font-size: 13px; color: var(--text-muted); margin: 0; font-style: italic;">"{{ $review->feedback }}"</p>
                </div>
                @endforeach
                
                @if($reviews->count() > 3)
                    <div style="text-align: center; margin-top: 10px;">
                        <a href="#" style="font-size: 12px; color: var(--primary); text-decoration: none;">View All Reviews</a>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('performanceChart').getContext('2d');
        
        // Custom Light theme defaults for Chart.js
        Chart.defaults.color = '#64748b';
        Chart.defaults.borderColor = '#e2e8f0';

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'KPI Score',
                    data: [75, 78, 80, 85, 82, 88, 90, 92, null, null, null, null],
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#4f46e5',
                },
                {
                    label: 'Task Completion %',
                    data: [80, 85, 85, 90, 85, 92, 95, 94, null, null, null, null],
                    borderColor: '#10b981',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    tension: 0.4,
                    fill: false,
                    pointBackgroundColor: '#10b981',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { usePointStyle: true, boxWidth: 8, color: '#64748b' }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: { borderDash: [2, 4], color: '#e2e8f0' },
                        ticks: { color: '#64748b' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#64748b' }
                    }
                }
            }
        });
    });
</script>
@endsection
