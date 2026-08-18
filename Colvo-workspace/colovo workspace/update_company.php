<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$companies = \App\Models\Company::all();
$found = false;
foreach ($companies as $c) {
    if (stripos($c->name, 'hatwaliya') !== false) {
        $c->address = "Head Office :\nHaryana\nPlot 93, Rajendra Park, Sector 105, Gurugram – 122001.";
        $c->save();
        echo "Updated address for company: " . $c->name . "\n";
        $found = true;
    }
}

if (!$found) {
    echo "Could not find any company with 'hatwaliya' in its name.\n";
}
