@extends('layouts.app')

@section('content')

<style>
    .grid-2col-main { display: grid; grid-template-columns: 1fr 1fr; }
    .grid-2col { display: grid; grid-template-columns: 1fr 1fr; }
    .grid-3col { display: grid; grid-template-columns: repeat(3, 1fr); }
    .grid-leave-row { display: grid; grid-template-columns: 1fr 120px 40px; }
    @media (max-width: 992px) {
        .grid-2col-main { grid-template-columns: 1fr; }
    }
    @media (max-width: 768px) {
        .grid-2col { grid-template-columns: 1fr; }
        .grid-3col { grid-template-columns: 1fr; }
        .grid-leave-row { grid-template-columns: 1fr 80px 40px; }
        .app-header { flex-direction: column; align-items: flex-start; gap: 15px; }
        .topbar-right { width: 100%; justify-content: space-between; flex-wrap: wrap; }
    }
</style>


{{-- ── Page Top Bar ─────────────────────────────────────────── --}}
<div class="app-header">
    <div class="header-title">
        <h1><i class='bx bx-file-blank' style="color:var(--primary); margin-right:8px;"></i>Joining Document Generator</h1>
        <p>Create professional, legally-structured employment documents for your employees.</p>
    </div>
    <div class="topbar-right">
        @if(isset($companies) && $companies->count() > 0 && in_array(auth()->user()->role, ['superadmin', 'admin']))
        <div class="workspace-switcher" style="margin-right: 15px;">
            <x-company-select :companies="$companies" :selected="isset($company) ? $company->id : request('company_id')" id="docHeaderCompanySelect" name="company_id" onchange="window.location.href='?company_id='+this.value" padding="6px 12px" width="200px" />
        </div>
        @endif
        <div class="noti-bell">
            <i class='bx bx-bell'></i>
        </div>
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
<div class="welcome-banner" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:20px; padding: 15px 25px;">
    <div>
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
            <span class="welcome-badge" style="margin-bottom: 0;">HR TOOLS</span>
        </div>
        <h2 class="welcome-title" style="font-size: 20px; margin-bottom: 4px;"><i class='bx bx-file-find'></i> Generate Joining Documents</h2>
        <p class="welcome-quote" style="font-size: 13px; margin-bottom: 0;">Fill in the form below to generate offer letters, NDAs, contracts and more — ready to print or share.</p>
        <div style="display:flex; gap:15px; margin-top:8px; font-size:12px; color:#cbd5e1;">
            <span><i class='bx bx-buildings'></i> {{ $company->name ?? 'Your Company' }}</span>
            <span><i class='bx bx-group'></i> {{ $employees->count() }} Employees</span>
            <span><i class='bx bx-file'></i> 6 Document Types</span>
        </div>
    </div>
    <div style="text-align:right;">
        <div style="font-size:12px; color:rgba(255,255,255,0.85);">Today</div>
        <div style="font-size:18px; font-weight:700; color:#fff;">{{ \Carbon\Carbon::now()->format('d M Y') }}</div>
    </div>
</div>

{{-- ── Metric Cards ────────────────────────────────────────── --}}
<div class="metrics-grid">
    <div class="metric-card">
        <div class="metric-card-icon"><i class='bx bx-group'></i></div>
        <div class="metric-card-details">
            <div class="metric-card-title">Total Employees</div>
            <div class="metric-card-value">{{ $employees->count() }}</div>
            <div style="font-size:11px; margin-top:4px; color:var(--text-muted);">Available for documents</div>
        </div>
    </div>
    <div class="metric-card amber">
        <div class="metric-card-icon"><i class='bx bx-file-blank'></i></div>
        <div class="metric-card-details">
            <div class="metric-card-title">Document Types</div>
            <div class="metric-card-value">6</div>
            <div style="font-size:11px; margin-top:4px; color:var(--text-muted);">Offer, NDA, Contract & more</div>
        </div>
    </div>
    <div class="metric-card rose">
        <div class="metric-card-icon"><i class='bx bx-buildings'></i></div>
        <div class="metric-card-details">
            <div class="metric-card-title">Company</div>
            <div class="metric-card-value" style="font-size:18px; margin-top:4px;">{{ $company->name ?? 'N/A' }}</div>
        </div>
    </div>
    <div class="metric-card purple">
        <div class="metric-card-icon"><i class='bx bx-printer'></i></div>
        <div class="metric-card-details">
            <div class="metric-card-title">Print Ready</div>
            <div class="metric-card-value" style="font-size:18px; margin-top:4px;">Instant PDF</div>
            <div style="font-size:11px; margin-top:4px; color:var(--text-muted);">After generation</div>
        </div>
    </div>
