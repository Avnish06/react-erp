@extends('layouts.app')

@section('content')
<div class="app-header">
    <div class="header-title">
        <h1>Global Workspaces</h1>
    </div>
    <div class="topbar-right" style="display: flex; align-items: center; gap: 20px;">
        <form method="GET" action="{{ route('superadmin.workspaces') }}" style="display: flex; align-items: center; gap: 10px;" id="filterForm">
            <label style="font-size: 13px; color: var(--text-muted); font-weight: 500;">Filter by Workspace:</label>
            
            <div class="custom-select-wrapper" style="position: relative; min-width: 250px;">
                @php
                    $selectedCompany = $allCompanies->firstWhere('id', request('company_id'));
                @endphp
                <div class="custom-select-trigger" onclick="document.getElementById('custom-options-companies').classList.toggle('show')" style="height: 38px; padding: 6px 12px; border: 1px solid var(--border-color); border-radius: 6px; background: white; display: flex; align-items: center; justify-content: space-between; cursor: pointer;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        @if($selectedCompany && $selectedCompany->logo)
                            <img src="{{ asset('storage/' . $selectedCompany->logo) }}" alt="Logo" style="width: 20px; height: 20px; border-radius: 4px; object-fit: cover;">
                        @elseif($selectedCompany)
                            <div style="width: 20px; height: 20px;  border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: bold;">
                                {{ substr($selectedCompany->name, 0, 1) }}
                            </div>
                        @else
                            <i class='bx bx-buildings' style="font-size: 18px; color: var(--text-muted);"></i>
                        @endif
                        <span style="font-size: 14px; color: var(--text-main);">{{ $selectedCompany ? $selectedCompany->name : 'Global Workspaces' }}</span>
                    </div>
                    <i class='bx bx-chevron-down' style="color: var(--text-muted);"></i>
                </div>
                
                <div id="custom-options-companies" class="custom-options" style="position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid var(--border-color); border-radius: 6px; margin-top: 5px; z-index: 100; max-height: 300px; overflow-y: auto; display: none; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                    @foreach($allCompanies as $c)
                        <a href="{{ route('superadmin.workspaces', ['company_id' => $c->id]) }}" style="display: flex; align-items: center; gap: 8px; padding: 10px 12px; text-decoration: none; color: var(--text-main); border-bottom: 1px solid var(--border-color);">
                            @if($c->logo)
                                <img src="{{ asset('storage/' . $c->logo) }}" alt="Logo" style="width: 20px; height: 20px; border-radius: 4px; object-fit: cover;">
                            @else
                                <div style="width: 20px; height: 20px;  border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: bold;">
                                    {{ substr($c->name, 0, 1) }}
                                </div>
                            @endif
                            <span style="font-size: 14px;">{{ $c->name }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            @if(request('company_id'))
                <a href="{{ route('superadmin.workspaces') }}" class="btn btn-sm btn-outline" style="height: 38px; display: flex; align-items: center; padding: 0 10px;">Clear</a>
            @endif
        </form>

        <script>
            document.addEventListener('click', function(event) {
                if (!event.target.closest('.custom-select-wrapper')) {
                    const el = document.getElementById('custom-options-companies');
                    if(el) el.classList.remove('show');
                }
            });
        </script>
        <style>
            .custom-options.show { display: block !important; }
            .custom-options a:hover { background: var(--bg-main); }
        </style>

        <div style="display: flex; align-items: center; gap: 10px; padding-left: 20px; border-left: 1px solid var(--border-color);">
            <div class="user-avatar-placeholder" style="width: 38px; height: 38px; font-size: 14px; ">
                {{ substr(auth()->user()->name, 0, 1) }}
            </div>
            <div style="font-size: 13px; font-weight: 600; color: var(--text-main); text-align: left;">
                <div>{{ auth()->user()->name }}</div>
                <div style="font-size: 10px; color: var(--text-muted); font-weight: normal;">{{ auth()->user()->role }}</div>
            </div>
        </div>
    </div>
</div>

<div class="welcome-banner" style="padding: 20px 30px; margin-bottom: 30px;">
    <h2 class="welcome-title">Global Workspaces</h2>
    <p class="welcome-quote">Manage all company and workspace instances across the platform.</p>
</div>

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 24px;">
        <i class='bx bx-check-circle'></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

<div class="content-panel">
    <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <div style="display: flex; align-items: center; gap: 15px;">
            <h3 class="panel-title" style="margin: 0;"><i class='bx bx-buildings' style="color: var(--primary);"></i> Workspace Directory</h3>
        </div>
    
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Workspace Name</th>
                    <th>Users</th>
                    <th>Projects</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($companies as $company)
                <tr>
                    <td>#{{ $company->id }}</td>
                    <td>
                        <a href="{{ route('superadmin.workspaces.show', $company->id) }}" style="text-decoration: none; display: flex; align-items: center; gap: 10px;">
                            @if($company->logo)
                                <img src="{{ asset('storage/' . $company->logo) }}" alt="Logo" style="width: 32px; height: 32px; border-radius: 4px; object-fit: cover;">
                            @else
                                <div class="user-avatar-placeholder" style="width: 32px; height: 32px; font-size: 14px;  border-radius: 4px; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                                    {{ substr($company->name, 0, 1) }}
                                </div>
                            @endif
                            <div>
                                <div style="font-weight: 600; color: var(--primary);">{{ $company->name }} <i class='bx bx-link-external' style="font-size: 12px;"></i></div>
                                <div style="font-size: 12px; color: var(--text-muted);">{{ $company->email ?? 'No email' }}</div>
                            </div>
                        </a>
                    </td>
                    <td>{{ $company->users_count }}</td>
                    <td>{{ $company->projects_count }}</td>
                    <td>
                        <span class="status-badge {{ $company->status === 'active' ? 'status-approved' : 'status-rejected' }}">
                            {{ ucfirst($company->status) }}
                        </span>
                    </td>
                    <td style="display: flex; gap: 8px; align-items: center;">
                        <form action="{{ route('superadmin.workspaces.toggle', $company->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-outline" style="border-color: {{ $company->status === 'active' ? 'var(--danger)' : 'var(--success)' }}; color: {{ $company->status === 'active' ? 'var(--danger)' : 'var(--success)' }};">
                                {{ $company->status === 'active' ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>

                        <button type="button" class="btn-action-edit" title="Edit Workspace" onclick="document.getElementById('edit-modal-{{ $company->id }}').showModal()">
                            <i class='bx bx-edit'></i>
                        </button>
                        
                        <dialog id="edit-modal-{{ $company->id }}" style="padding: 20px; border-radius: 8px; border: 1px solid var(--border-color); width: 400px; margin: auto; max-width: 90vw;">
                            <h3 style="margin-bottom: 15px;">Edit Workspace</h3>
                            <form action="{{ route('superadmin.workspaces.update', $company->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div style="margin-bottom: 15px; text-align: left;">
                                    <label style="display: block; font-size: 13px; font-weight: 500; color: var(--text-muted); margin-bottom: 8px;">Workspace Name</label>
                                    <input type="text" name="name" class="form-control" value="{{ $company->name }}" required>
                                </div>
                                <div style="margin-bottom: 15px; text-align: left;">
                                    <label style="display: block; font-size: 13px; font-weight: 500; color: var(--text-muted); margin-bottom: 8px;">Primary Email</label>
                                    <input type="email" name="email" class="form-control" value="{{ $company->email }}">
                                </div>
                                <div style="margin-bottom: 15px; text-align: left;">
                                    <label style="display: block; font-size: 13px; font-weight: 500; color: var(--text-muted); margin-bottom: 8px;">Company Logo (leave blank to keep current)</label>
                                    <input type="file" name="logo" class="form-control" accept="image/*" style="padding: 5px;">
                                </div>
                                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                                    <button type="button" class="btn btn-sm btn-outline" onclick="document.getElementById('edit-modal-{{ $company->id }}').close()">Cancel</button>
                                    <button type="submit" class="btn btn-sm btn-primary">Save Changes</button>
                                </div>
                            </form>
                        </dialog>

                        <form action="{{ route('superadmin.workspaces.delete', $company->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this workspace? This action cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-action-delete" title="Delete Workspace">
                                <i class='bx bx-trash'></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<dialog id="create-workspace-modal" style="padding: 20px; border-radius: 8px; border: 1px solid var(--border-color); width: 400px; margin: auto; max-width: 90vw;">
    <h3 style="margin-bottom: 15px;"><i class='bx bx-plus-circle' style="color: var(--primary);"></i> Create New Workspace</h3>
    <form action="{{ route('superadmin.workspaces.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div style="margin-bottom: 15px; text-align: left;">
            <label style="display: block; font-size: 13px; font-weight: 500; color: var(--text-muted); margin-bottom: 8px;">Workspace Name</label>
            <input type="text" name="name" class="form-control" placeholder="e.g. Colovo Europe" required>
        </div>
        <div style="margin-bottom: 15px; text-align: left;">
            <label style="display: block; font-size: 13px; font-weight: 500; color: var(--text-muted); margin-bottom: 8px;">Primary Email</label>
            <input type="email" name="email" class="form-control" placeholder="contact@colovo.eu">
        </div>
        <div style="margin-bottom: 15px; text-align: left;">
            <label style="display: block; font-size: 13px; font-weight: 500; color: var(--text-muted); margin-bottom: 8px;">Company Logo</label>
            <input type="file" name="logo" class="form-control" accept="image/*" style="padding: 5px;">
        </div>
        <div style="display: flex; gap: 10px; justify-content: flex-end;">
            <button type="button" class="btn btn-sm btn-outline" onclick="document.getElementById('create-workspace-modal').close()">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary">Create Workspace</button>
        </div>
    </form>
</dialog>

@endsection
