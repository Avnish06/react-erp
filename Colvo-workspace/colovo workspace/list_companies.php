<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$companies = \App\Models\Company::all();
foreach ($companies as $c) {
    echo $c->id . " : " . $c->name . "\n";
}
