<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_faqs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id')->default(1);
            $table->string('question');
            $table->text('answer');
            $table->json('keywords')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['clinic_id', 'is_active']);
            $table->foreign('clinic_id')->references('id')->on('sys_clinics');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_faqs');
    }
};
