@extends('layouts.app')

@section('content')
<div class="app-header">
    <div class="header-title">
        <h1>Employee Directory</h1>
    </div>
    
    <div class="topbar-right">
        <div class="noti-bell">
            <i class='bx bx-bell'></i>
            <span class="noti-bell-badge">1</span>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <div class="user-avatar-placeholder" style="width: 38px; height: 38px; font-size: 14px;">
                {{ substr(auth()->user()->name, 0, 1) }}
            </div>
            <div style="font-size: 13px; font-weight: 600; color: var(--text-main); text-align: left;">
                <div>{{ auth()->user()->name }}</div>
                <div style="font-size: 10px; color: var(--text-muted); font-weight: normal;">{{ auth()->user()->position ?? auth()->user()->role }}</div>
            </div>
        </div>
    </div>
</div>

<div class="welcome-banner" style="padding: 20px 30px; margin-bottom: 30px;">
    <h2 class="welcome-title">Team Directory</h2>
    <p class="welcome-quote">View profiles, contact information, and today's attendance status of all your team members.</p>
</div>

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 24px;">
        <i class='bx bx-check-circle'></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger" style="margin-bottom: 24px;">
        <i class='bx bx-error-circle'></i>
        <span>There were some problems adding the employee. Please check the form.</span>
    </div>
@endif

<div class="content-panel">
    <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <h3 class="panel-title"><i class='bx bxs-contact' style="color: var(--primary);"></i> Workspace Employees</h3>
        
        <div style="display: flex; gap: 10px; align-items: center;">
<style>
.custom-select-wrapper {
    position: relative;
    user-select: none;
    min-width: 240px;
}
.custom-select {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 16px;
    background: #fff;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    color: var(--text-main);
    box-shadow: 0 2px 5px rgba(0,0,0,0.02);
    transition: all 0.2s ease;
}
.custom-select:hover {
    border-color: var(--primary);
}
.custom-select-img {
    width: 26px;
    height: 26px;
    border-radius: 6px;
    object-fit: cover;
    margin-right: 12px;
    border: 1px solid #eee;
}
.custom-select-options {
    position: absolute;
    top: 110%;
    left: 0;
    right: 0;
    background: #fff;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    z-index: 100;
    display: none;
    max-height: 300px;
    overflow-y: auto;
}
.custom-select-options.show {
    display: block;
}
.custom-option {
    display: flex;
    align-items: center;
    padding: 10px 16px;
    cursor: pointer;
    transition: background 0.2s;
    font-size: 14px;
    color: var(--text-main);
}
.custom-option:hover {
    background: var(--bg-main);
}
.custom-option.selected {
    background: #f0f7ff;
    color: var(--primary);
    font-weight: 600;
}
</style>

<form id="companyFilterForm" action="{{ route('admin.directory') }}" method="GET" style="margin: 0;">
    @php
        $currentCompanyId = request('company_id');
        if (!$currentCompanyId && auth()->user()->role === 'admin') {
            $currentCompanyId = auth()->user()->company_id;
        }
    @endphp
    <input type="hidden" name="company_id" id="selectedCompanyId" value="{{ $currentCompanyId }}">
    <div class="custom-select-wrapper" id="customCompanySelect">
        <div class="custom-select" onclick="document.getElementById('companyOptions').classList.toggle('show')">
            <div style="display: flex; align-items: center; font-weight: 500;" id="customSelectLabel">
                @php
                    $selectedComp = $companies->firstWhere('id', $currentCompanyId);
                @endphp
                @if($selectedComp)
                    @if($selectedComp->logo)
                        <img src="{{ asset('storage/'.$selectedComp->logo) }}" class="custom-select-img" alt="logo">
                    @else
                        <div class="custom-select-img" style="background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 12px;">
                            {{ substr($selectedComp->name, 0, 1) }}
                        </div>
                    @endif
                    <span>{{ $selectedComp->name }}</span>
                @else
                    <div class="custom-select-img" style="background: #f1f5f9; color: #64748b; display: flex; align-items: center; justify-content: center; border: none;">
                        <i class='bx bx-buildings' style="font-size: 16px;"></i>
                    </div>
                    <span>All Companies</span>
                @endif
            </div>
            <i class='bx bx-chevron-down' style="font-size: 18px; color: var(--text-muted);"></i>
        </div>
        <div class="custom-select-options" id="companyOptions">
            <div class="custom-option {{ !$currentCompanyId ? 'selected' : '' }}" onclick="selectCompany('', 'All Companies', null, null)">
                <div class="custom-select-img" style="background: #f1f5f9; color: #64748b; display: flex; align-items: center; justify-content: center; border: none;">
                    <i class='bx bx-buildings' style="font-size: 16px;"></i>
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
                        <div class="custom-select-img" style="background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 12px;">
                            {{ $initial }}
                        </div>
                    @endif
                    <span>{{ $company->name }}</span>
                </div>
            @endforeach
        </div>
    </div>
