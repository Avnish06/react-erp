<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$sa = \App\Models\User::where('email', 'superadmin@colovo.com')->first();
echo "Role: " . $sa->role . ", Company ID: " . $sa->company_id . "\n";
