<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Booking;
use App\Models\Campaign;
use App\Models\Clinic;
use App\Models\Slot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_pages_render_with_campaign_booking_data(): void
    {
        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $admin = Admin::create([
            'clinic_id' => $clinic->id,
            'name' => 'Clinic Admin',
            'email' => 'admin@example.com',
            'google_id' => 'google-admin-1',
        ]);

        $user = User::create([
            'clinic_id' => $clinic->id,
            'name' => 'Test Patient',
            'full_name' => 'Test Patient',
            'student_personnel_id' => '6600001',
            'phone_number' => '0812345678',
            'department' => 'Medical',
            'email' => 'patient@example.com',
            'password' => 'password',
        ]);

        $campaign = Campaign::create([
            'clinic_id' => $clinic->id,
            'title' => 'Flu Vaccine 2026',
            'description' => 'Seasonal campaign',
            'total_capacity' => 20,
            'status' => 'active',
        ]);

        $slot = Slot::create([
            'camp_id' => $campaign->id,
            'date' => now()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'max_slots' => 20,
            'status' => 'available',
        ]);

        Booking::create([
            'clinic_id' => $clinic->id,
            'user_id' => $user->id,
            'camp_id' => $campaign->id,
            'slot_id' => $slot->id,
            'status' => 'attended',
        ]);

        foreach ([
            'admin.dashboard',
            'admin.campaigns',
            'admin.bookings',
            'admin.time_slots',
            'admin.manage_staff',
            'admin.activity_logs',
            'admin.reports',
            'admin.users',
        ] as $route) {
            $this->actingAs($admin, 'admin')
                ->withSession(['clinic_id' => $clinic->id])
                ->get(route($route))
                ->assertOk();
        }
    }
}
