<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sys_faculties', function (Blueprint $table) {
            $table->id();
            $table->string('name_th');
            $table->string('name_en')->nullable();
            $table->timestamps();
        });

        // Insert Default RSU Faculties
        $faculties = [
            ['name_th' => 'วิทยาลัยแพทยศาสตร์'],
            ['name_th' => 'วิทยาลัยทันตแพทยศาสตร์'],
            ['name_th' => 'วิทยาลัยเภสัชศาสตร์'],
            ['name_th' => 'คณะพยาบาลศาสตร์'],
            ['name_th' => 'คณะเทคนิคการแพทย์'],
            ['name_th' => 'คณะกายภาพบำบัดและเวชศาสตร์การกีฬา'],
            ['name_th' => 'วิทยาลัยวิศวกรรมศาสตร์'],
            ['name_th' => 'วิทยาลัยเทคโนโลยีสารสนเทศและการสื่อสาร'],
            ['name_th' => 'คณะวิทยาศาสตร์'],
            ['name_th' => 'วิทยาลัยนวัตกรรมเกษตร เทคโนโลยีชีวภาพ และอาหาร'],
            ['name_th' => 'วิทยาลัยบริหารธุรกิจ'],
            ['name_th' => 'วิทยาลัยบัญชี'],
            ['name_th' => 'วิทยาลัยนิเทศศาสตร์'],
            ['name_th' => 'วิทยาลัยศิลปินการแสดง'],
            ['name_th' => 'วิทยาลัยนวัตกรรมดิจิทัลเทคโนโลยี'],
            ['name_th' => 'คณะสถาปัตยกรรมศาสตร์'],
            ['name_th' => 'วิทยาลัยการออกแบบ'],
            ['name_th' => 'คณะดนตรี'],
            ['name_th' => 'วิทยาลัยการท่องเที่ยวและการบริการ'],
            ['name_th' => 'วิทยาลัยนานาชาติ'],
            ['name_th' => 'คณะนิติศาสตร์'],
            ['name_th' => 'วิทยาลัยรัฐกิจ'],
            ['name_th' => 'สถาบันการบิน'],
            ['name_th' => 'วิทยาลัยการแพทย์แผนตะวันออก'],
            ['name_th' => 'คณะศิลปศาสตร์'],
            ['name_th' => 'วิทยาลัยเศรษฐศาสตร์และการลงทุน'],
            ['name_th' => 'คณะศึกษาศาสตร์'],
            ['name_th' => 'สำนักงานอธิการบดี'],
        ];

        DB::table('sys_faculties')->insert($faculties);
    }

    public function down(): void
    {
        Schema::dropIfExists('sys_faculties');
    }
};
