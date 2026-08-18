<?php

$modelsDir = __DIR__ . '/app/Models';
$models = ['Project.php', 'Task.php', 'Attendance.php', 'FinancialRecord.php', 'PerformanceReview.php', 'PromotionRecognition.php', 'Leave.php', 'Payroll.php'];

foreach ($models as $modelFile) {
    $path = $modelsDir . '/' . $modelFile;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        
        // Add namespace import if not exists
        if (strpos($content, 'use App\Models\Traits\HasCompany;') === false) {
            $content = preg_replace('/(use Illuminate\\\\Database\\\\Eloquent\\\\Model;)/', "$1\nuse App\Models\Traits\HasCompany;", $content);
        }
        
        // Add trait use if not exists
        if (strpos($content, 'use HasCompany;') === false) {
            $content = preg_replace('/(class\s+[a-zA-Z0-9_]+\s+extends\s+Model\s*\{)/', "$1\n    use HasCompany;", $content);
        }
        
        file_put_contents($path, $content);
        echo "Applied HasCompany to $modelFile\n";
    }
}