</div>

{{-- ── Errors ──────────────────────────────────────────────── --}}
@if($errors->any())
<div class="alert alert-danger" style="margin-bottom:24px;">
    <i class='bx bx-error-circle'></i>
    <div>
        @foreach($errors->all() as $error)
            <p style="margin:0; font-size:13px;">{{ $error }}</p>
        @endforeach
    </div>
</div>
@endif

<form action="{{ route('admin.documents.generate') }}" method="POST" id="doc-form">
@csrf

{{-- ── Two-Column: Employee + Compensation ────────────────── --}}
<div class="grid-2col-main" style="gap:24px; margin-bottom:24px;">

    {{-- LEFT: Employee Details --}}
    <div class="content-panel">
        <div class="panel-header">
            <h3 class="panel-title"><i class='bx bx-user-pin' style="color:var(--primary);"></i> Employee Details</h3>
        </div>

        @if(in_array(auth()->user()->role, ['superadmin', 'admin']))
        <div class="form-group">
            <label class="form-label">Select Company</label>
            <x-company-select :companies="$companies" :selected="isset($company) ? $company->id : request('company_id')" id="company-dropdown" name="company_id" onchange="filterEmployeesByCompany(this.value)" placeholder="Select Company" />
        </div>
        @endif
        <div class="form-group">
            <label class="form-label">Select Employee</label>
            <x-employee-select :employees="$employees" :selected="old('employee_id')" id="employee-dropdown" name="employee_id" placeholder="— Choose Employee —" required="true" />
        </div>

        <div class="grid-2col" style="gap:15px;">
            <div class="form-group">
                <label class="form-label">Designation</label>
                <input type="text" name="designation" id="designation-field" class="form-input"
                       value="{{ old('designation') }}" placeholder="e.g. Senior Developer" required>
            </div>
            <div class="form-group">
                <label class="form-label">Department</label>
                <input type="text" name="department" id="department-field" class="form-input"
                       value="{{ old('department') }}" placeholder="e.g. Engineering" required>
            </div>
        </div>

        <div class="grid-2col" style="gap:15px;">
            <div class="form-group">
                <label class="form-label">Employment Type</label>
                <select name="employment_type" class="form-input" required>
                    <option value="full-time"  {{ old('employment_type','full-time')=='full-time'  ? 'selected' : '' }}>Full-Time</option>
                    <option value="part-time"  {{ old('employment_type')=='part-time'  ? 'selected' : '' }}>Part-Time</option>
                    <option value="contract"   {{ old('employment_type')=='contract'   ? 'selected' : '' }}>Contract</option>
                    <option value="intern"     {{ old('employment_type')=='intern'     ? 'selected' : '' }}>Intern</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Joining Date</label>
                <input type="date" name="start_date" class="form-input"
                       value="{{ old('start_date', date('Y-m-d')) }}" required>
            </div>
        </div>

        <div class="dynamic-group grid-2col" data-docs="offer_letter,appointment_letter,employment_contract" style="gap:15px;">
            <div class="form-group">
                <label class="form-label">Reporting Manager</label>
                <input type="text" name="reporting_manager" class="form-input"
                       value="{{ old('reporting_manager') }}" placeholder="e.g. Jane Smith" required>
            </div>
            <div class="form-group">
                <label class="form-label">Work Location</label>
                <input type="text" name="work_location" class="form-input"
                       value="{{ old('work_location') }}" placeholder="e.g. Moradabad" required>
            </div>
        </div>

        <div class="dynamic-group grid-2col" data-docs="offer_letter,employment_contract,nda,non_compete" style="gap:15px;">
            <div class="form-group">
                <label class="form-label">Probation Period (Months)</label>
                <input type="number" name="probation_period_months" class="form-input"
                       min="0" max="12" value="{{ old('probation_period_months', 3) }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Governing Jurisdiction</label>
                <input type="text" name="jurisdiction" class="form-input"
                       value="{{ old('jurisdiction', 'India') }}" placeholder="e.g. India" required>
            </div>
        </div>
    </div>

    {{-- RIGHT: Compensation & Policy --}}
    <div class="content-panel">
        <div class="panel-header">
            <h3 class="panel-title"><i class='bx bx-rupee' style="color:var(--primary);"></i> Compensation & Policy</h3>
        </div>

        <div class="dynamic-group grid-2col" data-docs="offer_letter,employment_contract" style="gap:15px;">
            <div class="form-group">
                <label class="form-label">Base Salary (Annual)</label>
                <input type="number" name="base_salary" class="form-input"
                       min="0" step="0.01" value="{{ old('base_salary') }}" placeholder="e.g. 600000" required>
            </div>
            <div class="form-group">
                <label class="form-label">Currency</label>
                <select name="currency" class="form-input" required>
                    <option value="INR" {{ old('currency','INR')=='INR' ? 'selected' : '' }}>INR (₹)</option>
                    <option value="USD" {{ old('currency')=='USD' ? 'selected' : '' }}>USD ($)</option>
                    <option value="EUR" {{ old('currency')=='EUR' ? 'selected' : '' }}>EUR (€)</option>
                    <option value="GBP" {{ old('currency')=='GBP' ? 'selected' : '' }}>GBP (£)</option>
                </select>
            </div>
        </div>

        <div class="dynamic-group grid-2col" data-docs="offer_letter,employment_contract" style="gap:15px;">
            <div class="form-group">
                <label class="form-label">Pay Frequency</label>
                <select name="pay_frequency" class="form-input" required>
                    <option value="monthly"    {{ old('pay_frequency','monthly')=='monthly'    ? 'selected' : '' }}>Monthly</option>
                    <option value="bi-monthly" {{ old('pay_frequency')=='bi-monthly' ? 'selected' : '' }}>Bi-Monthly</option>
                    <option value="weekly"     {{ old('pay_frequency')=='weekly'     ? 'selected' : '' }}>Weekly</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Notice Period (Days)</label>
                <input type="number" name="notice_period_days" class="form-input"
                       min="0" value="{{ old('notice_period_days', 30) }}" required>
            </div>
        </div>

        <div class="form-group dynamic-group" data-docs="offer_letter,employment_contract">
            <label class="form-label">Bonus Structure <span style="color:var(--text-muted); font-weight:400;">(Optional)</span></label>
            <input type="text" name="bonus_structure" class="form-input"
                   value="{{ old('bonus_structure') }}" placeholder="e.g. Annual performance bonus up to 15%">
        </div>

        <div class="dynamic-group grid-2col" data-docs="non_compete,nda,employment_contract" style="gap:15px;">
            <div class="form-group">
                <label class="form-label">Non-Compete Duration (Months)</label>
                <input type="number" name="non_compete_duration_months" class="form-input"
                       min="0" value="{{ old('non_compete_duration_months', 12) }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Confidentiality Duration (Years)</label>
                <input type="number" name="confidentiality_duration_years" class="form-input"
                       min="1" value="{{ old('confidentiality_duration_years', 2) }}" required>
            </div>
        </div>

        <div class="form-group dynamic-group" data-docs="non_compete,employment_contract">
            <label class="form-label">Non-Compete Geographic Scope</label>
            <input type="text" name="non_compete_geographic_scope" class="form-input"
                   value="{{ old('non_compete_geographic_scope') }}" placeholder="e.g. India, South Asia" required>
        </div>

        <div class="grid-2col" style="gap:15px;">
            <div class="form-group">
                <label class="form-label">Signatory Name</label>
                <input type="text" name="signatory_name" class="form-input"
                       value="{{ old('signatory_name') }}" placeholder="e.g. Rahul Sharma" required>
            </div>
            <div class="form-group">
                <label class="form-label">Signatory Title</label>
                <input type="text" name="signatory_title" class="form-input"
                       value="{{ old('signatory_title') }}" placeholder="e.g. CEO" required>
            </div>
        </div>
    </div>
