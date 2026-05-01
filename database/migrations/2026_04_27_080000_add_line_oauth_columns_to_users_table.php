<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('line_user_id')->nullable()->after('email');
            $table->string('line_avatar_url', 2048)->nullable()->after('profile_photo_path');
            $table->unique(['clinic_id', 'line_user_id']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['clinic_id', 'line_user_id']);
            $table->dropColumn(['line_user_id', 'line_avatar_url']);
        });
    }
};
