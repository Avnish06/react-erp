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
        Schema::create('company_attendance_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->decimal('office_latitude', 10, 8)->nullable();
            $table->decimal('office_longitude', 11, 8)->nullable();
            $table->integer('allowed_radius')->default(100);
            $table->boolean('attendance_photo_required')->default(true);
            $table->boolean('location_required')->default(true);
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
        Schema::dropIfExists('company_attendance_settings');
    }
};
