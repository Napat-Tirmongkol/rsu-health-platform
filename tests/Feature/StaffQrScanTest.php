<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\Campaign;
use App\Models\Clinic;
use App\Models\Slot;
use App\Models\Staff;
use App\Models\User;
use App\Services\IdentityQrCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StaffQrScanTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_scan_page_renders_for_staff_guard(): void
    {
        [$clinic, $staff] = $this->createScanFixture();

        $this->actingAs($staff, 'staff')
            ->withSession(['clinic_id' => $clinic->id])
            ->get(route('staff.scan'))
            ->assertOk()
            ->assertSee('Identity Scanner');
    }

    public function test_staff_can_open_campaign_bound_scan_mode(): void
    {
        [$clinic, $staff, , $booking] = $this->createScanFixture();

        $this->actingAs($staff, 'staff')
            ->withSession(['clinic_id' => $clinic->id])
            ->get(route('staff.scan.campaign', $booking->camp_id))
            ->assertOk()
            ->assertSee('Scanner is locked to')
            ->assertSee('Flu Vaccine');
    }

    public function test_staff_can_verify_signed_identity_qr(): void
    {
        [$clinic, $staff, $user, $booking] = $this->createScanFixture();

        $response = $this->actingAs($staff, 'staff')
            ->withSession(['clinic_id' => $clinic->id])
            ->postJson(route('staff.scan.verify'), [
                'qr_payload' => app(IdentityQrCode::class)->payload($user),
            ]);

        $response->assertOk()
            ->assertJsonPath('user.name', 'Scan Target')
            ->assertJsonPath('user.identity_type', 'student_id')
            ->assertJsonPath('bookings.0.id', $booking->id)
            ->assertJsonPath('bookings.0.can_check_in', true);
    }

    public function test_staff_scan_can_filter_bookings_by_campaign_and_today(): void
    {
        [$clinic, $staff, $user, $booking] = $this->createScanFixture();

        $otherCampaign = Campaign::create([
            'clinic_id' => $clinic->id,
            'title' => 'Other Campaign',
            'description' => 'Secondary',
            'total_capacity' => 30,
            'status' => 'active',
        ]);

        $otherSlot = Slot::create([
            'camp_id' => $otherCampaign->id,
            'date' => now()->addDay()->toDateString(),
            'start_time' => '13:00',
            'end_time' => '14:00',
            'max_slots' => 30,
            'status' => 'available',
        ]);

        Booking::create([
            'clinic_id' => $clinic->id,
            'user_id' => $user->id,
            'camp_id' => $otherCampaign->id,
            'slot_id' => $otherSlot->id,
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($staff, 'staff')
            ->withSession(['clinic_id' => $clinic->id])
            ->postJson(route('staff.scan.verify'), [
                'qr_payload' => app(IdentityQrCode::class)->payload($user),
                'campaign_id' => $booking->camp_id,
                'today_only' => true,
            ]);

        $response->assertOk()
            ->assertJsonCount(1, 'bookings')
            ->assertJsonPath('bookings.0.id', $booking->id);
    }

    public function test_staff_can_check_in_verified_booking(): void
    {
        [$clinic, $staff, $user, $booking] = $this->createScanFixture();

        $payload = app(IdentityQrCode::class)->payload($user);

        $response = $this->actingAs($staff, 'staff')
            ->withSession(['clinic_id' => $clinic->id])
            ->postJson(route('staff.scan.check-in'), [
                'qr_payload' => $payload,
                'booking_id' => $booking->id,
            ]);

        $response->assertOk()
            ->assertJsonPath('booking.status', 'attended');

        $this->assertDatabaseHas('camp_bookings', [
            'id' => $booking->id,
            'status' => 'attended',
        ]);

        $this->assertDatabaseHas('sys_activity_logs', [
            'actor_id' => $staff->id,
            'actor_type' => Staff::class,
            'action' => 'booking.checked_in',
        ]);
    }

    public function test_staff_can_store_check_in_note(): void
    {
        [$clinic, $staff, $user, $booking] = $this->createScanFixture();

        $payload = app(IdentityQrCode::class)->payload($user);

        $response = $this->actingAs($staff, 'staff')
            ->withSession(['clinic_id' => $clinic->id])
            ->postJson(route('staff.scan.check-in'), [
                'qr_payload' => $payload,
                'booking_id' => $booking->id,
                'check_in_note' => 'Patient arrived with supporting documents.',
            ]);

        $response->assertOk()
            ->assertJsonPath('booking.status', 'attended');

        $this->assertStringContainsString(
            'Patient arrived with supporting documents.',
            (string) Booking::findOrFail($booking->id)->notes
        );

        $log = ActivityLog::where('action', 'booking.checked_in')->latest()->first();
        $this->assertSame('Patient arrived with supporting documents.', $log->properties['check_in_note']);
    }

    public function test_staff_scan_returns_duplicate_warning_after_recent_check_in(): void
    {
        [$clinic, $staff, $user, $booking] = $this->createScanFixture();

        $payload = app(IdentityQrCode::class)->payload($user);

        $this->actingAs($staff, 'staff')
            ->withSession(['clinic_id' => $clinic->id])
            ->postJson(route('staff.scan.check-in'), [
                'qr_payload' => $payload,
                'booking_id' => $booking->id,
            ])->assertOk();

        $response = $this->actingAs($staff, 'staff')
            ->withSession(['clinic_id' => $clinic->id])
            ->postJson(route('staff.scan.verify'), [
                'qr_payload' => $payload,
            ]);

        $response->assertOk()
            ->assertJsonPath('duplicate_warning.message', 'This identity was checked in recently.')
            ->assertJsonPath('recent_scans.0.user_name', 'Scan Target');
    }

    public function test_staff_scan_rejects_tampered_payload(): void
    {
        [$clinic, $staff, $user] = $this->createScanFixture();

        $payload = json_decode(app(IdentityQrCode::class)->payload($user), true, 512, JSON_THROW_ON_ERROR);
        $payload['data']['user_id'] = 999999;

        $response = $this->actingAs($staff, 'staff')
            ->withSession(['clinic_id' => $clinic->id])
            ->postJson(route('staff.scan.verify'), [
                'qr_payload' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Invalid QR signature.');
    }

    private function createScanFixture(): array
    {
        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $staffUser = User::create([
            'clinic_id' => $clinic->id,
            'name' => 'Staff Helper',
            'full_name' => 'Staff Helper',
            'email' => 'staff-helper@example.com',
            'password' => Hash::make('password'),
        ]);

        $staff = Staff::create([
            'clinic_id' => $clinic->id,
            'user_id' => $staffUser->id,
            'full_name' => 'Staff Helper',
            'email' => 'staff-scan@example.com',
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);

        $user = User::create([
            'clinic_id' => $clinic->id,
            'name' => 'Scan Target',
            'full_name' => 'Scan Target',
            'status' => 'student',
            'student_personnel_id' => '6712345678',
            'email' => 'scan-target@example.com',
            'password' => Hash::make('password'),
        ]);

        $campaign = Campaign::create([
            'clinic_id' => $clinic->id,
            'title' => 'Flu Vaccine',
            'description' => 'Seasonal dose',
            'total_capacity' => 30,
            'status' => 'active',
        ]);

        $slot = Slot::create([
            'camp_id' => $campaign->id,
            'date' => now()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'max_slots' => 30,
            'status' => 'available',
        ]);

        $booking = Booking::create([
            'clinic_id' => $clinic->id,
            'user_id' => $user->id,
            'camp_id' => $campaign->id,
            'slot_id' => $slot->id,
            'status' => 'confirmed',
        ]);

        return [$clinic, $staff, $user, $booking];
    }
}
