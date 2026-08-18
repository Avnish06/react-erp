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
        Schema::create('employee_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('father_occupation')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_no')->nullable();
            $table->string('bank_ifsc')->nullable();
            $table->string('marksheet_10th_path')->nullable();
            $table->string('marksheet_12th_path')->nullable();
            $table->string('passport_photo_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_details');
    }
};
