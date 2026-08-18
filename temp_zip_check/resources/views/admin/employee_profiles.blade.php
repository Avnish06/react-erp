@extends('layouts.app')

@section('content')
<div class="app-header">
    <div class="header-title">
        <h1>Employee Profile Status</h1>
        <p>Track which employees have completed their personal details and document uploads.</p>
    </div>
</div>

<div class="content-panel">
    <div class="panel-header">
        <h3 class="panel-title">Employee List</h3>
    </div>
    
    <div class="table-responsive">
        <table class="table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="text-align: left; border-bottom: 2px solid var(--border-color);">
                    <th style="padding: 12px; color: var(--text-muted);">Employee Name</th>
                    <th style="padding: 12px; color: var(--text-muted);">Email</th>
                    <th style="padding: 12px; color: var(--text-muted);">Company</th>
                    <th style="padding: 12px; color: var(--text-muted);">Status</th>
                    <th style="padding: 12px; text-align: right; color: var(--text-muted);">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $emp)
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: 15px 12px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--secondary)); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 14px;">
                                    {{ substr($emp->name, 0, 1) }}
                                </div>
                                <span style="font-weight: 600; color: var(--text-main);">{{ $emp->name }}</span>
                            </div>
                        </td>
                        <td style="padding: 15px 12px; color: var(--text-muted);">{{ $emp->email }}</td>
                        <td style="padding: 15px 12px; color: var(--text-muted);">{{ $emp->company->name ?? 'N/A' }}</td>
                        <td style="padding: 15px 12px;">
                            @if($emp->detail && $emp->detail->bank_account_no)
                                <span class="badge badge-success" style="background: #dcfce7; color: #166534; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">Completed</span>
                            @else
                                <span class="badge badge-warning" style="background: #fef08a; color: #854d0e; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">Pending</span>
                            @endif
                        </td>
                        <td style="padding: 15px 12px; text-align: right;">
                            <button type="button" class="btn btn-sm btn-outline" onclick="openDetailsModal({{ $emp->id }})">View Details</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="padding: 20px; text-align: center; color: var(--text-muted);">No employees found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

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
                    <li style="margin-bottom: 8px;"><strong>Passport Size Photo:</strong> ${emp.detail.passport_photo_path ? '<a href="/storage/'+emp.detail.passport_photo_path+'" target="_blank" style="color:var(--primary);">View Photo</a>' : 'Not Uploaded'}</li>
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
