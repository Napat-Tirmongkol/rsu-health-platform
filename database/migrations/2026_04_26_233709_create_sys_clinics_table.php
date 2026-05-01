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
        Schema::create('sys_clinics', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique(); // For subdomain lookup
            $table->string('code')->unique();
            $table->string('domain')->nullable()->unique();
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sys_clinics');
    }
};
