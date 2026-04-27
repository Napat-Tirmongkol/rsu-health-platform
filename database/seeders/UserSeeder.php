<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Portal;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'patient@example.com'],
            [
                'name' => 'Test Patient',
                'password' => Hash::make('password'),
                'clinic_id' => 1,
            ]
        );

        $staffUser = User::updateOrCreate(
            ['email' => 'staff-user@example.com'],
            [
                'name' => 'Clinic Staff',
                'password' => Hash::make('password'),
                'clinic_id' => 1,
            ]
        );

        Staff::updateOrCreate(
            ['email' => 'staff@example.com'],
            [
                'clinic_id' => 1,
                'user_id' => $staffUser->id,
                'password' => Hash::make('password'),
                'role' => 'staff',
                'status' => 'active',
            ]
        );

        Admin::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'clinic_id' => 1,
                'name' => 'Clinic Admin',
            ]
        );

        Portal::updateOrCreate(
            ['email' => 'portal@example.com'],
            [
                'name' => 'Portal Admin',
                'password' => Hash::make('password'),
            ]
        );
    }
}
