<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('borrow_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id')->default(1);
            $table->foreignId('category_id')->nullable()->constrained('borrow_categories')->nullOnDelete();
            $table->foreignId('item_id')->nullable()->constrained('borrow_items')->nullOnDelete();
            $table->foreignId('borrower_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('lending_staff_id')->nullable()->constrained('sys_staff')->nullOnDelete();
            $table->foreignId('approver_staff_id')->nullable()->constrained('sys_staff')->nullOnDelete();
            $table->foreignId('return_staff_id')->nullable()->constrained('sys_staff')->nullOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->text('reason')->nullable();
            $table->dateTime('borrowed_at')->useCurrent();
            $table->date('due_date')->nullable();
            $table->dateTime('returned_at')->nullable();
            $table->enum('status', ['borrowed', 'returned'])->default('borrowed');
            $table->enum('approval_status', ['pending', 'approved', 'rejected', 'staff_added'])->default('staff_added');
            $table->string('attachment_path')->nullable();
            $table->enum('fine_status', ['none', 'pending', 'paid'])->default('none');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['clinic_id', 'status', 'approval_status']);
            $table->index(['clinic_id', 'borrower_user_id', 'due_date']);
            $table->foreign('clinic_id')->references('id')->on('sys_clinics');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('borrow_records');
    }
};
