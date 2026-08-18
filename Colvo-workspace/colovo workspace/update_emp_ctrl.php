<?php
$file = 'app/Http/Controllers/EmployeeController.php';
$content = file_get_contents($file);

$method = '
    public function updateDetails(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            \'father_name\' => \'nullable|string|max:255\',
            \'mother_name\' => \'nullable|string|max:255\',
            \'father_occupation\' => \'nullable|string|max:255\',
            \'bank_name\' => \'nullable|string|max:255\',
            \'bank_account_no\' => \'nullable|string|max:255\',
            \'bank_ifsc\' => \'nullable|string|max:255\',
            \'marksheet_10th\' => \'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048\',
            \'marksheet_12th\' => \'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048\',
            \'passport_photo\' => \'nullable|file|mimes:jpg,jpeg,png|max:2048\',
        ]);

        $detail = $user->detail()->firstOrCreate([\'user_id\' => $user->id]);

        $data = $request->only([
            \'father_name\', \'mother_name\', \'father_occupation\',
            \'bank_name\', \'bank_account_no\', \'bank_ifsc\'
        ]);

        if ($request->hasFile(\'marksheet_10th\')) {
            $data[\'marksheet_10th_path\'] = $request->file(\'marksheet_10th\')->store(\'employee_docs\', \'public\');
        }
        if ($request->hasFile(\'marksheet_12th\')) {
            $data[\'marksheet_12th_path\'] = $request->file(\'marksheet_12th\')->store(\'employee_docs\', \'public\');
        }
        if ($request->hasFile(\'passport_photo\')) {
            $data[\'passport_photo_path\'] = $request->file(\'passport_photo\')->store(\'employee_docs\', \'public\');
        }

        $detail->update($data);

        return redirect()->back()->with(\'success\', \'Personal details updated successfully.\');
    }
';

$search = '    public function updatePassword(Request $request)';
if (strpos($content, $search) !== false) {
    $content = str_replace($search, $method . "\n" . $search, $content);
    file_put_contents($file, $content);
    echo "Updated EmployeeController.php\n";
} else {
    echo "Could not find target in EmployeeController.php\n";
}