</div>

{{-- ── Document Type Selection ─────────────────────────────── --}}
<div class="content-panel" style="margin-bottom:24px;">
    <div class="panel-header" style="justify-content:space-between;">
        <h3 class="panel-title"><i class='bx bx-file' style="color:var(--primary);"></i> Select Documents to Generate</h3>
        <div style="display:flex; gap:8px;">
            <button type="button" class="btn btn-sm btn-outline" onclick="selectAllDocs()"><i class='bx bx-check-double'></i> All</button>
            <button type="button" class="btn btn-sm btn-outline" onclick="clearAllDocs()"><i class='bx bx-x'></i> Clear</button>
        </div>
    </div>

    <div class="grid-3col" style="gap:14px; padding:10px 0;">
        @php
            $docTypes = [
                'offer_letter'        => ['icon' => 'bx-envelope-open',  'label' => 'Offer Letter',           'desc' => 'Position & salary offer'],
                'nda'                 => ['icon' => 'bx-lock-alt',        'label' => 'Non-Disclosure (NDA)',   'desc' => 'Confidentiality agreement'],
                'appointment_letter'  => ['icon' => 'bx-id-card',         'label' => 'Appointment Letter',     'desc' => 'Confirms appointment'],
                'employment_contract' => ['icon' => 'bx-file-blank',      'label' => 'Employment Contract',    'desc' => 'Full terms of employment'],
                'non_compete'         => ['icon' => 'bx-block',           'label' => 'Non-Compete Agreement',  'desc' => 'Post-employment restriction'],
                'leave_policy'        => ['icon' => 'bx-calendar-check',  'label' => 'Leave Policy',           'desc' => 'Leave entitlements'],
            ];
        @endphp
        @foreach($docTypes as $key => $doc)
        <label id="label-{{ $key }}" style="display:flex; align-items:center; gap:12px; padding:16px 18px; border:2px solid var(--border-color); border-radius:14px; cursor:pointer; transition:all 0.2s; background:var(--bg-main);"
               onmouseover="if(!this.querySelector('input').checked){this.style.borderColor='var(--primary)'; this.style.background='var(--primary-glow)';}"
               onmouseout="if(!this.querySelector('input').checked){this.style.borderColor='var(--border-color)'; this.style.background='var(--bg-main)';}">
            <input type="checkbox" name="documents[]" value="{{ $key }}"
                   style="width:18px; height:18px; accent-color:var(--primary); flex-shrink:0;"
                   {{ in_array($key, old('documents', [])) ? 'checked' : '' }}
                   onchange="updateDocLabel(this, '{{ $key }}')">
            <div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <i class='bx {{ $doc["icon"] }}' style="font-size:20px; color:var(--primary);"></i>
                    <span style="font-weight:600; font-size:13px; color:var(--text-main);">{{ $doc['label'] }}</span>
                </div>
                <div style="font-size:11px; color:var(--text-muted); margin-top:2px; margin-left:28px;">{{ $doc['desc'] }}</div>
            </div>
        </label>
        @endforeach
    </div>
