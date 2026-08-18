<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$company = \App\Models\Company::find(2);
if ($company) {
    $company->address = "Head Office :\nHaryana\nPlot 93, Rajendra Park, Sector 105, Gurugram – 122001.";
    $company->save();
    echo "Updated Hatbaliya's address in DB.\n";
}
