<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$admin = \App\Models\User::where('email', 'admin@colovo.com')->orWhere('role', 'admin')->first();
if (!$admin) {
    // Create one if it doesn't exist
    $company = \App\Models\Company::first();
    if (!$company) {
        $company = \App\Models\Company::create(['name' => 'Colovo Workspace']);
    }
    $admin = \App\Models\User::create([
        'name' => 'Admin User',
        'email' => 'admin@colovo.com',
        'password' => bcrypt('admin123'),
        'role' => 'admin',
        'company_id' => $company->id
    ]);
    echo "Created admin: admin@colovo.com with password: admin123\n";
} else {
    $admin->password = bcrypt('admin123');
    $admin->save();
    echo "Found admin: " . $admin->email . " and reset password to: admin123\n";
}
