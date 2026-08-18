<?php
$user = App\Models\User::firstOrCreate(
    ['email' => 'superadmin@colovo.com'],
    [
        'name' => 'Colovo Superadmin',
        'password' => Hash::make('admin123'),
        'role' => 'superadmin',
        'department' => 'Executive',
        'position' => 'CEO',
        'salary' => 200000.00,
        'company_id' => 1
    ]
);
$user->password = Hash::make('admin123');
$user->role = 'superadmin';
$user->save();
echo "Superadmin created successfully!\n";