</form>

<script>
function selectCompany(id, name, logoUrl, initial) {
    document.getElementById('selectedCompanyId').value = id;
    
    let html = '';
    if(id === '') {
        html = `<div class="custom-select-img" style="background: #f1f5f9; color: #64748b; display: flex; align-items: center; justify-content: center; border: none;"><i class='bx bx-buildings' style="font-size: 16px;"></i></div><span>${name}</span>`;
    } else {
        if(logoUrl && logoUrl !== 'null' && logoUrl !== '') {
            html = `<img src="${logoUrl}" class="custom-select-img" alt="logo"><span>${name}</span>`;
        } else {
            html = `<div class="custom-select-img" style="background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 12px;">${initial}</div><span>${name}</span>`;
        }
    }
    
    document.getElementById('customSelectLabel').innerHTML = html;
    document.getElementById('companyOptions').classList.remove('show');
    document.getElementById('companyFilterForm').submit();
}

document.addEventListener('click', function(e) {
    let selectWrapper = document.getElementById('customCompanySelect');
    if (selectWrapper && !selectWrapper.contains(e.target)) {
        let options = document.getElementById('companyOptions');
        if (options) options.classList.remove('show');
    }
});
</script>


        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden" style="background: white; border-radius: 12px; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05); border: 1px solid #e5e7eb; margin-top: 20px;">
    <div style="padding: 24px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <h3 style="font-size: 1.25rem; font-weight: 700; color: #172554; margin: 0;">User Directory</h3>
        <div style="display: flex; gap: 16px; align-items: center;">
            <div style="position: relative;">
                <i class='bx bx-search' style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 18px;"></i>
                <input type="text" placeholder="Search employees..." style="padding: 8px 16px 8px 36px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 0.875rem; outline: none; width: 250px;">
            </div>
            <form action="{{ route('admin.directory.sync-erp') }}" method="POST" style="margin: 0;">
                @csrf
                <button type="submit" style="background: #2563eb; color: white; padding: 8px 16px; border-radius: 8px; font-size: 0.875rem; font-weight: 600; border: none; display: flex; align-items: center; gap: 8px; cursor: pointer;" title="Sync employees from the central ERP database">
                    <i class='bx bx-sync' style="font-size: 18px;"></i> Sync ERP Employees
                </button>
            </form>
            <!-- Disabled Add Employee button as per previous requirement to only add in ERP -->
            <button style="background: #ea580c; color: white; padding: 8px 16px; border-radius: 8px; font-size: 0.875rem; font-weight: 600; border: none; display: flex; align-items: center; gap: 8px; opacity: 0.7; cursor: not-allowed;" title="Employees must be added via React ERP">
                <i class='bx bx-plus' style="font-size: 18px;"></i> Add Employee
            </button>
        </div>
    </div>
    
    <div style="overflow-x: auto;">
        <table style="width: 100%; text-align: left; border-collapse: collapse;">
            <thead style="background: #f9fafb; border-bottom: 1px solid #e5e7eb;">
                <tr>
                    <th style="padding: 16px 24px; font-size: 0.75rem; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em;">Name</th>
                    <th style="padding: 16px 24px; font-size: 0.75rem; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em;">Department</th>
                    <th style="padding: 16px 24px; font-size: 0.75rem; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em;">Role</th>
                    <th style="padding: 16px 24px; font-size: 0.75rem; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em;">Status</th>
                    <th style="padding: 16px 24px; font-size: 0.75rem; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody style="border-top: 1px solid #f3f4f6;">
                @forelse($employees as $emp)
                    @php
                        $initials = collect(explode(' ', $emp->name))->map(function($n) { return substr($n, 0, 1); })->take(2)->implode('');
                    @endphp
                    <tr style="border-bottom: 1px solid #f3f4f6; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#f9fafb'" onmouseout="this.style.backgroundColor='transparent'">
                        <td style="padding: 16px 24px;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: #ffedd5; color: #ea580c; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.75rem;">
                                    {{ strtoupper($initials) }}
                                </div>
                                <div>
                                    <p style="margin: 0; font-size: 0.875rem; font-weight: 600; color: #172554;">{{ $emp->name }}</p>
                                    <p style="margin: 0; font-size: 0.75rem; color: #64748b;">{{ $emp->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 16px 24px; font-size: 0.875rem; color: #475569;">
                            {{ $emp->department ?? 'N/A' }}
                        </td>
                        <td style="padding: 16px 24px; font-size: 0.875rem; color: #475569;">
                            {{ $emp->role ?? 'Employee' }}
                        </td>
                        <td style="padding: 16px 24px;">
                            <span style="padding: 4px 8px; border-radius: 9999px; font-size: 0.625rem; font-weight: 700; text-transform: uppercase; background: #dcfce3; color: #16a34a;">
                                ACTIVE
                            </span>
                        </td>
                        <td style="padding: 16px 24px; text-align: right;">
                            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 8px;">
                                <button style="background: transparent; border: none; padding: 8px; color: #9ca3af; cursor: not-allowed; border-radius: 8px;" title="Edit in ERP">
                                    <i class='bx bx-edit-alt' style="font-size: 16px;"></i>
                                </button>
                                <button style="background: transparent; border: none; padding: 8px; color: #9ca3af; cursor: not-allowed; border-radius: 8px;" title="Delete in ERP">
                                    <i class='bx bx-trash' style="font-size: 16px;"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="padding: 32px 24px; text-align: center; color: #64748b;">No employees found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

<!-- Add Employee Modal -->
<div id="add-employee-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div class="content-panel" style="width: 100%; max-width: 500px; padding: 25px; position: relative;">
        <button onclick="document.getElementById('add-employee-modal').style.display='none'" style="position: absolute; right: 20px; top: 20px; background: none; border: none; font-size: 24px; cursor: pointer; color: var(--text-muted);">&times;</button>
        <h3 style="margin-bottom: 20px; color: var(--text-main);">Add New Employee</h3>
        
        <form action="{{ route('admin.directory.store') }}" method="POST">
            @csrf
            <div class="form-group" style="margin-bottom: 15px; display: flex; gap: 15px;">
                <div style="flex: 1;">
                    <label class="form-label" style="color: var(--text-main);">Full Name</label>
                    <input type="text" name="name" required class="form-input" value="{{ old('name') }}">
                    @error('name') <span style="color: var(--danger); font-size: 11px;">{{ $message }}</span> @enderror
                </div>
                <div style="flex: 1;">
                    <label class="form-label" style="color: var(--text-main);">Email Address</label>
                    <input type="email" name="email" required class="form-input" value="{{ old('email') }}">
                    @error('email') <span style="color: var(--danger); font-size: 11px;">{{ $message }}</span> @enderror
                </div>
            </div>
            
            <div class="form-group" style="margin-bottom: 15px; display: flex; gap: 15px;">
                <div style="flex: 1;">
                    <label class="form-label" style="color: var(--text-main);">Password (Min 6 chars)</label>
                    <input type="password" name="password" required class="form-input">
                    @error('password') <span style="color: var(--danger); font-size: 11px;">{{ $message }}</span> @enderror
                </div>
                <div style="flex: 1;">
                    <label class="form-label" style="color: var(--text-main);">Base Salary (₹)</label>
                    <input type="number" name="salary" min="0" step="0.01" class="form-input" value="{{ old('salary') }}">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 15px; display: flex; gap: 15px;">
                <div style="flex: 1;">
                    <label class="form-label" style="color: var(--text-main);">Department</label>
                    <input type="text" name="department" required class="form-input" placeholder="e.g. Development">
                </div>
                <div style="flex: 1;">
                    <label class="form-label" style="color: var(--text-main);">Position</label>
                    <input type="text" name="position" required class="form-input" placeholder="e.g. Junior Dev">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 25px;">
                <label class="form-label" style="color: var(--text-main);">Assign Company</label>
                <x-company-select :companies="$companies" :selected="auth()->user()->company_id" id="directoryAddCompany" name="company_id" placeholder="Select a company..." padding="10px 15px" />
                @error('company_id') <span style="color: var(--danger); font-size: 11px;">{{ $message }}</span> @enderror
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('add-employee-modal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Employee</button>
            </div>
        </form>
    </div>
</div>

@if($errors->any())
<script>
    // Automatically show modal if there are validation errors
    document.addEventListener("DOMContentLoaded", function() {
        document.getElementById('add-employee-modal').style.display='flex';
    });
</script>
@endif

<!-- Modal for showing employee details -->
<div id="detailsModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: var(--bg-main); width: 600px; max-width: 90%; border-radius: 12px; padding: 24px; max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin: 0; font-size: 18px; color: var(--text-main);" id="modalTitle">Employee Details</h3>
            <button type="button" onclick="closeDetailsModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>
        <div id="modalContent">
            Loading...
        </div>
    </div>
</div>

<script>
    const employeesData = @json($employees->load('detail'));
    
    function openDetailsModal(empId) {
        const emp = employeesData.find(e => e.id === empId);
        if(!emp) return;

        document.getElementById('modalTitle').innerText = emp.name + "'s Personal Details";
        
        let content = '';
        if(emp.detail) {
            content = `
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                    <div><strong style="color:var(--text-muted); font-size:12px; display:block;">Father's Name</strong> ${emp.detail.father_name || 'N/A'}</div>
                    <div><strong style="color:var(--text-muted); font-size:12px; display:block;">Mother's Name</strong> ${emp.detail.mother_name || 'N/A'}</div>
                    <div><strong style="color:var(--text-muted); font-size:12px; display:block;">Father's Occupation</strong> ${emp.detail.father_occupation || 'N/A'}</div>
                </div>
                
                <h4 style="margin: 15px 0 10px; border-bottom: 1px solid var(--border-color); padding-bottom: 5px;">Bank Details</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                    <div><strong style="color:var(--text-muted); font-size:12px; display:block;">Bank Name</strong> ${emp.detail.bank_name || 'N/A'}</div>
                    <div><strong style="color:var(--text-muted); font-size:12px; display:block;">Account Number</strong> ${emp.detail.bank_account_no || 'N/A'}</div>
                    <div><strong style="color:var(--text-muted); font-size:12px; display:block;">IFSC Code</strong> ${emp.detail.bank_ifsc || 'N/A'}</div>
                </div>

                <h4 style="margin: 15px 0 10px; border-bottom: 1px solid var(--border-color); padding-bottom: 5px;">Documents</h4>
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <li style="margin-bottom: 8px;"><strong>10th Marksheet:</strong> ${emp.detail.marksheet_10th_path ? '<a href="/storage/'+emp.detail.marksheet_10th_path+'" target="_blank" style="color:var(--primary);">View Document</a>' : 'Not Uploaded'}</li>
                    <li style="margin-bottom: 8px;"><strong>12th Marksheet:</strong> ${emp.detail.marksheet_12th_path ? '<a href="/storage/'+emp.detail.marksheet_12th_path+'" target="_blank" style="color:var(--primary);">View Document</a>' : 'Not Uploaded'}</li>
                    <li style="margin-bottom: 8px;"><strong>Passport Photo:</strong> ${emp.detail.passport_photo_path ? '<a href="/storage/'+emp.detail.passport_photo_path+'" target="_blank" style="color:var(--primary);">View Photo</a>' : 'Not Uploaded'}</li>
                </ul>
            `;
        } else {
            content = '<p style="color:var(--text-muted);">This employee has not filled their personal details yet.</p>';
        }

        document.getElementById('modalContent').innerHTML = content;
        document.getElementById('detailsModal').style.display = 'flex';
    }

    function closeDetailsModal() {
        document.getElementById('detailsModal').style.display = 'none';
    }
</script>
@endsection

