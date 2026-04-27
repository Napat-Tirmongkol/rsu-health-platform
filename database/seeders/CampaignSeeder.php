<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\Slot;
use Illuminate\Database\Seeder;

class CampaignSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // สร้างแคมเปญตัวอย่าง
        $camp = Campaign::create([
            'clinic_id' => 1,
            'title' => 'วัคซีนไข้หวัดใหญ่ 4 สายพันธุ์ (2026)',
            'description' => 'บริการฉีดวัคซีนไข้หวัดใหญ่สำหรับนักศึกษาและบุคลากร',
            'type' => 'vaccine',
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addMonths(3),
        ]);

        // สร้าง Slots สำหรับ 5 วันข้างหน้า
        for ($i = 1; $i <= 5; $i++) {
            $date = now()->addDays($i)->format('Y-m-d');
            
            // ช่วงเช้า
            Slot::create([
                'camp_id' => $camp->id,
                'date' => $date,
                'start_time' => '09:00:00',
                'end_time' => '10:00:00',
                'max_slots' => 20,
            ]);

            // ช่วงบ่าย
            Slot::create([
                'camp_id' => $camp->id,
                'date' => $date,
                'start_time' => '13:00:00',
                'end_time' => '14:00:00',
                'max_slots' => 20,
            ]);
        }
    }
}
