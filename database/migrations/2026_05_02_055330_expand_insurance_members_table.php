<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('insurance_members', function (Blueprint $table) {
            $table->string('member_id')->nullable()->after('user_id')->index();
            $table->enum('member_type', ['student', 'staff'])->default('student')->after('member_id');
            $table->string('first_name')->nullable()->after('member_type');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('national_id')->nullable()->after('last_name');
            $table->string('department')->nullable()->after('national_id');
            $table->string('member_status')->default('active')->after('department');
            $table->string('insurance_status')->default('pending')->after('member_status');
            $table->date('coverage_start_date')->nullable()->after('insurance_status');

            $table->foreignId('user_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('insurance_members', function (Blueprint $table) {
            $table->dropColumn([
                'member_id', 'member_type', 'first_name', 'last_name',
                'national_id', 'department', 'member_status',
                'insurance_status', 'coverage_start_date',
            ]);
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }
};
