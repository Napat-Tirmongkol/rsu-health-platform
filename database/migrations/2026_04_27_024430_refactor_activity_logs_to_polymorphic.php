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
        Schema::table('sys_activity_logs', function (Blueprint $table) {
            // Drop existing foreign key if any (SQLite doesn't always handle this the same way, but let's try)
            // For SQLite, we might just need to add columns.
            $table->string('actor_type')->nullable()->after('user_id');
            $table->renameColumn('user_id', 'actor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sys_activity_logs', function (Blueprint $table) {
            $table->renameColumn('actor_id', 'user_id');
            $table->dropColumn('actor_type');
        });
    }
};
