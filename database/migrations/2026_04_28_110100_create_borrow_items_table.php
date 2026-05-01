<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('borrow_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id')->default(1);
            $table->foreignId('category_id')->constrained('borrow_categories');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->string('serial_number', 100)->nullable();
            $table->enum('status', ['available', 'borrowed', 'maintenance'])->default('available');
            $table->timestamps();

            $table->index(['clinic_id', 'category_id', 'status']);
            $table->unique(['clinic_id', 'serial_number']);
            $table->foreign('clinic_id')->references('id')->on('sys_clinics');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('borrow_items');
    }
};
