@extends('layouts.app')

@section('content')
<div class="app-header">
    <div class="header-title">
        <h1>Promotions & Awards</h1>
    </div>
    
    <div class="topbar-right">
        <div class="noti-bell">
            <i class='bx bx-bell'></i>
            <span class="noti-bell-badge">1</span>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <div class="user-avatar-placeholder" style="width: 38px; height: 38px; font-size: 14px; ">
                {{ substr(auth()->user()->name, 0, 1) }}
            </div>
            <div style="font-size: 13px; font-weight: 600; color: var(--text-main); text-align: left;">
                <div>{{ auth()->user()->name }}</div>
                <div style="font-size: 10px; color: var(--text-muted); font-weight: normal;">{{ auth()->user()->position ?? auth()->user()->role }}</div>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 24px;">
        <i class='bx bx-check-circle'></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

<div class="section-grid">
    <!-- Left: Recognition Logs List -->
    <div class="content-panel">
        <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <h3 class="panel-title"><i class='bx bx-trophy' style="color: var(--primary);"></i> Workspace Recognition Log</h3>
            <form id="promotionsFilterForm" action="{{ route('admin.promotions') }}" method="GET" style="display: flex; gap: 10px; align-items: center; margin: 0;">
                @if(isset($companies) && in_array(auth()->user()->role, ['superadmin', 'admin']))
                    @php
                        $currentCompanyId = request('company_id') ?: (auth()->user()->role === 'admin' ? auth()->user()->company_id : '');
                    @endphp
                    <x-company-select :companies="$companies" :selected="$currentCompanyId" id="customCompanySelect" name="company_id" padding="8px 14px" width="200px" onchange="document.getElementById('promotionsFilterForm').submit();" />
                @endif
                @php
                    $currentEmployeeId = request('employee_id');
                @endphp
                <x-employee-select :employees="$employees" :selected="$currentEmployeeId" id="customEmployeeSelect" name="employee_id" padding="8px 14px" width="200px" onchange="document.getElementById('promotionsFilterForm').submit();" />
            </form>
        </div>
        
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Type</th>
                        <th>Title / Adjustment</th>
                        <th>Details</th>
                        <th>Date Awarded</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($promotions as $p)
                        <tr style="transition: all 0.2s ease; border-bottom: 1px solid #f1f5f9; background: #fff;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
                            <td style="padding: 16px;">
                                <div style="display: flex; align-items: center; gap: 14px;">
                                    <div style="background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; width: 44px; height: 44px; border-radius: 12px; box-shadow: 0 4px 10px rgba(249, 115, 22, 0.2);">
                                        {{ substr($p->user->name, 0, 2) }}
                                    </div>
                                    <div>
                                        <div style="color: var(--text-main); font-size: 14px; font-weight: 700;">{{ $p->user->name }}</div>
                                        <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                                            <i class='bx bx-wallet' style="color: #94a3b8;"></i> Base: ₹{{ number_format($p->user->salary, 2) }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 16px;">
                                @if($p->type === 'promotion')
                                    <span class="badge badge-success" style="padding: 6px 12px; border-radius: 20px; box-shadow: 0 2px 5px rgba(16, 185, 129, 0.15);"><i class='bx bx-trending-up' style="margin-right: 4px;"></i> Promotion</span>
                                @elseif($p->type === 'salary_hike')
                                    <span class="badge badge-info" style="padding: 6px 12px; border-radius: 20px; box-shadow: 0 2px 5px rgba(59, 130, 246, 0.15);"><i class='bx bx-coin-stack' style="margin-right: 4px;"></i> Salary Hike</span>
                                @elseif($p->type === 'recognition')
                                    <span class="badge badge-warning" style="padding: 6px 12px; border-radius: 20px; box-shadow: 0 2px 5px rgba(245, 158, 11, 0.15);"><i class='bx bx-star' style="margin-right: 4px;"></i> Recognition</span>
                                @else
                                    <span class="badge" style="background: #f1f5f9; color: #475569; padding: 6px 12px; border-radius: 20px;"><i class='bx bx-medal' style="margin-right: 4px;"></i> Appreciation</span>
                                @endif
                            </td>
                            <td style="padding: 16px;">
                                <div style="font-weight: 700; color: var(--text-main); font-size: 14px; margin-bottom: 4px;">{{ $p->title }}</div>
                                @if($p->type === 'salary_hike' && $p->amount)
                                    <div style="color: #16a34a; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; background: #f0fdf4; padding: 2px 8px; border-radius: 4px;"><i class='bx bx-up-arrow-alt'></i> ₹{{ number_format($p->amount, 2) }}</div>
                                @endif
                            </td>
                            <td style="padding: 16px;">
                                <div style="font-size: 13px; color: #64748b; max-width: 280px; line-height: 1.6; background: #f8fafc; padding: 12px 16px; border-radius: 10px; border-left: 3px solid var(--primary);">
                                    {{ $p->detail }}
                                </div>
                            </td>
                            <td style="padding: 16px;">
                                <div style="font-size: 13px; color: var(--text-main); font-weight: 600; display: flex; align-items: center; gap: 6px;">
                                    <div style="width: 32px; height: 32px; border-radius: 8px; background: #f1f5f9; display: flex; align-items: center; justify-content: center;">
                                        <i class='bx bx-calendar' style="color: var(--primary); font-size: 16px;"></i>
                                    </div>
                                    {{ \Carbon\Carbon::parse($p->date_awarded)->format('d M, Y') }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 40px 0;">No awards logged.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Right: Grant Promotion/Recognition Form -->
    <div class="content-panel" style="height: fit-content;">
        <div class="panel-header">
            <h3 class="panel-title"><i class='bx bx-gift' style="color: var(--secondary);"></i> Award Recognition</h3>
        </div>

        <form action="{{ route('admin.promotions.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="user_id" class="form-label">Employee</label>
                <x-employee-select :employees="$employees" id="user_id" name="user_id" placeholder="Select employee..." padding="12px 15px" required="true" />
            </div>

            <div class="form-group">
                <label for="type" class="form-label">Award Type</label>
                <select name="type" id="type" class="form-input custom-input-style" required onchange="toggleAmountInput(this.value)">
                    <option value="recognition" selected>Recognition (e.g. Employee of the Month)</option>
                    <option value="promotion">Promotion (Title / Role upgrade)</option>
                    <option value="salary_hike">Salary Hike (Increments base salary)</option>
                    <option value="appreciation">Appreciation (Formal thank you note)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="title" class="form-label">Recognition Title</label>
                <input type="text" name="title" id="title" class="form-input custom-input-style" placeholder="e.g. Employee of the Month, Lead Developer Upgrade" required>
            </div>

            <div class="form-group" id="amount-field-container" style="display: none;">
                <label for="amount" class="form-label">Salary Hike Amount (Increase per Year)</label>
                <div class="input-with-icon">
                    <span class="input-icon">₹</span>
                    <input type="number" name="amount" id="amount" class="form-input custom-input-style" style="padding-left: 35px;" min="0" step="0.01" placeholder="e.g. 5000.00">
                </div>
            </div>

            <div class="form-group">
                <label for="detail" class="form-label">Details / Accomplishments</label>
                <textarea name="detail" id="detail" class="form-input custom-input-style" placeholder="Write reason for this recognition or description of new job duties..." rows="4"></textarea>
            </div>

            <div class="form-group">
                <label for="date_awarded" class="form-label">Award Date</label>
                <input type="date" name="date_awarded" id="date_awarded" class="form-input custom-input-style" value="{{ date('Y-12-31') > date('Y-m-d') ? date('Y-m-d') : date('Y-12-31') }}" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block submit-award-btn">
                <i class='bx bx-award'></i> Award Recognition
            </button>
        </form>
    </div>
</div>

<script>
    function toggleAmountInput(val) {
        const container = document.getElementById('amount-field-container');
        const input = document.getElementById('amount');
        if (val === 'salary_hike') {
            container.style.display = 'block';
            input.setAttribute('required', 'required');
        } else {
            container.style.display = 'none';
            input.removeAttribute('required');
        }
    }
</script>
@endsection

