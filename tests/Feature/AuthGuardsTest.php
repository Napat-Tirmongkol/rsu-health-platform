<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Campaign;
use App\Models\Clinic;
use App\Models\Portal;
use App\Models\SiteSetting;
use App\Models\Slot;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AuthGuardsTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_login_with_staff_guard(): void
    {
        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $user = User::create([
            'clinic_id' => $clinic->id,
            'name' => 'Clinic Staff',
            'email' => 'staff-user@example.com',
            'password' => Hash::make('password'),
        ]);

        $staff = Staff::create([
            'clinic_id' => $clinic->id,
            'user_id' => $user->id,
            'email' => 'staff@example.com',
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);

        $response = $this->post(route('staff.login.store'), [
            'email' => 'staff@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/staff/dashboard');
        $this->assertAuthenticatedAs($staff, 'staff');
        $this->assertSame($staff->id, session('staff_id'));
        $this->assertSame($clinic->id, session('clinic_id'));
    }

    public function test_guard_login_pages_render(): void
    {
        $this->get(route('staff.login'))->assertOk();
        $this->get(route('portal.login'))->assertOk();
        $this->get(route('admin.login'))->assertOk();
    }

    public function test_portal_can_login_with_portal_guard(): void
    {
        $portal = Portal::create([
            'name' => 'Portal Admin',
            'email' => 'portal@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post(route('portal.login.store'), [
            'email' => 'portal@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/portal/dashboard');
        $this->assertAuthenticatedAs($portal, 'portal');
        $this->assertSame($portal->id, session('portal_id'));
    }

    public function test_portal_entry_route_redirects_to_login_or_dashboard(): void
    {
        $this->get('/portal')
            ->assertRedirect(route('portal.login'));

        $portal = Portal::create([
            'name' => 'Portal Entry Admin',
            'email' => 'portal-entry@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($portal, 'portal')
            ->get('/portal')
            ->assertRedirect(route('portal.dashboard'));
    }

    public function test_local_portal_dev_login_creates_account_and_redirects(): void
    {
        $response = $this->get(route('dev.login.portal'));

        $response->assertRedirect(route('portal.dashboard'));
        $this->assertAuthenticated('portal');
        $this->assertDatabaseHas('sys_portals', [
            'email' => 'portal@test.com',
            'name' => 'Developer Portal',
        ]);
    }

    public function test_portal_pages_render_for_portal_guard(): void
    {
        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        Campaign::create([
            'clinic_id' => $clinic->id,
            'title' => 'Flu Vaccine 2026',
            'description' => 'Seasonal campaign',
            'total_capacity' => 20,
            'status' => 'active',
        ]);

        SiteSetting::create([
            'clinic_id' => 0,
            'key' => 'site_name',
            'value' => 'RSU Medical Hub',
            'type' => 'string',
        ]);

        $portal = Portal::create([
            'name' => 'Portal Admin',
            'email' => 'portal-pages@example.com',
            'password' => Hash::make('password'),
        ]);

        foreach ([
            'portal.dashboard',
            'portal.clinics',
            'portal.settings',
        ] as $route) {
            $this->actingAs($portal, 'portal')
                ->get(route($route))
                ->assertOk();
        }
    }

    public function test_inactive_staff_cannot_login(): void
    {
        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $user = User::create([
            'clinic_id' => $clinic->id,
            'name' => 'Inactive Staff',
            'email' => 'inactive-user@example.com',
            'password' => Hash::make('password'),
        ]);

        Staff::create([
            'clinic_id' => $clinic->id,
            'user_id' => $user->id,
            'email' => 'inactive@example.com',
            'password' => Hash::make('password'),
            'status' => 'inactive',
        ]);

        $response = $this->from(route('staff.login'))->post(route('staff.login.store'), [
            'email' => 'inactive@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('staff.login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest('staff');
    }

    public function test_user_hub_requires_user_guard(): void
    {
        $response = $this->get(route('user.hub'));

        $response->assertRedirect(route('login'));
    }

    public function test_user_guard_can_access_user_hub(): void
    {
        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $user = User::create([
            'clinic_id' => $clinic->id,
            'name' => 'LINE User',
            'email' => 'line@example.com',
            'line_user_id' => 'line-user-1',
            'password' => Hash::make('password'),
        ]);

        $response = $this->actingAs($user, 'user')->get(route('user.hub'));

        $response->assertOk();
    }

    public function test_user_portal_pages_render_for_user_guard(): void
    {
        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $user = User::create([
            'clinic_id' => $clinic->id,
            'name' => 'LINE User',
            'email' => 'line-portal@example.com',
            'line_user_id' => 'line-user-portal',
            'password' => Hash::make('password'),
        ]);

        foreach ([
            'user.hub',
            'user.booking',
            'user.history',
            'user.chat',
            'user.profile',
            'user.services.ncd-clinic',
            'user.services.contact',
            'user.services.help',
        ] as $route) {
            $this->actingAs($user, 'user')
                ->get(route($route))
                ->assertOk();
        }
    }

    public function test_user_booking_page_auto_selects_single_campaign(): void
    {
        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $user = User::create([
            'clinic_id' => $clinic->id,
            'name' => 'LINE User',
            'email' => 'line-booking@example.com',
            'line_user_id' => 'line-user-booking',
            'password' => Hash::make('password'),
        ]);

        $campaign = Campaign::create([
            'clinic_id' => $clinic->id,
            'title' => 'Flu Vaccine 2026',
            'description' => 'Seasonal campaign',
            'total_capacity' => 20,
            'status' => 'active',
        ]);

        Slot::create([
            'camp_id' => $campaign->id,
            'date' => now()->addDay()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'max_slots' => 20,
            'status' => 'available',
        ]);

        $this->actingAs($user, 'user')
            ->withSession(['clinic_id' => $clinic->id])
            ->get(route('user.booking'))
            ->assertOk()
            ->assertSee('2. เลือกวันที่')
            ->assertSee(now()->addDay()->format('d'));
    }

    public function test_user_profile_page_posts_logout_to_user_guard(): void
    {
        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $user = User::create([
            'clinic_id' => $clinic->id,
            'name' => 'LINE User',
            'email' => 'line-profile@example.com',
            'line_user_id' => 'line-user-profile',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user, 'user')
            ->get(route('user.profile'))
            ->assertOk()
            ->assertSee(route('user.logout'));
    }

    public function test_time_slot_picker_loads_slots_for_sqlite_date_column(): void
    {
        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $campaign = Campaign::create([
            'clinic_id' => $clinic->id,
            'title' => 'Flu Vaccine 2026',
            'description' => 'Seasonal campaign',
            'total_capacity' => 20,
            'status' => 'active',
        ]);

        Slot::create([
            'camp_id' => $campaign->id,
            'date' => '2026-05-01',
            'start_time' => '09:00',
            'end_time' => '10:00',
            'max_slots' => 20,
            'status' => 'available',
        ]);

        Slot::create([
            'camp_id' => $campaign->id,
            'date' => '2026-05-01',
            'start_time' => '13:00',
            'end_time' => '14:00',
            'max_slots' => 20,
            'status' => 'available',
        ]);

        Livewire::test(\App\Livewire\User\TimeSlotPicker::class)
            ->call('updateSlots', $campaign->id, '2026-05-01')
            ->assertSet('campaignId', $campaign->id)
            ->assertSet('date', '2026-05-01')
            ->assertSee('09:00')
            ->assertSee('13:00');
    }

    public function test_user_can_open_booking_details_from_history(): void
    {
        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $user = User::create([
            'clinic_id' => $clinic->id,
            'name' => 'LINE User',
            'email' => 'line-history@example.com',
            'line_user_id' => 'line-user-history',
            'password' => Hash::make('password'),
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
            'date' => now()->addDay()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'max_slots' => 20,
            'status' => 'available',
        ]);

        $booking = Booking::create([
            'clinic_id' => $clinic->id,
            'user_id' => $user->id,
            'camp_id' => $campaign->id,
            'slot_id' => $slot->id,
            'status' => 'confirmed',
            'notes' => 'Bring student ID',
        ]);

        Livewire::actingAs($user, 'user')
            ->test(\App\Livewire\User\MyBookings::class)
            ->call('showDetails', $booking->id)
            ->assertDispatched('open-modal')
            ->assertSet('selectedBooking.id', $booking->id)
            ->assertSee($booking->booking_code)
            ->assertSee('Bring student ID');
    }
}
