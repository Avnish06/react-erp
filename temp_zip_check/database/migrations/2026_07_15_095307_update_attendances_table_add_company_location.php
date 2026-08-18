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
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('check_in_photo')->nullable();
            $table->string('check_out_photo')->nullable();
            $table->decimal('check_in_latitude', 10, 8)->nullable();
            $table->decimal('check_in_longitude', 11, 8)->nullable();
            $table->decimal('check_out_latitude', 10, 8)->nullable();
            $table->decimal('check_out_longitude', 11, 8)->nullable();
            $table->decimal('check_in_distance', 8, 2)->nullable();
            $table->decimal('check_out_distance', 8, 2)->nullable();
            $table->decimal('working_hours', 5, 2)->nullable();
            
            $table->string('correction_status')->nullable(); // pending, approved, rejected
            $table->text('correction_reason')->nullable();
            $table->time('requested_check_in')->nullable();
            $table->time('requested_check_out')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn([
                'check_in_photo', 'check_out_photo',
                'check_in_latitude', 'check_in_longitude',
                'check_out_latitude', 'check_out_longitude',
                'check_in_distance', 'check_out_distance',
                'working_hours', 'correction_status', 'correction_reason',
                'requested_check_in', 'requested_check_out'
            ]);
        });
    }
};
