<?php
$user = App\Models\User::where('email', 'admin@colovo.com')->first();
if ($user) {
    $user->password = Hash::make('admin123');
    $user->save();
    echo "Password reset successfully!\n";
} else {
    echo "User not found.\n";
}
