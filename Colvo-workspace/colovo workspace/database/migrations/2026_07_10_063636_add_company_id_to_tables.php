<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create a default company first to attach existing records to avoid foreign key constraint errors
        $defaultCompanyId = DB::table('companies')->insertGetId([
            'name' => 'Default Company',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $tables = [
            'projects', 'tasks', 'attendances', 'financial_records', 
            'performance_reviews', 'promotion_recognitions', 'leaves', 'payrolls'
        ];

        Schema::table('users', function (Blueprint $table) use ($defaultCompanyId) {
            $table->foreignId('company_id')->nullable()->default($defaultCompanyId)->constrained('companies')->onDelete('cascade');
            // 'role' is currently string, let's keep it as string but we will use 'superadmin', 'admin', 'employee'
        });

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($defaultCompanyId) {
                $table->foreignId('company_id')->default($defaultCompanyId)->constrained('companies')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tables = [
            'projects', 'tasks', 'attendances', 'financial_records', 
            'performance_reviews', 'promotion_recognitions', 'leaves', 'payrolls', 'users'
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['company_id']);
                $table->dropColumn('company_id');
            });
        }
    }
};
