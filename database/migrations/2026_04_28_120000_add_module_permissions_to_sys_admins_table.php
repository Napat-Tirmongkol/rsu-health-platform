<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sys_admins', function (Blueprint $table) {
            $table->json('module_permissions')->nullable()->after('profile_photo_path');
        });
    }

    public function down(): void
    {
        Schema::table('sys_admins', function (Blueprint $table) {
            $table->dropColumn('module_permissions');
        });
    }
};
