<?php
$file = 'app/Http/Controllers/SuperAdminController.php';
$content = file_get_contents($file);

$method = '
    public function profileStatus()
    {
        $employees = \App\Models\User::where(\'role\', \'employee\')
            ->with([\'detail\', \'company\'])
            ->get();
        return view(\'admin.employee_profiles\', compact(\'employees\'));
    }
';

$search = '    public function dashboard()';
if (strpos($content, $search) !== false) {
    $content = str_replace($search, $method . "\n" . $search, $content);
    file_put_contents($file, $content);
    echo "Updated SuperAdminController.php\n";
} else {
    echo "Could not find target in SuperAdminController.php\n";
}
