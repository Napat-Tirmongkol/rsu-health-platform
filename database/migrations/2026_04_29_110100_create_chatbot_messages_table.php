<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id')->default(1);
            $table->foreignId('conversation_id')->constrained('chatbot_conversations')->cascadeOnDelete();
            $table->string('role', 20);
            $table->longText('content');
            $table->string('intent', 50)->nullable();
            $table->unsignedInteger('tokens_used')->nullable();
            $table->timestamps();

            $table->index(['clinic_id', 'conversation_id', 'created_at']);
            $table->foreign('clinic_id')->references('id')->on('sys_clinics');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_messages');
    }
};
