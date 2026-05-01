<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('borrow_fines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id')->default(1);
            $table->foreignId('borrow_record_id')->constrained('borrow_records')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('amount', 10, 2)->default(0);
            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('created_by_staff_id')->nullable()->constrained('sys_staff')->nullOnDelete();
            $table->timestamps();

            $table->index(['clinic_id', 'status']);
            $table->foreign('clinic_id')->references('id')->on('sys_clinics');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('borrow_fines');
    }
};
