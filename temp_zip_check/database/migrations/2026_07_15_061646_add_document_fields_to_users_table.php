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
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('employment_type')->nullable();
            $table->string('reporting_manager')->nullable();
            $table->string('work_location')->nullable();
            $table->string('currency')->nullable();
            $table->string('pay_frequency')->nullable();
            $table->string('bonus_structure')->nullable();
            $table->integer('probation_period_months')->nullable();
            $table->integer('notice_period_days')->nullable();
            $table->integer('non_compete_duration_months')->nullable();
            $table->integer('confidentiality_duration_years')->nullable();
            $table->string('non_compete_geographic_scope')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