</div>

{{-- ── Leave Types ─────────────────────────────────────────── --}}
<div class="content-panel dynamic-group" data-docs="leave_policy" style="margin-bottom:24px;">
    <div class="panel-header" style="justify-content:space-between;">
        <h3 class="panel-title">
            <i class='bx bx-calendar' style="color:var(--primary);"></i> Leave Types
            <span style="font-size:11px; font-weight:400; color:var(--text-muted); margin-left:6px;">(for Leave Policy document)</span>
        </h3>
        <button type="button" class="btn btn-sm btn-outline" onclick="addLeaveRow()"><i class='bx bx-plus'></i> Add Row</button>
    </div>

    <div style="padding:0 0 4px;">
        <div class="grid-leave-row" style="gap:12px; padding:8px 0; border-bottom:1px solid var(--border-color); margin-bottom:10px;">
            <span style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.4px;">Leave Type Name</span>
            <span style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.4px;">Days / Year</span>
            <span></span>
        </div>
        <div id="leave-types-container">
            <div class="leave-row grid-leave-row" style="gap:12px; align-items:center; margin-bottom:10px;">
                <input type="text" name="leave_types[0][name]" class="form-input" placeholder="e.g. Annual Leave">
                <input type="number" name="leave_types[0][days]" class="form-input" min="0" placeholder="e.g. 21">
                <button type="button" onclick="this.closest('.leave-row').remove()"
                    style="width:38px; height:42px; background:var(--danger-bg); color:var(--danger); border:1px solid var(--danger); border-radius:8px; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:16px; transition:all 0.2s;"
                    onmouseover="this.style.background='var(--danger)'; this.style.color='#fff';"
                    onmouseout="this.style.background='var(--danger-bg)'; this.style.color='var(--danger)';">
                    <i class='bx bx-trash'></i>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── Action Buttons ───────────────────────────────────────── --}}
