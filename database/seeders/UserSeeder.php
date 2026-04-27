<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // สร้าง Test User สำหรับ Clinic ID 1
        User::create([
            'name' => 'Test Patient',
            'email' => 'patient@example.com',
            'password' => Hash::make('password'),
            'clinic_id' => 1,
        ]);

        // สร้าง Test Staff
        User::create([
            'name' => 'Clinic Staff',
            'email' => 'staff@example.com',
            'password' => Hash::make('password'),
            'clinic_id' => 1,
        ]);
    }
}
