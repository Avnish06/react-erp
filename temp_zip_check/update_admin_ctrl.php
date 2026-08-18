<?php
$file = 'app/Http/Controllers/AdminController.php';
$content = file_get_contents($file);

$method = '
    public function profileStatus()
    {
        $user = auth()->user();
        $employees = \App\Models\User::where(\'company_id\', $user->company_id)
            ->where(\'role\', \'employee\')
            ->with(\'detail\')
            ->get();
        return view(\'admin.employee_profiles\', compact(\'employees\'));
    }
';

$search = '    public function profile()';
if (strpos($content, $search) !== false) {
    $content = str_replace($search, $method . "\n" . $search, $content);
    file_put_contents($file, $content);
    echo "Updated AdminController.php\n";
} else {
    echo "Could not find target in AdminController.php\n";
}
