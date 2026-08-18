<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$company = \App\Models\Company::first();
if ($company) {
    $company->address = 'Haryana Plot 93, Rajendra Park, Sector 105, Gurugram – 122001.';
    $company->save();
    echo "Updated successfully.";
} else {
    echo "No company found.";
}
