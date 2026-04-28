<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('borrow_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id')->default(1);
            $table->foreignId('fine_id')->constrained('borrow_fines')->cascadeOnDelete();
            $table->decimal('amount_paid', 10, 2);
            $table->enum('payment_method', ['cash', 'bank_transfer'])->default('cash');
            $table->string('payment_slip_path')->nullable();
            $table->dateTime('payment_date')->useCurrent();
            $table->foreignId('received_by_staff_id')->nullable()->constrained('sys_staff')->nullOnDelete();
            $table->string('receipt_number', 100)->nullable();
            $table->text('payment_notes')->nullable();
            $table->timestamps();

            $table->index(['clinic_id', 'payment_date']);
            $table->foreign('clinic_id')->references('id')->on('sys_clinics');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('borrow_payments');
    }
};
