<?php
$file = 'app/Http/Controllers/AdminController.php';
$content = file_get_contents($file);

$search = "    public function directory(\Illuminate\Http\Request \$request)
    {
        \$query = User::where('role', 'employee')->with('detail');
        if (auth()->user()->role === 'admin') {
        } elseif (auth()->user()->role === 'superadmin' && \$request->filled('company_id')) {
            \$query->where('company_id', \$request->company_id);
        }
            \$query->where('company_id', auth()->user()->company_id);
        }";

$replace = "    public function directory(\Illuminate\Http\Request \$request)
    {
        \$query = User::where('role', 'employee')->with('detail');
        if (auth()->user()->role === 'admin') {
            \$query->where('company_id', auth()->user()->company_id);
        } elseif (auth()->user()->role === 'superadmin' && \$request->filled('company_id')) {
            \$query->where('company_id', \$request->company_id);
        }";

$content = str_replace(str_replace("\r\n", "\n", $search), str_replace("\r\n", "\n", $replace), str_replace("\r\n", "\n", $content));

file_put_contents($file, $content);
echo "Fixed AdminController directory method\n";
