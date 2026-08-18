@extends('layouts.app')

@section('content')
<div class="app-header">
    <div class="header-title">
        <h1>Project Monitoring Dashboard</h1>
    </div>
    
    <div class="topbar-right" style="display: flex; gap: 10px; align-items: center;">
        <form action="{{ route('admin.projects-monitoring.index') }}" method="GET" id="projectFilterForm" style="margin: 0; display: flex; gap: 10px; align-items: center;">
            @if(isset($companies) && in_array(auth()->user()->role, ['superadmin', 'admin']))
                @php
                    $currentCompanyId = request('company_id');
                @endphp
                <input type="hidden" name="company_id" id="selectedCompanyId" value="{{ $currentCompanyId }}">
                <div class="custom-select-wrapper" id="customCompanySelect">
                    <div class="custom-select" style="padding: 6px 12px;" onclick="document.getElementById('companyOptions').classList.toggle('show')">
                        <div style="display: flex; align-items: center; font-weight: 500;" id="customSelectLabel">
                            @php
                                $selectedComp = $companies->firstWhere('id', $currentCompanyId);
                            @endphp
                            @if($selectedComp)
                                @if($selectedComp->logo)
                                    <img src="{{ asset('storage/'.$selectedComp->logo) }}" class="custom-select-img" style="width:20px; height:20px; border-radius:4px;" alt="logo">
                                @else
                                    <div class="custom-select-img" style="width:20px; height:20px; border-radius:4px; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 10px;">
                                        {{ substr($selectedComp->name, 0, 1) }}
                                    </div>
                                @endif
                                <span>{{ $selectedComp->name }}</span>
                            @else
                                <div class="custom-select-img" style="width:20px; height:20px; border-radius:4px; background: #f1f5f9; color: #64748b; display: flex; align-items: center; justify-content: center; border: none;">
                                    <i class='bx bx-buildings' style="font-size: 12px;"></i>
                                </div>
                                <span>All Companies</span>
                            @endif
                        </div>
                        <i class='bx bx-chevron-down' style="font-size: 16px; color: var(--text-muted); margin-left: 8px;"></i>
                    </div>
                    <div class="custom-select-options" id="companyOptions">
                        <div class="custom-option {{ !$currentCompanyId ? 'selected' : '' }}" onclick="selectCompany('', 'All Companies', null, null)">
                            <div class="custom-select-img" style="background: #f1f5f9; color: #64748b; display: flex; align-items: center; justify-content: center; border: none;">
                                <i class='bx bx-buildings' style="font-size: 14px;"></i>
                            </div>
                            <span>All Companies</span>
                        </div>
                        @foreach($companies as $company)
                            @php
                                $logoUrl = $company->logo ? asset('storage/'.$company->logo) : null;
                                $initial = substr($company->name, 0, 1);
                            @endphp
                            <div class="custom-option {{ $currentCompanyId == $company->id ? 'selected' : '' }}" 
                                 onclick="selectCompany('{{ $company->id }}', '{{ addslashes($company->name) }}', '{{ $logoUrl }}', '{{ $initial }}')">
                                @if($company->logo)
                                    <img src="{{ $logoUrl }}" class="custom-select-img" alt="logo">
                                @else
                                    <div class="custom-select-img" style="background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 11px;">
                                        {{ $initial }}
                                    </div>
                                @endif
                                <span>{{ $company->name }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @php
                $currentEmployeeId = request('employee_id');
            @endphp
            <input type="hidden" name="employee_id" id="selectedEmployeeId" value="{{ $currentEmployeeId }}">
            <div class="custom-select-wrapper" id="customEmployeeSelect">
                <div class="custom-select" style="padding: 6px 12px;" onclick="document.getElementById('employeeOptions').classList.toggle('show')">
                    <div style="display: flex; align-items: center; font-weight: 500;" id="customSelectLabelEmployee">
                        @php
                            $selectedEmp = $employees->firstWhere('id', $currentEmployeeId);
                        @endphp
                        @if($selectedEmp)
                            <div class="custom-select-img" style="width:20px; height:20px; border-radius:4px; background: var(--secondary); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 10px;">
                                {{ substr($selectedEmp->name, 0, 2) }}
                            </div>
                            <span>{{ $selectedEmp->name }}</span>
                        @else
                            <div class="custom-select-img" style="width:20px; height:20px; border-radius:4px; background: #f1f5f9; color: #64748b; display: flex; align-items: center; justify-content: center; border: none;">
                                <i class='bx bx-user' style="font-size: 12px;"></i>
                            </div>
                            <span>All Employees</span>
                        @endif
                    </div>
                    <i class='bx bx-chevron-down' style="font-size: 16px; color: var(--text-muted); margin-left: 8px;"></i>
                </div>
                <div class="custom-select-options" id="employeeOptions">
                    <div class="custom-option {{ !$currentEmployeeId ? 'selected' : '' }}" onclick="selectEmployee('', 'All Employees', '')">
                        <div class="custom-select-img" style="background: #f1f5f9; color: #64748b; display: flex; align-items: center; justify-content: center; border: none;">
                            <i class='bx bx-user' style="font-size: 14px;"></i>
                        </div>
                        <span>All Employees</span>
                    </div>
                    @foreach($employees as $e)
                        @php
                            $initial = substr($e->name, 0, 2);
                        @endphp
                        <div class="custom-option {{ $currentEmployeeId == $e->id ? 'selected' : '' }}" 
                             onclick="selectEmployee('{{ $e->id }}', '{{ addslashes($e->name) }}', '{{ $initial }}')">
                            <div class="custom-select-img" style="background: var(--secondary); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 11px;">
                                {{ $initial }}
                            </div>
                            <span>{{ $e->name }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </form>

        <script>
        function selectCompany(id, name, logoUrl, initial) {
            document.getElementById('selectedCompanyId').value = id;
            document.getElementById('projectFilterForm').submit();
        }
        function selectEmployee(id, name, initial) {
            document.getElementById('selectedEmployeeId').value = id;
            document.getElementById('projectFilterForm').submit();
        }
        document.addEventListener('click', function(e) {
            let compSelect = document.getElementById('customCompanySelect');
            if (compSelect && !compSelect.contains(e.target)) {
                let opts = document.getElementById('companyOptions');
                if (opts) opts.classList.remove('show');
            }
            let empSelect = document.getElementById('customEmployeeSelect');
            if (empSelect && !empSelect.contains(e.target)) {
                let opts = document.getElementById('employeeOptions');
                if (opts) opts.classList.remove('show');
            }
        });
        </script>
        <a href="{{ route('admin.projects-monitoring.create') }}" class="btn btn-primary" style="display: flex; align-items: center; gap: 4px; padding: 6px 12px; font-size: 12px; font-weight: 600; height: auto; text-decoration: none;">
            <i class='bx bx-plus' style="font-size: 16px;"></i> Create Project
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 24px; display: flex; align-items: center; gap: 10px; padding: 15px; background: #ecfdf5; color: #065f46; border-radius: 8px; border: 1px solid #a7f3d0;">
        <i class='bx bx-check-circle' style="font-size: 20px;"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

<div class="section-grid" style="grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
    @forelse($projects as $project)
        <div class="content-panel" style="display: flex; flex-direction: column; height: 100%; padding: 16px;">
            <div class="panel-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 10px; margin-bottom: 10px;">
                <h3 class="panel-title" style="font-size: 15px;">
                    <a href="{{ route('admin.projects-monitoring.show', $project->id) }}" style="color: var(--primary); text-decoration: none;">
                        <i class='bx bx-folder-open' style="margin-right: 4px;"></i> {{ $project->title }}
                    </a>
                </h3>
                <span class="badge {{ $project->progress == 100 ? 'badge-success' : 'badge-warning' }}">
                    {{ $project->calculated_status }}
                </span>
            </div>
            
            <div style="flex-grow: 1;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 12px;">
                    <div>
                        <strong style="color: var(--text-muted);">Assigned To:</strong><br>
                        @if($project->users->count() > 0)
                            <span style="display: flex; align-items: center; gap: 6px; margin-top: 5px;">
                                <div class="user-avatar-placeholder" style="width: 20px; height: 20px; font-size: 10px;">
                                    {{ substr($project->users->first()->name, 0, 2) }}
                                </div>
                                <span style="font-weight: 500; color: var(--text-main);">{{ $project->users->first()->name }}</span>
                            </span>
                        @else
                            <span style="color: var(--danger); font-weight: 500; margin-top: 5px; display: block;">Unassigned</span>
                        @endif
                    </div>
                    <div style="text-align: right;">
                        <strong style="color: var(--text-muted);">Deadline:</strong><br>
                        <span style="font-weight: 500; color: var(--text-main); margin-top: 5px; display: block;">
                            {{ $project->deadline ? \Carbon\Carbon::parse($project->deadline)->format('M d, Y') : 'N/A' }}
                        </span>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; text-align: center; margin-bottom: 15px; padding: 10px; background: rgba(0,0,0,0.02); border-radius: 8px;">
                    <div style="flex: 1; border-right: 1px solid var(--border-color);">
                        <div style="font-size: 10px; color: var(--text-muted); text-transform: uppercase; font-weight: 600;">Total Tasks</div>
                        <div style="font-size: 18px; font-weight: 700; color: var(--text-main);">{{ $project->tasks->count() }}</div>
                    </div>
                    <div style="flex: 1; border-right: 1px solid var(--border-color);">
                        <div style="font-size: 10px; color: var(--success); text-transform: uppercase; font-weight: 600;">Completed</div>
                        <div style="font-size: 18px; font-weight: 700; color: var(--success);">{{ $project->tasks->where('status', 'completed')->count() }}</div>
                    </div>
                    <div style="flex: 1;">
                        <div style="font-size: 10px; color: var(--warning); text-transform: uppercase; font-weight: 600;">Pending</div>
                        <div style="font-size: 18px; font-weight: 700; color: var(--warning);">{{ $project->tasks->where('status', 'pending')->count() }}</div>
                    </div>
                </div>

                <div style="margin-bottom: 10px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px; font-weight: 600;">
                        <span style="color: var(--text-muted);">Progress</span>
                        <span style="color: {{ $project->progress == 100 ? 'var(--success)' : 'var(--primary)' }};">{{ $project->progress }}%</span>
                    </div>
                    <div style="height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden;">
                        <div style="height: 100%; width: {{ $project->progress }}%; background: {{ $project->progress == 100 ? 'var(--success)' : 'var(--primary)' }}; border-radius: 4px; transition: width 0.3s ease;"></div>
                    </div>
                </div>
            </div>
            
            <div style="border-top: 1px solid var(--border-color); padding-top: 10px; margin-top: auto; font-size: 11px; color: var(--text-muted); display: flex; align-items: center; justify-content: space-between;">
                <span style="display: flex; align-items: center; gap: 5px;">
                    <i class='bx bx-time-five'></i> {{ $project->last_activity_at ? \Carbon\Carbon::parse($project->last_activity_at)->diffForHumans() : 'Never' }}
                </span>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <a href="{{ route('admin.projects-monitoring.show', $project->id) }}" class="btn btn-outline" style="padding: 3px 8px; font-size: 11px;">View</a>
                    <form action="{{ route('admin.projects-monitoring.destroy', $project->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this project and all its tasks?');" style="margin: 0;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn" style="padding: 4px 10px; font-size: 12px; color: #dc2626; border: 1px solid #fecaca; background: #fef2f2; cursor: pointer; border-radius: 4px;">
                            <i class='bx bx-trash'></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="content-panel" style="grid-column: 1 / -1; text-align: center; padding: 40px;">
            <i class='bx bx-pie-chart-alt-2' style="font-size: 48px; color: var(--text-muted); margin-bottom: 15px;"></i>
            <h3 style="color: var(--text-main); margin-bottom: 10px;">No Projects Found</h3>
            <p style="color: var(--text-muted); margin-bottom: 20px;">Get started by creating a new project and assigning it to an employee.</p>
            <a href="{{ route('admin.projects-monitoring.create') }}" class="btn btn-primary">Create New Project</a>
        </div>
    @endforelse
</div>
@endsection
