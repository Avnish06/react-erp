@extends('layouts.app')

@section('content')
<div class="app-header">
    <div class="header-title">
        <h1>Learning Portal</h1>
        <p>Enhance your skills and grow your career with our curated courses.</p>
    </div>
    
    <div class="topbar-right">
    </div>
</div>

<div class="section-grid" style="grid-template-columns: 2fr 1fr;">
    <!-- Course Grid -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        <h3 style="color: var(--text-main); font-size: 18px; font-weight: 600;">Recommended for You</h3>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
            <!-- Course 1 -->
            <div class="content-panel" style="padding: 0; overflow: hidden; transition: transform 0.2s; cursor: pointer;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='none'">
                <div style="height: 140px; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); display: flex; align-items: center; justify-content: center; position: relative;">
                    <i class='bx bxl-react' style="font-size: 64px; color: rgba(255,255,255,0.2); position: absolute; right: -10px; bottom: -10px;"></i>
                    <i class='bx bx-code-alt' style="font-size: 48px; color: white;"></i>
                </div>
                <div style="padding: 20px;">
                    <span class="badge badge-primary" style="margin-bottom: 10px; display: inline-block;">Technical</span>
                    <h4 style="color: var(--text-main); margin-bottom: 8px; font-size: 16px;">Advanced React Patterns</h4>
                    <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 15px;">Master modern React concepts including Hooks, Context, and Performance optimization.</p>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; font-size: 12px; color: var(--text-muted);">
                        <span><i class='bx bx-time'></i> 4h 30m</span>
                        <span><i class='bx bx-user'></i> Tech Lead</span>
                    </div>
                    
                    <div style="width: 100%; background: rgba(0,0,0,0.1); border-radius: 4px; height: 6px; margin-bottom: 8px;">
                        <div style="width: 45%; background: var(--primary); height: 100%; border-radius: 4px;"></div>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 11px; color: var(--text-muted);">
                        <span>45% Completed</span>
                        <a href="#" style="color: var(--primary); font-weight: 600; text-decoration: none;">Resume</a>
                    </div>
                </div>
            </div>

            <!-- Course 2 -->
            <div class="content-panel" style="padding: 0; overflow: hidden; transition: transform 0.2s; cursor: pointer;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='none'">
                <div style="height: 140px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); display: flex; align-items: center; justify-content: center; position: relative;">
                    <i class='bx bx-conversation' style="font-size: 64px; color: rgba(255,255,255,0.2); position: absolute; right: -10px; bottom: -10px;"></i>
                    <i class='bx bx-group' style="font-size: 48px; color: white;"></i>
                </div>
                <div style="padding: 20px;">
                    <span class="badge badge-success" style="margin-bottom: 10px; display: inline-block;">Soft Skills</span>
                    <h4 style="color: var(--text-main); margin-bottom: 8px; font-size: 16px;">Effective Communication</h4>
                    <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 15px;">Learn how to communicate effectively with stakeholders and cross-functional teams.</p>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; font-size: 12px; color: var(--text-muted);">
                        <span><i class='bx bx-time'></i> 2h 15m</span>
                        <span><i class='bx bx-user'></i> HR Dept</span>
                    </div>
                    
                    <a href="#" class="btn btn-outline btn-block" style="text-align: center;">Start Course</a>
                </div>
            </div>
            
            <!-- Course 3 -->
            <div class="content-panel" style="padding: 0; overflow: hidden; transition: transform 0.2s; cursor: pointer;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='none'">
                <div style="height: 140px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); display: flex; align-items: center; justify-content: center; position: relative;">
                    <i class='bx bx-sitemap' style="font-size: 64px; color: rgba(255,255,255,0.2); position: absolute; right: -10px; bottom: -10px;"></i>
                    <i class='bx bx-bar-chart-alt-2' style="font-size: 48px; color: white;"></i>
                </div>
                <div style="padding: 20px;">
                    <span class="badge badge-warning" style="margin-bottom: 10px; display: inline-block;">Management</span>
                    <h4 style="color: var(--text-main); margin-bottom: 8px; font-size: 16px;">Agile Methodologies</h4>
                    <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 15px;">Deep dive into Scrum, Kanban, and modern agile practices for software delivery.</p>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; font-size: 12px; color: var(--text-muted);">
                        <span><i class='bx bx-time'></i> 5h 00m</span>
                        <span><i class='bx bx-user'></i> PMO</span>
                    </div>
                    
                    <a href="#" class="btn btn-outline btn-block" style="text-align: center;">Start Course</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Learning Stats & Progress -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        <div class="content-panel">
            <div class="panel-header" style="border-bottom: none; padding-bottom: 0;">
                <h3 class="panel-title">Your Learning Journey</h3>
            </div>
            <div style="padding: 20px; text-align: center;">
                <div style="position: relative; width: 120px; height: 120px; margin: 0 auto 20px; border-radius: 50%; border: 8px solid rgba(79, 70, 229, 0.1); display: flex; align-items: center; justify-content: center; border-left-color: var(--primary); transform: rotate(-45deg);">
                    <div style="transform: rotate(45deg);">
                        <div style="font-size: 28px; font-weight: 700; color: var(--text-main);">12</div>
                        <div style="font-size: 11px; color: var(--text-muted); text-transform: uppercase;">Hours</div>
                    </div>
                </div>
                <h4 style="color: var(--text-main); margin-bottom: 5px;">Great progress!</h4>
                <p style="color: var(--text-muted); font-size: 13px;">You have spent 12 hours learning this month. Keep it up to reach your 20-hour goal.</p>
            </div>
        </div>

        <div class="content-panel">
            <div class="panel-header">
                <h3 class="panel-title">Certificates Earned</h3>
            </div>
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <div style="display: flex; gap: 15px; align-items: center;">
                    <div style="width: 40px; height: 40px; border-radius: 8px; background: rgba(16, 185, 129, 0.1); color: var(--success); display: flex; align-items: center; justify-content: center;">
                        <i class='bx bx-certification' style="font-size: 20px;"></i>
                    </div>
                    <div>
                        <div style="font-weight: 600; font-size: 13px; color: var(--text-main);">Laravel Basics</div>
                        <div style="font-size: 11px; color: var(--text-muted);">Issued on Jan 15, 2026</div>
                    </div>
                    <a href="#" style="margin-left: auto; color: var(--primary);"><i class='bx bx-download'></i></a>
                </div>
                <div style="display: flex; gap: 15px; align-items: center;">
                    <div style="width: 40px; height: 40px; border-radius: 8px; background: rgba(245, 158, 11, 0.1); color: var(--warning); display: flex; align-items: center; justify-content: center;">
                        <i class='bx bx-certification' style="font-size: 20px;"></i>
                    </div>
                    <div>
                        <div style="font-weight: 600; font-size: 13px; color: var(--text-main);">UI/UX Principles</div>
                        <div style="font-size: 11px; color: var(--text-muted);">Issued on Dec 02, 2025</div>
                    </div>
                    <a href="#" style="margin-left: auto; color: var(--primary);"><i class='bx bx-download'></i></a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