<div class="content-panel" style="margin-bottom:28px;">
    <div style="display:flex; align-items:center; justify-content:space-between; gap:16px;">
        <div style="display:flex; align-items:center; gap:8px; color:var(--text-muted); font-size:13px;">
            <i class='bx bx-info-circle' style="color:var(--primary); font-size:18px;"></i>
            Select at least one document type and fill all required fields.
        </div>
        <div style="display:flex; gap:12px;">
            <button type="button" class="btn btn-outline" onclick="resetDocumentForm()">
                <i class='bx bx-refresh'></i> Reset Form
            </button>
            <button type="submit" class="btn btn-primary" style="padding:12px 32px; font-size:15px;">
                <i class='bx bx-file-find' style="margin-right:6px;"></i> Generate Joining Letter
            </button>
        </div>
    </div>
</div>

</form>

<script>
    function resetDocumentForm() {
        document.getElementById('doc-form').reset();
        setTimeout(() => {
            document.querySelectorAll('input[name="documents[]"]').forEach(cb => updateDocLabel(cb, cb.value));
            updateDynamicFields();
        }, 50);
    }

let leaveRowIndex = 1;

function addLeaveRow() {
    const container = document.getElementById('leave-types-container');
    const row = document.createElement('div');
    row.className = 'leave-row';
    row.className = 'leave-row grid-leave-row';
      row.style = 'gap:12px; align-items:center; margin-bottom:10px;';
    row.innerHTML = `
        <input type="text" name="leave_types[${leaveRowIndex}][name]" class="form-input" placeholder="e.g. Sick Leave">
        <input type="number" name="leave_types[${leaveRowIndex}][days]" class="form-input" min="0" placeholder="e.g. 12">
        <button type="button" onclick="this.closest('.leave-row').remove()"
            style="width:38px; height:42px; background:var(--danger-bg); color:var(--danger); border:1px solid var(--danger); border-radius:8px; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:16px; transition:all 0.2s;"
            onmouseover="this.style.background='var(--danger)'; this.style.color='#fff';"
            onmouseout="this.style.background='var(--danger-bg)'; this.style.color='var(--danger)';">
            <i class='bx bx-trash'></i>
        </button>`;
    container.appendChild(row);
    leaveRowIndex++;
}

function selectAllDocs() {
    document.querySelectorAll('input[name="documents[]"]').forEach(cb => {
        cb.checked = true;
        updateDocLabel(cb, cb.value);
    });
}
function clearAllDocs() {
    document.querySelectorAll('input[name="documents[]"]').forEach(cb => {
        cb.checked = false;
        updateDocLabel(cb, cb.value);
    });
}
function updateDocLabel(input, key) {
    updateDynamicFields();
    const label = document.getElementById('label-' + key);
    if (input.checked) {
        label.style.borderColor = 'var(--primary)';
        label.style.background  = 'var(--primary-glow)';
    } else {
        label.style.borderColor = 'var(--border-color)';
        label.style.background  = 'var(--bg-main)';
    }
}

);
}

);
}


