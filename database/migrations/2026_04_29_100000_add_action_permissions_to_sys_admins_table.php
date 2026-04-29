<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sys_admins', function (Blueprint $table) {
            if (! Schema::hasColumn('sys_admins', 'action_permissions')) {
                $table->json('action_permissions')->nullable()->after('module_permissions');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sys_admins', function (Blueprint $table) {
            if (Schema::hasColumn('sys_admins', 'action_permissions')) {
                $table->dropColumn('action_permissions');
            }
        });
    }
};
