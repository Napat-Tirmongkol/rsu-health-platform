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
        Schema::table('sys_clinics', function (Blueprint $table) {
            $table->text('description')->nullable()->after('status');
            $table->string('logo_url')->nullable()->after('description');
            $table->string('primary_color', 20)->nullable()->after('logo_url');
            $table->string('contact_email')->nullable()->after('primary_color');
            $table->string('contact_phone', 30)->nullable()->after('contact_email');
        });
    }

    public function down(): void
    {
        Schema::table('sys_clinics', function (Blueprint $table) {
            $table->dropColumn(['description', 'logo_url', 'primary_color', 'contact_email', 'contact_phone']);
        });
    }
};
