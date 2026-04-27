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
        Schema::create('camp_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained('sys_clinics')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('camp_id')->constrained('camp_list')->cascadeOnDelete();
            $table->foreignId('slot_id')->constrained('camp_slots')->cascadeOnDelete();
            $table->string('booking_code')->unique();
            $table->string('status')->default('pending'); // pending, confirmed, cancelled, attended
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('camp_bookings');
    }
};
