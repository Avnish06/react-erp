<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$u = App\Models\User::where('email', 'john@colovo.com')->first();
if($u) {
    $u->password = Illuminate\Support\Facades\Hash::make('Employee@123');
    $u->save();
    echo 'Password reset for john@colovo.com';
} else {
    echo 'User not found';
}
