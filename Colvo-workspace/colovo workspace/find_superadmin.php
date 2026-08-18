<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('role', 'superadmin')->first();
if ($user) {
    echo "Email: " . $user->email . "\n";
    // I will also reset the password to something known so the user can login
    $user->password = \Illuminate\Support\Facades\Hash::make('password123');
    $user->save();
    echo "Password: password123\n";
} else {
    echo "No superadmin found. Creating one...\n";
    $company = App\Models\Company::first();
    if (!$company) {
        $company = App\Models\Company::create(['name' => 'Colovo Global', 'status' => 'active']);
    }
    $user = App\Models\User::create([
        'name' => 'Super Admin',
        'email' => 'superadmin@colovo.com',
        'password' => \Illuminate\Support\Facades\Hash::make('superadmin123'),
        'role' => 'superadmin',
        'company_id' => $company->id
    ]);
    echo "Email: superadmin@colovo.com\n";
    echo "Password: superadmin123\n";
}
