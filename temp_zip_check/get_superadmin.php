<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$superadmin = \App\Models\User::where('role', 'superadmin')->first();
if ($superadmin) {
    echo "Email: " . $superadmin->email . "\n";
    // We can't reverse hash, so we'll just reset it for them
    $superadmin->password = \Illuminate\Support\Facades\Hash::make('superadmin123');
    $superadmin->save();
    echo "Password has been reset to: superadmin123\n";
} else {
    // Create one
    $superadmin = \App\Models\User::create([
        'name' => 'Super Admin',
        'email' => 'superadmin@colovo.com',
        'password' => \Illuminate\Support\Facades\Hash::make('superadmin123'),
        'role' => 'superadmin',
    ]);
    echo "Created Superadmin! Email: superadmin@colovo.com | Password: superadmin123\n";
}