// Dynamic form fields logic
function updateDynamicFields() {
    const checkedDocs = Array.from(document.querySelectorAll('input[name="documents[]"]:checked')).map(cb => cb.value);
    const groups = document.querySelectorAll('.dynamic-group');
    
    groups.forEach(group => {
        const requiredDocs = group.getAttribute('data-docs').split(',');
        const shouldShow = requiredDocs.some(doc => checkedDocs.includes(doc));
        if (shouldShow) {
            group.style.display = group.classList.contains('form-group') ? 'block' : (group.style.gridTemplateColumns ? 'grid' : 'block');
            
            // Re-enable required attributes
            group.querySelectorAll('input, select').forEach(input => {
                if (input.hasAttribute('data-required')) {
                    input.setAttribute('required', 'required');
                }
            });
        } else {
            group.style.display = 'none';
            
            // Disable required attributes so form can submit
            group.querySelectorAll('input, select').forEach(input => {
                if (input.hasAttribute('required')) {
                    input.setAttribute('data-required', 'true');
                    input.removeAttribute('required');
                }
            });
        }
    });
}

document.querySelectorAll('input[name="documents[]"]').forEach(cb => {
    cb.addEventListener('change', updateDynamicFields);
});
updateDynamicFields();


// Filter employees by company
const allEmployees = @json(isset($allEmployees) ? $allEmployees : []);
function filterEmployeesByCompany(companyId) {
    const optionsContainer = document.getElementById('employee-dropdown_options');
    if(!optionsContainer) return;
    optionsContainer.innerHTML = '';
    
    // Add default
    let defaultOpt = document.createElement('div');
    defaultOpt.className = 'custom-option';
    defaultOpt.innerHTML = `<div class="custom-select-img" style="width:20px; height:20px; margin-right: 8px; border-radius:4px; background: #f1f5f9; color: #64748b; display: flex; align-items: center; justify-content: center; border: none;">
                                <i class='bx bx-user' style="font-size: 14px;"></i>
                            </div>
                            <span>— Choose Employee —</span>`;
    defaultOpt.onclick = function() { selectEmployeeComponent_employee_dropdown('', '— Choose Employee —', ''); };
    optionsContainer.appendChild(defaultOpt);

    allEmployees.forEach(emp => {
        if (emp.company_id == companyId) {
            const initial = emp.name.substring(0, 2);
            let opt = document.createElement('div');
            opt.className = 'custom-option';
            opt.innerHTML = `<div class="custom-select-img" style="width:20px; height:20px; border-radius:4px; margin-right: 8px; background: var(--secondary); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 11px;">
                                ${initial}
                            </div>
                            <span>${emp.name}</span>`;
            opt.onclick = function() { selectEmployeeComponent_employee_dropdown(emp.id, emp.name, initial); };
            optionsContainer.appendChild(opt);
        }
    });

    // Reset selection
    if (typeof selectEmployeeComponent_employee_dropdown === 'function') {
        selectEmployeeComponent_employee_dropdown('', '— Choose Employee —', '');
    }
    
    // Clear designation/department fields
    document.getElementById('department-field').value = '';
    document.getElementById('designation-field').value = '';
}

// Auto-fill fields from employee selection
document.querySelector('input[name="employee_id"]').addEventListener('change', function() {
    const empId = this.value;
    if (!empId) return;

    // allEmployees is an array, let's find the matching employee
    const emp = allEmployees.find(e => e.id == empId);
    if (!emp) return;

    // Helper to set value if field exists and value is not null/empty
    const setVal = (name, val) => {
        const field = document.querySelector('[name="' + name + '"]');
        if (field && val !== null && val !== '') {
            field.value = val;
        }
    };

    setVal('department', emp.department);
    setVal('designation', emp.position);
    setVal('employment_type', emp.employment_type);
    setVal('reporting_manager', emp.reporting_manager);
    setVal('work_location', emp.work_location);
    setVal('base_salary', emp.salary);
    setVal('currency', emp.currency);
    setVal('pay_frequency', emp.pay_frequency);
    setVal('bonus_structure', emp.bonus_structure);
    setVal('probation_period_months', emp.probation_period_months);
    setVal('notice_period_days', emp.notice_period_days);
    setVal('non_compete_duration_months', emp.non_compete_duration_months);
    setVal('confidentiality_duration_years', emp.confidentiality_duration_years);
    setVal('non_compete_geographic_scope', emp.non_compete_geographic_scope);
});

// Re-apply checked styles on page load (for old() values)
document.querySelectorAll('input[name="documents[]"]:checked').forEach(cb => updateDocLabel(cb, cb.value));
</script>

@endsection
