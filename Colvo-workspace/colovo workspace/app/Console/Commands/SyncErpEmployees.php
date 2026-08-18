<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class SyncErpEmployees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:erp-employees';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync employees from the ERP database to Colovo Workspace';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting ERP Employee Sync...');

        try {
            // Fetch users from ERP database
            $erpUsers = DB::connection('erp_db')->select("
                SELECT u.email, u.password, u.name, r.name as role_name
                FROM users u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE r.name LIKE 'Employee%'
            ");

            $count = 0;
            // A default company ID if needed
            $defaultCompany = \App\Models\Company::first();
            $companyId = $defaultCompany ? $defaultCompany->id : 1;

            foreach ($erpUsers as $erpUser) {
                // Check if user already exists in Colovo
                $existingUser = User::where('email', $erpUser->email)->first();

                if ($existingUser) {
                    // Update existing user's name or password if they changed in ERP
                    // We sync the hash directly from ERP so they can use the exact same password
                    $existingUser->update([
                        'name' => $erpUser->name ?? 'ERP User',
                        'password' => $erpUser->password
                    ]);
                } else {
                    // Create new user in Colovo
                    User::create([
                        'name' => $erpUser->name ?? 'ERP User',
                        'email' => $erpUser->email,
                        'password' => $erpUser->password,
                        'role' => 'employee',
                        'company_id' => $companyId
                    ]);
                    $count++;
                }
            }

            $this->info("Successfully synced " . count($erpUsers) . " employees from ERP. ($count newly created)");
        } catch (\Exception $e) {
            $this->error('Error syncing from ERP: ' . $e->getMessage());
        }
    }
}
