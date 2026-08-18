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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('month'); // e.g. "July 2026"
            $table->decimal('salary', 12, 2);
            $table->decimal('bonus', 12, 2)->default(0.00);
            $table->decimal('deductions', 12, 2)->default(0.00);
            $table->decimal('net_salary', 12, 2);
            $table->string('status')->default('unpaid'); // unpaid, paid
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
        Schema::dropIfExists('payrolls');
    }
};
