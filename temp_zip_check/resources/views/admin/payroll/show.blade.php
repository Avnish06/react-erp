@extends('layouts.app')

@section('content')
<div class="app-header">
    <div class="header-title">
        <h1>Payslip Details</h1>
    </div>
    <div class="topbar-right">
        <a href="{{ route('admin.payroll.index') }}" class="btn btn-outline">Back to Payrolls</a>
        <button onclick="downloadPDF()" class="btn btn-primary"><i class='bx bx-download'></i> Download PDF</button>
    </div>
</div>

<div class="content-panel" style="max-width: 800px; margin: 0 auto; background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid var(--border-color); padding-bottom: 20px; margin-bottom: 30px;">
        <div>
            <h2 style="margin: 0; color: var(--primary); font-size: 24px;">{{ $payroll->user->company->name ?? 'Colovo Workspace' }}</h2>
            <p style="margin: 5px 0 0 0; color: var(--text-muted); font-size: 14px;">Payslip for the month of <strong>{{ $payroll->month }}</strong></p>
        </div>
        <div style="text-align: right;">
            <div style="font-size: 14px; color: var(--text-muted);">Payslip #{{ str_pad($payroll->id, 5, '0', STR_PAD_LEFT) }}</div>
            <div style="font-size: 14px; color: var(--text-muted);">Generated On: {{ $payroll->created_at->format('M d, Y') }}</div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 40px;">
        <div>
            <h4 style="margin: 0 0 10px 0; color: var(--text-main); border-bottom: 1px solid var(--border-color); padding-bottom: 5px;">Employee Details</h4>
            <table style="width: 100%; font-size: 14px;">
                <tr><td style="padding: 5px 0; color: var(--text-muted); width: 120px;">Employee ID:</td><td style="font-weight: 600; color: var(--primary);">EMP-{{ str_pad($payroll->user_id, 4, '0', STR_PAD_LEFT) }}</td></tr>
                <tr><td style="padding: 5px 0; color: var(--text-muted);">Name:</td><td style="font-weight: 600;">{{ $payroll->user->name }}</td></tr>
                <tr><td style="padding: 5px 0; color: var(--text-muted);">Department:</td><td>{{ $payroll->user->department ?? 'N/A' }}</td></tr>
                <tr><td style="padding: 5px 0; color: var(--text-muted);">Designation:</td><td>{{ ucfirst($payroll->user->position ?? 'N/A') }}</td></tr>
                <tr><td style="padding: 5px 0; color: var(--text-muted);">Joining Date:</td><td>{{ $payroll->user->created_at->format('M d, Y') }}</td></tr>
                <tr><td style="padding: 5px 0; color: var(--text-muted);">Email:</td><td>{{ $payroll->user->email }}</td></tr>
            </table>
        </div>
        <div>
            @php
                $startOfMonth = \Carbon\Carbon::parse($payroll->month)->startOfMonth();
                $endOfMonth = \Carbon\Carbon::parse($payroll->month)->endOfMonth();
                
                $workingDays = 0;
                for ($date = clone $startOfMonth; $date->lte($endOfMonth); $date->addDay()) {
                    if (!$date->isSunday()) {
                        $workingDays++;
                    }
                }

                $fullDays = \App\Models\Attendance::where('user_id', $payroll->user_id)
                    ->whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
                    ->whereIn('status', ['present', 'late'])
                    ->count();

                $halfDays = \App\Models\Attendance::where('user_id', $payroll->user_id)
                    ->whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
                    ->where('status', 'half_day')
                    ->count();
                    
                $presentDays = $fullDays + ($halfDays * 0.5);
                    
                $absentDays = $workingDays > $presentDays ? $workingDays - $presentDays : 0;
            @endphp
            <h4 style="margin: 0 0 10px 0; color: var(--text-main); border-bottom: 1px solid var(--border-color); padding-bottom: 5px;">Salary Information</h4>
            <table style="width: 100%; font-size: 14px;">
                <tr><td style="padding: 5px 0; color: var(--text-muted); width: 120px;">Base Salary:</td><td style="font-weight: 600;">₹{{ number_format($payroll->salary, 2) }}</td></tr>
                <tr><td style="padding: 5px 0; color: var(--text-muted);">Working Days:</td><td>{{ $workingDays }}</td></tr>
                <tr><td style="padding: 5px 0; color: var(--text-muted);">Present:</td><td style="color: var(--success); font-weight: 600;">{{ $presentDays }} Days</td></tr>
                @if($halfDays > 0)
                <tr><td style="padding: 5px 0; color: var(--text-muted);">Half Days:</td><td style="color: var(--warning); font-weight: 600;">{{ $halfDays }}</td></tr>
                @endif
                <tr><td style="padding: 5px 0; color: var(--text-muted);">Absent:</td><td style="color: var(--danger); font-weight: 600;">{{ $absentDays }} Days</td></tr>
                <tr><td style="padding: 5px 0; color: var(--text-muted);">Status:</td><td><span style="color: var(--success); font-weight: 600; text-transform: uppercase;">{{ $payroll->status }}</span></td></tr>
            </table>
        </div>
    </div>

    <h4 style="margin: 0 0 15px 0; color: var(--text-main);">Earnings & Deductions</h4>
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 30px; font-size: 14px;">
        <thead>
            <tr style="background: rgba(0,0,0,0.02);">
                <th style="padding: 12px; text-align: left; border: 1px solid var(--border-color); color: var(--text-muted);">Description</th>
                <th style="padding: 12px; text-align: right; border: 1px solid var(--border-color); color: var(--text-muted); width: 150px;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="padding: 12px; border: 1px solid var(--border-color);">Calculated Salary (Based on Attendance)</td>
                <td style="padding: 12px; text-align: right; border: 1px solid var(--border-color); font-weight: 500;">₹{{ number_format($payroll->net_salary + $payroll->deductions - $payroll->bonus, 2) }}</td>
            </tr>
            @if($payroll->bonus > 0)
            <tr>
                <td style="padding: 12px; border: 1px solid var(--border-color);">Bonus / Allowances</td>
                <td style="padding: 12px; text-align: right; border: 1px solid var(--border-color); color: var(--success); font-weight: 500;">+ ₹{{ number_format($payroll->bonus, 2) }}</td>
            </tr>
            @endif
            @if($payroll->deductions > 0)
            <tr>
                <td style="padding: 12px; border: 1px solid var(--border-color);">Deductions / Penalties</td>
                <td style="padding: 12px; text-align: right; border: 1px solid var(--border-color); color: var(--danger); font-weight: 500;">- ₹{{ number_format($payroll->deductions, 2) }}</td>
            </tr>
            @endif
            <tr style="background: rgba(0,0,0,0.02);">
                <td style="padding: 12px; border: 1px solid var(--border-color); font-weight: 700; text-align: right;">Net Payable Salary</td>
                <td style="padding: 12px; text-align: right; border: 1px solid var(--border-color); font-weight: 700; font-size: 18px; color: var(--primary);">₹{{ number_format($payroll->net_salary, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div style="text-align: center; color: var(--text-muted); font-size: 12px; margin-top: 50px; padding-top: 20px; border-top: 1px dashed var(--border-color);">
        <p>This is a computer-generated document. No signature is required.</p>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    function downloadPDF() {
        const element = document.querySelector('.content-panel');
        const opt = {
            margin:       10,
            filename:     'Payslip_{{ str_replace(" ", "_", $payroll->month) }}.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2, useCORS: true },
            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
        };
        html2pdf().set(opt).from(element).toPdf().get('pdf').then(function (pdf) {
            var blob = pdf.output('blob');
            var url = URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.href = url;
            a.download = opt.filename;
            a.style.display = 'none';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        });
    }
</script>
@endsection
