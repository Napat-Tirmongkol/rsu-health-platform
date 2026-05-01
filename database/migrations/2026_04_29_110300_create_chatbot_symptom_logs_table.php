<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_symptom_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id')->default(1);
            $table->string('line_user_id');
            $table->text('symptoms');
            $table->string('severity', 50)->nullable();
            $table->text('recommendation')->nullable();
            $table->timestamps();

            $table->index(['clinic_id', 'line_user_id', 'created_at']);
            $table->foreign('clinic_id')->references('id')->on('sys_clinics');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_symptom_logs');
    }
};
