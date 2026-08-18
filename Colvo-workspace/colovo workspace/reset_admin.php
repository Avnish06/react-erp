<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$u = App\Models\User::where('email', 'admin@colovo.com')->first();
if($u) {
    $u->password = Illuminate\Support\Facades\Hash::make('Admin@123');
    $u->save();
    echo 'Password reset for admin@colovo.com';
} else {
    echo 'User not found';
}
