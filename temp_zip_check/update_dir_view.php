<?php
$file = 'resources/views/admin/directory.blade.php';
$content = file_get_contents($file);

$search = '<div class="content-panel">
    <div class="panel-header">
        <h3 class="panel-title"><i class=\'bx bxs-contact\' style="color: var(--primary);"></i> Workspace Employees</h3>
        <button class="btn btn-primary btn-sm" onclick="document.getElementById(\'add-employee-modal\').style.display=\'flex\'">
            <i class=\'bx bx-plus\'></i> Add New Employee
        </button>
    </div>';

$replace = '<div class="content-panel">
    <div class="panel-header">
        <h3 class="panel-title"><i class=\'bx bxs-contact\' style="color: var(--primary);"></i> Workspace Employees</h3>
        <div style="display: flex; gap: 10px; align-items: center;">
            @if(auth()->user()->role === \'superadmin\')
                <form method="GET" action="{{ route(\'admin.directory\') }}" style="margin: 0; display: flex; align-items: center; gap: 10px;">
                    <select name="company_id" class="form-input" style="padding: 6px 12px; font-size: 14px; width: auto; border-radius: 6px;" onchange="this.form.submit()">
                        <option value="">All Companies</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ request(\'company_id\') == $company->id ? \'selected\' : \'\' }}>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            @endif
            <button class="btn btn-primary btn-sm" onclick="document.getElementById(\'add-employee-modal\').style.display=\'flex\'">
                <i class=\'bx bx-plus\'></i> Add New Employee
            </button>
        </div>
    </div>';

$content = str_replace(str_replace("\r\n", "\n", $search), str_replace("\r\n", "\n", $replace), str_replace("\r\n", "\n", $content));

file_put_contents($file, $content);
echo "Added dropdown to directory view\n";
