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

    public function test_admin_pages_show_identity_label_and_value_for_general_user(): void
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
            'email' => 'passport-admin@example.com',
            'google_id' => 'google-admin-passport',
        ]);

        $user = User::create([
            'clinic_id' => $clinic->id,
            'name' => 'Foreign Visitor',
            'full_name' => 'Foreign Visitor',
            'status' => 'other',
            'citizen_id' => 'AB1234567',
            'phone_number' => '0812345678',
            'department' => 'External',
            'email' => 'passport-visitor@example.com',
            'password' => 'password',
        ]);

        $campaign = Campaign::create([
            'clinic_id' => $clinic->id,
            'title' => 'General Checkup',
            'description' => 'Walk-in campaign',
            'total_capacity' => 10,
            'status' => 'active',
        ]);

        $slot = Slot::create([
            'camp_id' => $campaign->id,
            'date' => now()->toDateString(),
            'start_time' => '13:00',
            'end_time' => '14:00',
            'max_slots' => 10,
            'status' => 'available',
        ]);

        Booking::create([
            'clinic_id' => $clinic->id,
            'user_id' => $user->id,
            'camp_id' => $campaign->id,
            'slot_id' => $slot->id,
            'status' => 'confirmed',
        ]);

        $this->actingAs($admin, 'admin')
            ->withSession(['clinic_id' => $clinic->id])
            ->get(route('admin.users'))
            ->assertOk()
            ->assertSee('Passport')
            ->assertSee('AB1234567');

        $this->actingAs($admin, 'admin')
            ->withSession(['clinic_id' => $clinic->id])
            ->get(route('admin.bookings'))
            ->assertOk()
            ->assertSee('Passport')
            ->assertSee('AB1234567')
            ->assertSee('Open Scanner');

        $this->actingAs($admin, 'admin')
            ->withSession(['clinic_id' => $clinic->id])
            ->get(route('admin.reports'))
            ->assertOk()
            ->assertSee('Passport')
            ->assertSee('AB1234567');
    }

    public function test_admin_campaign_page_includes_scanner_entrypoint(): void
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
            'email' => 'scanner-admin@example.com',
            'google_id' => 'google-admin-scanner',
        ]);

        $this->actingAs($admin, 'admin')
            ->withSession(['clinic_id' => $clinic->id])
            ->get(route('admin.campaigns'))
            ->assertOk()
            ->assertSee('Open Scanner');
    }
}
