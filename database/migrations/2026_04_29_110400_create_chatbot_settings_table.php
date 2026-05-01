<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id')->default(1);
            $table->text('system_prompt')->nullable();
            $table->string('model')->default('gemini-2.5-flash');
            $table->decimal('temperature', 3, 2)->default(0.20);
            $table->unsignedInteger('daily_quota')->default(20);
            $table->timestamps();

            $table->unique('clinic_id');
            $table->foreign('clinic_id')->references('id')->on('sys_clinics');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_settings');
    }
};
