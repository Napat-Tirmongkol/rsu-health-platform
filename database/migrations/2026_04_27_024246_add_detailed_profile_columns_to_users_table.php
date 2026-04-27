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
        Schema::table('users', function (Blueprint $table) {
            $table->string('prefix', 20)->nullable()->after('id');
            $table->string('first_name', 100)->nullable()->after('prefix');
            $table->string('last_name', 100)->nullable()->after('first_name');
            $table->string('full_name', 200)->nullable()->after('last_name');
            $table->string('gender', 20)->nullable()->after('full_name');
            $table->string('status', 50)->nullable()->after('gender'); // student, staff, other
            $table->string('citizen_id', 20)->nullable()->after('status');
            $table->string('student_personnel_id', 20)->nullable()->after('citizen_id');
            $table->string('department', 150)->nullable()->after('student_personnel_id');
            $table->string('phone_number', 20)->nullable()->after('department');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'prefix', 'first_name', 'last_name', 'full_name', 
                'gender', 'status', 'citizen_id', 
                'student_personnel_id', 'department', 'phone_number'
            ]);
        });
    }
};
