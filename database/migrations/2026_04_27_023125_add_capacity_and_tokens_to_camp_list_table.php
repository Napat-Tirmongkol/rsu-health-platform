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
        Schema::table('camp_list', function (Blueprint $table) {
            $table->integer('total_capacity')->default(0)->after('description');
            $table->boolean('is_auto_approve')->default(false)->after('status');
            $table->string('share_token', 64)->nullable()->unique()->after('is_auto_approve');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('camp_list', function (Blueprint $table) {
            $table->dropColumn(['total_capacity', 'is_auto_approve', 'share_token']);
        });
    }
};
