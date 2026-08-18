<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $userId = App\Models\User::first()->id;
    $todayStr = now()->toDateString();
    
    // Simulate Daily Report insert
    Illuminate\Support\Facades\DB::connection('erp_db')->table('daily_reports')->insert([
        'user_id'         => $userId,
        'work_summary'    => 'Test Work Summary',
        'tasks_completed' => 'Test tasks completed',
        'challenges'      => 'No challenges',
        'plan_tomorrow'   => 'Continue working',
        'created_at'      => now(),
    ]);
    echo "Daily Report inserted into erp_db successfully.\n";
    
} catch (\Exception $e) {
    echo "Daily Report insert failed: " . $e->getMessage() . "\n";
}

try {
    // Simulate Attendance insert
    Illuminate\Support\Facades\DB::connection('erp_db')->table('attendance')->insert([
        'user_id'       => $userId,
        'employee_name' => 'Test Employee',
        'date'          => $todayStr,
        'clock_in'      => now()->toTimeString(),
        'status'        => 'Present',
        'image_url'     => null,
    ]);
    echo "Attendance inserted into erp_db successfully.\n";
} catch (\Exception $e) {
    echo "Attendance insert failed: " . $e->getMessage() . "\n";
}
