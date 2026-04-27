<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('satisfaction_surveys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('sys_clinics')->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained('camp_bookings')->nullOnDelete();
            $table->integer('score')->unsigned(); // 1-5
            $table->text('comment')->nullable();
            $table->json('detailed_responses')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('satisfaction_surveys');
    }
};
