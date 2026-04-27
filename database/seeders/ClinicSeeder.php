<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClinicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \DB::table('sys_clinics')->insert([
            [
                'id' => 1,
                'name' => 'RSU Medical Clinic',
                'slug' => 'medical',
                'code' => 'RSU-MED',
                'domain' => 'medical.rsu.ac.th',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
