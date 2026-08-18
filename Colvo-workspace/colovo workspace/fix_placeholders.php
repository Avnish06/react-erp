<?php
$file = 'app/Http/Controllers/DocumentController.php';
$content = file_get_contents($file);

// Replace {{placeholder}} with {$d['placeholder']} in the text
$content = preg_replace('/\{\{([a-zA-Z0-9_]+)\}\}/', '{$d[\'$1\']}', $content);

// We need to inject default values for all these placeholders into each method before $content = <<<MD
$defaults = [
    'company_name' => 'Your Company',
    'company_address' => 'Plot 93, Rajendra Park, Sector 105, Gurugram – 122001. Haryana',
    'employee_address' => 'Employee Address',
    'benefits_description' => 'Standard company benefits as per policy',
    'dispute_negotiation_days' => 30,
    'dispute_resolution_mechanism' => 'Binding Arbitration',
    'competitor_1' => 'Competitor A',
    'sector_1' => 'Technology',
    'competitor_2' => 'Competitor B',
    'sector_2' => 'Software',
    'competitor_3' => 'Competitor C',
    'sector_3' => 'IT Services',
    'leave_type_name' => 'Annual Leave',
    'days_per_year' => 20,
    'remarks' => 'Subject to approval',
    'annual_leave_days' => 20,
    'sick_leave_days' => 10,
    'casual_leave_days' => 5,
    'accrual_method' => 'monthly',
    'leave_application_notice_days' => 14,
    'sick_leave_doc_threshold' => 3,
    'max_carry_forward_days' => 5,
    'employee_work_location' => 'Office',
    'max_unpaid_leave_days' => 30,
    'maternity_leave_days' => 12,
    'paternity_leave_days' => 5,
    'bereavement_leave_days' => 3,
    'policy_review_frequency' => 'annually',
    'bonus_structure' => 'As per company policy',
];

$mergeStr = "        \$d = array_merge([\n";
foreach ($defaults as $k => $v) {
    if (is_int($v)) {
        $mergeStr .= "            '$k' => $v,\n";
    } else {
        $mergeStr .= "            '$k' => '$v',\n";
    }
}
$mergeStr .= "        ], \$d);\n";

// Insert it before $content = <<<MD in every generate method
$content = preg_replace('/(\s+)(\$content\s*=\s*<<<MD)/', '$1' . trim($mergeStr) . '$1$2', $content);

file_put_contents($file, $content);
echo "Done.\n";
