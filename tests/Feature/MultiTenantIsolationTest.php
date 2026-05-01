<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Admin;
use App\Models\Announcement;
use App\Models\Booking;
use App\Models\BorrowCategory;
use App\Models\BorrowItem;
use App\Models\BorrowRecord;
use App\Models\Campaign;
use App\Models\ChatMessage;
use App\Models\Clinic;
use App\Models\Portal;
use App\Models\SiteSetting;
use App\Models\Slot;
use App\Models\Staff;
use App\Models\User;
use App\Scopes\TenantScope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Multi-tenant isolation tests.
 *
 * Verifies that TenantScope, BelongsToClinic, and all HTTP / Livewire
 * boundaries correctly isolate data between clinics.
 *
 * Taxonomy:
 *   A – Model-level query isolation  (TenantScope applied)
 *   B – Auto clinic_id assignment    (creating records)
 *   C – withoutGlobalScope           (portal cross-clinic reads)
 *   D – Portal impersonation         (currentClinicId() override)
 *   E – HTTP endpoint isolation      (request-level)
 *   F – Livewire component isolation (Livewire::test)
 *   G – SiteSetting edge cases       (clinic_id = 0 global)
 */
class MultiTenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    // ── Shared factory helpers ────────────────────────────────────────────────

    private function makeClinic(string $slug, string $code): Clinic
    {
        return Clinic::create([
            'name'   => "Clinic {$slug}",
            'slug'   => $slug,
            'code'   => $code,
            'status' => 'active',
        ]);
    }

    private function makeUser(Clinic $clinic, string $suffix = ''): User
    {
        return User::create([
            'clinic_id'    => $clinic->id,
            'name'         => "User {$suffix}",
            'email'        => "user{$suffix}@example.com",
            'line_user_id' => "line-{$suffix}",
            'password'     => Hash::make('password'),
        ]);
    }

    private function makeAdmin(Clinic $clinic, string $suffix = ''): Admin
    {
        return Admin::create([
            'clinic_id' => $clinic->id,
            'name'      => "Admin {$suffix}",
            'email'     => "admin{$suffix}@example.com",
            'password'  => Hash::make('password'),
        ]);
    }

    private function makeStaff(Clinic $clinic, string $suffix = ''): Staff
    {
        return Staff::create([
            'clinic_id' => $clinic->id,
            'email'     => "staff{$suffix}@example.com",
            'password'  => Hash::make('password'),
            'status'    => 'active',
        ]);
    }

    private function makeCampaign(Clinic $clinic, string $title = 'Flu Vaccine'): Campaign
    {
        return Campaign::create([
            'clinic_id'      => $clinic->id,
            'title'          => $title,
            'description'    => 'Test campaign',
            'total_capacity' => 50,
            'status'         => 'active',
        ]);
    }

    private function makeSlot(Campaign $campaign, Clinic $clinic): Slot
    {
        return Slot::create([
            'camp_id'    => $campaign->id,
            'clinic_id'  => $clinic->id,
            'date'       => now()->addDay()->toDateString(),
            'start_time' => '09:00',
            'end_time'   => '10:00',
            'max_slots'  => 20,
            'status'     => 'available',
        ]);
    }

    private function makeBooking(Clinic $clinic, User $user, Campaign $campaign, Slot $slot, string $status = 'confirmed'): Booking
    {
        return Booking::create([
            'clinic_id' => $clinic->id,
            'user_id'   => $user->id,
            'camp_id'   => $campaign->id,
            'slot_id'   => $slot->id,
            'status'    => $status,
        ]);
    }

    // =========================================================================
    // A – Model-level query isolation
    // =========================================================================

    /** @test */
    public function booking_query_only_returns_records_of_the_current_clinic(): void
    {
        $clinicA = $this->makeClinic('alpha', 'ALPHA');
        $clinicB = $this->makeClinic('beta',  'BETA');

        $userA = $this->makeUser($clinicA, 'a1');
        $userB = $this->makeUser($clinicB, 'b1');

        $campaignA = $this->makeCampaign($clinicA, 'Alpha Flu');
        $campaignB = $this->makeCampaign($clinicB, 'Beta Flu');

        $slotA = $this->makeSlot($campaignA, $clinicA);
        $slotB = $this->makeSlot($campaignB, $clinicB);

        $bookingA = $this->makeBooking($clinicA, $userA, $campaignA, $slotA);
        $bookingB = $this->makeBooking($clinicB, $userB, $campaignB, $slotB);

        // Scoped to clinic A: only bookingA visible
        Session::put('clinic_id', $clinicA->id);
        $visibleToA = Booking::all();
        $this->assertCount(1, $visibleToA);
        $this->assertEquals($bookingA->id, $visibleToA->first()->id);

        // Scoped to clinic B: only bookingB visible
        Session::put('clinic_id', $clinicB->id);
        $visibleToB = Booking::all();
        $this->assertCount(1, $visibleToB);
        $this->assertEquals($bookingB->id, $visibleToB->first()->id);
    }

    /** @test */
    public function campaign_query_only_returns_records_of_the_current_clinic(): void
    {
        $clinicA = $this->makeClinic('alpha', 'ALPHA');
        $clinicB = $this->makeClinic('beta',  'BETA');

        $this->makeCampaign($clinicA, 'Alpha Campaign');
        $this->makeCampaign($clinicB, 'Beta Campaign');

        Session::put('clinic_id', $clinicA->id);
        $results = Campaign::all();
        $this->assertCount(1, $results);
        $this->assertEquals('Alpha Campaign', $results->first()->title);

        Session::put('clinic_id', $clinicB->id);
        $results = Campaign::all();
        $this->assertCount(1, $results);
        $this->assertEquals('Beta Campaign', $results->first()->title);
    }

    /** @test */
    public function user_query_only_returns_records_of_the_current_clinic(): void
    {
        $clinicA = $this->makeClinic('alpha', 'ALPHA');
        $clinicB = $this->makeClinic('beta',  'BETA');

        $this->makeUser($clinicA, 'alpha');
        $this->makeUser($clinicB, 'beta');

        Session::put('clinic_id', $clinicA->id);
        $this->assertCount(1, User::all());
        $this->assertEquals('User alpha', User::first()->name);

        Session::put('clinic_id', $clinicB->id);
        $this->assertCount(1, User::all());
        $this->assertEquals('User beta', User::first()->name);
    }

    /** @test */
    public function activity_log_query_only_returns_records_of_the_current_clinic(): void
    {
        $clinicA = $this->makeClinic('alpha', 'ALPHA');
        $clinicB = $this->makeClinic('beta',  'BETA');

        ActivityLog::create(['clinic_id' => $clinicA->id, 'action' => 'test.action.a', 'description' => 'Alpha log']);
        ActivityLog::create(['clinic_id' => $clinicB->id, 'action' => 'test.action.b', 'description' => 'Beta log']);

        Session::put('clinic_id', $clinicA->id);
        $logs = ActivityLog::all();
        $this->assertCount(1, $logs);
        $this->assertEquals('Alpha log', $logs->first()->description);

        Session::put('clinic_id', $clinicB->id);
        $logs = ActivityLog::all();
        $this->assertCount(1, $logs);
        $this->assertEquals('Beta log', $logs->first()->description);
    }

    /** @test */
    public function chat_message_query_only_returns_records_of_the_current_clinic(): void
    {
        $clinicA = $this->makeClinic('alpha', 'ALPHA');
        $clinicB = $this->makeClinic('beta',  'BETA');

        $userA = $this->makeUser($clinicA, 'cha');
        $userB = $this->makeUser($clinicB, 'chb');

        ChatMessage::create(['clinic_id' => $clinicA->id, 'user_id' => $userA->id, 'sender' => 'user', 'message' => 'Hello from A']);
        ChatMessage::create(['clinic_id' => $clinicB->id, 'user_id' => $userB->id, 'sender' => 'user', 'message' => 'Hello from B']);

        Session::put('clinic_id', $clinicA->id);
        $msgs = ChatMessage::all();
        $this->assertCount(1, $msgs);
        $this->assertEquals('Hello from A', $msgs->first()->message);

        Session::put('clinic_id', $clinicB->id);
        $msgs = ChatMessage::all();
        $this->assertCount(1, $msgs);
        $this->assertEquals('Hello from B', $msgs->first()->message);
    }

    /** @test */
    public function announcement_query_only_returns_records_of_the_current_clinic(): void
    {
        $clinicA = $this->makeClinic('alpha', 'ALPHA');
        $clinicB = $this->makeClinic('beta',  'BETA');

        Announcement::create(['clinic_id' => $clinicA->id, 'title' => 'Alpha Notice', 'content' => 'A', 'type' => 'info', 'is_active' => true]);
        Announcement::create(['clinic_id' => $clinicB->id, 'title' => 'Beta Notice',  'content' => 'B', 'type' => 'info', 'is_active' => true]);

        Session::put('clinic_id', $clinicA->id);
        $this->assertCount(1, Announcement::all());
        $this->assertEquals('Alpha Notice', Announcement::first()->title);

        Session::put('clinic_id', $clinicB->id);
        $this->assertCount(1, Announcement::all());
        $this->assertEquals('Beta Notice', Announcement::first()->title);
    }

    /** @test */
    public function borrow_category_query_only_returns_records_of_the_current_clinic(): void
    {
        $clinicA = $this->makeClinic('alpha', 'ALPHA');
        $clinicB = $this->makeClinic('beta',  'BETA');

        BorrowCategory::create(['clinic_id' => $clinicA->id, 'name' => 'Alpha Wheelchair', 'total_quantity' => 2, 'available_quantity' => 2, 'is_active' => true]);
        BorrowCategory::create(['clinic_id' => $clinicB->id, 'name' => 'Beta Crutches',    'total_quantity' => 3, 'available_quantity' => 3, 'is_active' => true]);

        Session::put('clinic_id', $clinicA->id);
        $cats = BorrowCategory::all();
        $this->assertCount(1, $cats);
        $this->assertEquals('Alpha Wheelchair', $cats->first()->name);

        Session::put('clinic_id', $clinicB->id);
        $cats = BorrowCategory::all();
        $this->assertCount(1, $cats);
        $this->assertEquals('Beta Crutches', $cats->first()->name);
    }

    /** @test */
    public function staff_query_only_returns_records_of_the_current_clinic(): void
    {
        $clinicA = $this->makeClinic('alpha', 'ALPHA');
        $clinicB = $this->makeClinic('beta',  'BETA');

        $this->makeStaff($clinicA, 'a');
        $this->makeStaff($clinicB, 'b');

        Session::put('clinic_id', $clinicA->id);
        $this->assertCount(1, Staff::all());
        $this->assertEquals('staffa@example.com', Staff::first()->email);

        Session::put('clinic_id', $clinicB->id);
        $this->assertCount(1, Staff::all());
        $this->assertEquals('staffb@example.com', Staff::first()->email);
    }

    /** @test */
    public function find_by_id_returns_null_when_record_belongs_to_different_clinic(): void
    {
        $clinicA = $this->makeClinic('alpha', 'ALPHA');
        $clinicB = $this->makeClinic('beta',  'BETA');

        $userA     = $this->makeUser($clinicA, 'fa');
        $campaignA = $this->makeCampaign($clinicA, 'Alpha Flu');
        $slotA     = $this->makeSlot($campaignA, $clinicA);
        $bookingA  = $this->makeBooking($clinicA, $userA, $campaignA, $slotA);

        // Scoped to clinic B → booking A must not be found
        Session::put('clinic_id', $clinicB->id);
        $this->assertNull(Booking::find($bookingA->id));
    }

    // =========================================================================
    // B – Auto clinic_id assignment on create
    // =========================================================================

    /** @test */
    public function booking_auto_assigns_clinic_id_from_session_when_omitted(): void
    {
        $clinic = $this->makeClinic('alpha', 'ALPHA');
        $user   = $this->makeUser($clinic, 'auto');

        Session::put('clinic_id', $clinic->id);

        $campaign = $this->makeCampaign($clinic);
        $slot     = $this->makeSlot($campaign, $clinic);

        // Omit clinic_id — BelongsToClinic must fill it
        $booking = Booking::create([
            'user_id' => $user->id,
            'camp_id' => $campaign->id,
            'slot_id' => $slot->id,
            'status'  => 'pending',
        ]);

        $this->assertEquals($clinic->id, $booking->fresh()->clinic_id);
    }

    /** @test */
    public function campaign_auto_assigns_clinic_id_from_session_when_omitted(): void
    {
        $clinic = $this->makeClinic('alpha', 'ALPHA');
        Session::put('clinic_id', $clinic->id);

        $campaign = Campaign::create([
            'title'          => 'Auto-Assign Campaign',
            'description'    => 'Test',
            'total_capacity' => 10,
            'status'         => 'active',
        ]);

        $this->assertEquals($clinic->id, $campaign->fresh()->clinic_id);
    }

    /** @test */
    public function activity_log_auto_assigns_clinic_id_from_session_when_omitted(): void
    {
        $clinic = $this->makeClinic('alpha', 'ALPHA');
        Session::put('clinic_id', $clinic->id);

        $log = ActivityLog::create([
            'action'      => 'test.auto_assign',
            'description' => 'Should inherit clinic_id',
        ]);

        $this->assertEquals($clinic->id, $log->fresh()->clinic_id);
    }

    // =========================================================================
    // C – withoutGlobalScope (portal cross-clinic reads)
    // =========================================================================

    /** @test */
    public function without_global_scope_returns_all_clinic_data_for_portal_reads(): void
    {
        $clinicA = $this->makeClinic('alpha', 'ALPHA');
        $clinicB = $this->makeClinic('beta',  'BETA');

        $this->makeCampaign($clinicA, 'Alpha Campaign');
        $this->makeCampaign($clinicB, 'Beta Campaign');

        Session::put('clinic_id', $clinicA->id);

        // Normal query: only clinic A
        $this->assertCount(1, Campaign::all());

        // Portal read: all clinics
        $all = Campaign::withoutGlobalScope(TenantScope::class)->get();
        $this->assertCount(2, $all);
        $this->assertTrue($all->pluck('title')->contains('Alpha Campaign'));
        $this->assertTrue($all->pluck('title')->contains('Beta Campaign'));
    }

    /** @test */
    public function activity_log_cross_clinic_query_returns_all_records_without_scope(): void
    {
        $clinicA = $this->makeClinic('alpha', 'ALPHA');
        $clinicB = $this->makeClinic('beta',  'BETA');

        ActivityLog::create(['clinic_id' => $clinicA->id, 'action' => 'a', 'description' => 'Log A']);
        ActivityLog::create(['clinic_id' => $clinicB->id, 'action' => 'b', 'description' => 'Log B']);

        Session::put('clinic_id', $clinicA->id);
        $this->assertCount(1, ActivityLog::all());

        $all = ActivityLog::withoutGlobalScope(TenantScope::class)->get();
        $this->assertCount(2, $all);
    }

    // =========================================================================
    // D – Portal impersonation (currentClinicId() override)
    // =========================================================================

    /** @test */
    public function portal_impersonation_changes_current_clinic_id(): void
    {
        $clinicA = $this->makeClinic('alpha', 'ALPHA');
        $clinicB = $this->makeClinic('beta',  'BETA');

        $portal = Portal::create([
            'name'     => 'Super Admin',
            'email'    => 'portal-imp@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($portal, 'portal');

        // Set impersonation to clinic B
        Session::put('portal_impersonating_clinic_id', $clinicB->id);

        $this->assertEquals($clinicB->id, currentClinicId());
    }

    /** @test */
    public function impersonation_session_routes_data_to_impersonated_clinic(): void
    {
        $clinicA = $this->makeClinic('alpha', 'ALPHA');
        $clinicB = $this->makeClinic('beta',  'BETA');

        $this->makeCampaign($clinicA, 'Alpha Campaign');
        $this->makeCampaign($clinicB, 'Beta Campaign');

        $portal = Portal::create([
            'name'     => 'Super Admin',
            'email'    => 'portal-route@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($portal, 'portal');

        // Impersonating clinic B
        Session::put('portal_impersonating_clinic_id', $clinicB->id);
        $this->assertEquals($clinicB->id, currentClinicId());

        $campaigns = Campaign::all();
        $this->assertCount(1, $campaigns);
        $this->assertEquals('Beta Campaign', $campaigns->first()->title);
    }

    /** @test */
    public function stopping_impersonation_restores_session_clinic_id(): void
    {
        $clinicA = $this->makeClinic('alpha', 'ALPHA');
        $clinicB = $this->makeClinic('beta',  'BETA');

        $portal = Portal::create([
            'name'     => 'Super Admin',
            'email'    => 'portal-stop@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($portal, 'portal');

        Session::put('clinic_id', $clinicA->id);
        Session::put('portal_impersonating_clinic_id', $clinicB->id);

        // During impersonation → clinic B
        $this->assertEquals($clinicB->id, currentClinicId());

        // Stop impersonation
        Session::forget('portal_impersonating_clinic_id');

        // currentClinicId() falls back to session clinic_id → clinic A
        $this->assertEquals($clinicA->id, currentClinicId());
    }

    // =========================================================================
    // E – HTTP endpoint isolation
    // =========================================================================

    /** @test */
    public function user_booking_history_page_only_shows_own_clinic_bookings(): void
    {
        $clinicA = $this->makeClinic('alpha', 'ALPHA');
        $clinicB = $this->makeClinic('beta',  'BETA');

        $userA     = $this->makeUser($clinicA, 'httpa');
        $campaignA = $this->makeCampaign($clinicA, 'Alpha Campaign');
        $slotA     = $this->makeSlot($campaignA, $clinicA);
        $bookingA  = $this->makeBooking($clinicA, $userA, $campaignA, $slotA, 'confirmed');

        $userB     = $this->makeUser($clinicB, 'httpb');
        $campaignB = $this->makeCampaign($clinicB, 'Beta Campaign');
        $slotB     = $this->makeSlot($campaignB, $clinicB);
        $bookingB  = $this->makeBooking($clinicB, $userB, $campaignB, $slotB, 'confirmed');

        // User A sees only Alpha Campaign in their history
        $this->actingAs($userA, 'user')
            ->withSession(['clinic_id' => $clinicA->id])
            ->get(route('user.history'))
            ->assertOk()
            ->assertSee('Alpha Campaign')
            ->assertDontSee('Beta Campaign');
    }

    /** @test */
    public function admin_dashboard_only_sees_own_clinic_campaigns(): void
    {
        $clinicA = $this->makeClinic('alpha', 'ALPHA');
        $clinicB = $this->makeClinic('beta',  'BETA');

        $this->makeCampaign($clinicA, 'Alpha Only Campaign');
        $this->makeCampaign($clinicB, 'Beta Only Campaign');

        $adminA = $this->makeAdmin($clinicA, 'httpadmina');

        $this->actingAs($adminA, 'admin')
            ->withSession(['clinic_id' => $clinicA->id])
            ->get(route('admin.campaigns'))
            ->assertOk()
            ->assertSee('Alpha Only Campaign')
            ->assertDontSee('Beta Only Campaign');
    }

    /** @test */
    public function user_from_clinic_a_cannot_cancel_booking_belonging_to_clinic_b(): void
    {
        $clinicA = $this->makeClinic('alpha', 'ALPHA');
        $clinicB = $this->makeClinic('beta',  'BETA');

        $userA     = $this->makeUser($clinicA, 'cxa');
        $userB     = $this->makeUser($clinicB, 'cxb');
        $campaignB = $this->makeCampaign($clinicB, 'Beta Campaign');
        $slotB     = $this->makeSlot($campaignB, $clinicB);
        $bookingB  = $this->makeBooking($clinicB, $userB, $campaignB, $slotB, 'confirmed');

        // User A (clinic A session) tries to cancel booking B — must not be found
        Session::put('clinic_id', $clinicA->id);
        $this->assertNull(Booking::find($bookingB->id));
    }

    /** @test */
    public function portal_impersonate_route_sets_correct_session_values(): void
    {
        $clinicA = $this->makeClinic('alpha', 'ALPHA');
        $clinicB = $this->makeClinic('beta',  'BETA');

        $portal = Portal::create([
            'name'     => 'Portal Admin',
            'email'    => 'portal-imp-route@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($portal, 'portal')
            ->withSession(['clinic_id' => $clinicA->id])
            ->post(route('portal.impersonate', $clinicB))
            ->assertRedirect(route('portal.clinics'));

        $this->assertEquals($clinicB->id, session('portal_impersonating_clinic_id'));
        $this->assertEquals($clinicB->id, session('clinic_id'));
    }

    /** @test */
    public function portal_stop_impersonate_route_clears_impersonation_session(): void
    {
        $clinicA = $this->makeClinic('alpha', 'ALPHA');
        $clinicB = $this->makeClinic('beta',  'BETA');

        $portal = Portal::create([
            'name'     => 'Portal Admin',
            'email'    => 'portal-stop-route@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($portal, 'portal')
            ->withSession([
                'clinic_id'                      => $clinicB->id,
                'portal_impersonating_clinic_id' => $clinicB->id,
                'portal_impersonating_clinic_name' => 'Clinic beta',
            ])
            ->post(route('portal.stop-impersonate'))
            ->assertRedirect(route('portal.clinics'));

        $this->assertNull(session('portal_impersonating_clinic_id'));
        $this->assertNull(session('portal_impersonating_clinic_name'));
    }

    // =========================================================================
    // F – Livewire component isolation
    // =========================================================================

    /** @test */
    public function my_bookings_component_only_shows_bookings_of_current_clinic(): void
    {
        $clinicA = $this->makeClinic('alpha', 'ALPHA');
        $clinicB = $this->makeClinic('beta',  'BETA');

        $userA     = $this->makeUser($clinicA, 'lwua');
        $userB     = $this->makeUser($clinicB, 'lwub');
        $campaignA = $this->makeCampaign($clinicA, 'Alpha Livewire Campaign');
        $campaignB = $this->makeCampaign($clinicB, 'Beta Livewire Campaign');
        $slotA     = $this->makeSlot($campaignA, $clinicA);
        $slotB     = $this->makeSlot($campaignB, $clinicB);

        $this->makeBooking($clinicA, $userA, $campaignA, $slotA, 'confirmed');
        $this->makeBooking($clinicB, $userB, $campaignB, $slotB, 'confirmed');

        // userA's MyBookings must see Alpha campaign, not Beta
        Session::put('clinic_id', $clinicA->id);

        Livewire::actingAs($userA, 'user')
            ->test(\App\Livewire\User\MyBookings::class)
            ->assertSee('Alpha Livewire Campaign')
            ->assertDontSee('Beta Livewire Campaign');
    }

    /** @test */
    public function time_slot_picker_only_loads_slots_of_current_clinic_campaign(): void
    {
        $clinicA = $this->makeClinic('alpha', 'ALPHA');
        $clinicB = $this->makeClinic('beta',  'BETA');

        $userA     = $this->makeUser($clinicA, 'slua');
        $campaignA = $this->makeCampaign($clinicA, 'Alpha Slot Campaign');
        $campaignB = $this->makeCampaign($clinicB, 'Beta Slot Campaign');

        $slotA = $this->makeSlot($campaignA, $clinicA);

        // Slot B for clinic B's campaign — must not appear in clinic A context
        Slot::create([
            'camp_id'    => $campaignB->id,
            'clinic_id'  => $clinicB->id,
            'date'       => now()->addDay()->toDateString(),
            'start_time' => '14:00',
            'end_time'   => '15:00',
            'max_slots'  => 10,
            'status'     => 'available',
        ]);

        Session::put('clinic_id', $clinicA->id);

        Livewire::actingAs($userA, 'user')
            ->test(\App\Livewire\User\TimeSlotPicker::class)
            ->call('updateSlots', $campaignA->id, now()->addDay()->toDateString())
            ->assertSee('09:00')
            ->assertDontSee('14:00');
    }

    /** @test */
    public function booking_manager_livewire_component_only_shows_current_clinic_bookings(): void
    {
        $clinicA = $this->makeClinic('alpha', 'ALPHA');
        $clinicB = $this->makeClinic('beta',  'BETA');

        $userA     = $this->makeUser($clinicA, 'bma');
        $userB     = $this->makeUser($clinicB, 'bmb');
        $campaignA = $this->makeCampaign($clinicA, 'Alpha Manager Campaign');
        $campaignB = $this->makeCampaign($clinicB, 'Beta Manager Campaign');
        $slotA     = $this->makeSlot($campaignA, $clinicA);
        $slotB     = $this->makeSlot($campaignB, $clinicB);

        $this->makeBooking($clinicA, $userA, $campaignA, $slotA);
        $this->makeBooking($clinicB, $userB, $campaignB, $slotB);

        $adminA = $this->makeAdmin($clinicA, 'bma');

        Session::put('clinic_id', $clinicA->id);

        Livewire::actingAs($adminA, 'admin')
            ->test(\App\Livewire\Admin\BookingManager::class)
            ->assertSee('Alpha Manager Campaign')
            ->assertDontSee('Beta Manager Campaign');
    }

    /** @test */
    public function activity_log_viewer_livewire_only_shows_current_clinic_logs(): void
    {
        $clinicA = $this->makeClinic('alpha', 'ALPHA');
        $clinicB = $this->makeClinic('beta',  'BETA');

        ActivityLog::create(['clinic_id' => $clinicA->id, 'action' => 'a.event', 'description' => 'Alpha action']);
        ActivityLog::create(['clinic_id' => $clinicB->id, 'action' => 'b.event', 'description' => 'Beta action']);

        $adminA = $this->makeAdmin($clinicA, 'lva');

        Session::put('clinic_id', $clinicA->id);

        Livewire::actingAs($adminA, 'admin')
            ->test(\App\Livewire\Admin\ActivityLogViewer::class)
            ->assertSee('Alpha action')
            ->assertDontSee('Beta action');
    }

    // =========================================================================
    // G – SiteSetting edge cases (clinic_id = 0 global)
    // =========================================================================

    /** @test */
    public function site_setting_from_clinic_b_is_not_visible_in_clinic_a_scoped_query(): void
    {
        $clinicA = $this->makeClinic('alpha', 'ALPHA');
        $clinicB = $this->makeClinic('beta',  'BETA');

        SiteSetting::create(['clinic_id' => $clinicA->id, 'key' => 'brand_color', 'value' => '#0369a1', 'type' => 'string']);
        SiteSetting::create(['clinic_id' => $clinicB->id, 'key' => 'brand_color', 'value' => '#16a34a', 'type' => 'string']);

        // Clinic A scope: only sees its own setting
        Session::put('clinic_id', $clinicA->id);
        $scoped = SiteSetting::where('key', 'brand_color')->get();
        $this->assertCount(1, $scoped);
        $this->assertEquals('#0369a1', $scoped->first()->value);

        // Clinic B scope: only sees its own setting
        Session::put('clinic_id', $clinicB->id);
        $scoped = SiteSetting::where('key', 'brand_color')->get();
        $this->assertCount(1, $scoped);
        $this->assertEquals('#16a34a', $scoped->first()->value);
    }

    /** @test */
    public function site_setting_without_scope_returns_all_clinics_settings(): void
    {
        $clinicA = $this->makeClinic('alpha', 'ALPHA');
        $clinicB = $this->makeClinic('beta',  'BETA');

        SiteSetting::create(['clinic_id' => $clinicA->id, 'key' => 'site_name', 'value' => 'Alpha Hub', 'type' => 'string']);
        SiteSetting::create(['clinic_id' => $clinicB->id, 'key' => 'site_name', 'value' => 'Beta Hub',  'type' => 'string']);

        // Normal scoped query (clinic A) sees only 1
        Session::put('clinic_id', $clinicA->id);
        $this->assertCount(1, SiteSetting::where('key', 'site_name')->get());

        // Cross-clinic lookup (e.g. portal read) finds all 2
        $all = SiteSetting::withoutGlobalScope(TenantScope::class)
            ->where('key', 'site_name')
            ->get();
        $this->assertCount(2, $all);
        $this->assertTrue($all->pluck('value')->contains('Alpha Hub'));
        $this->assertTrue($all->pluck('value')->contains('Beta Hub'));
    }

    /** @test */
    public function two_clinics_can_have_same_setting_key_with_different_values(): void
    {
        $clinicA = $this->makeClinic('alpha', 'ALPHA');
        $clinicB = $this->makeClinic('beta',  'BETA');

        SiteSetting::create(['clinic_id' => $clinicA->id, 'key' => 'site_name', 'value' => 'Alpha Hub', 'type' => 'string']);
        SiteSetting::create(['clinic_id' => $clinicB->id, 'key' => 'site_name', 'value' => 'Beta Hub',  'type' => 'string']);

        Session::put('clinic_id', $clinicA->id);
        $this->assertEquals('Alpha Hub', SiteSetting::where('key', 'site_name')->value('value'));

        Session::put('clinic_id', $clinicB->id);
        $this->assertEquals('Beta Hub', SiteSetting::where('key', 'site_name')->value('value'));
    }

    // =========================================================================
    // Extra – Relationship scoping
    // =========================================================================

    /** @test */
    public function booking_relationship_only_resolves_campaign_from_same_clinic(): void
    {
        $clinicA = $this->makeClinic('alpha', 'ALPHA');
        $clinicB = $this->makeClinic('beta',  'BETA');

        $userA     = $this->makeUser($clinicA, 'rela');
        $campaignA = $this->makeCampaign($clinicA, 'Alpha Rel Campaign');
        $slotA     = $this->makeSlot($campaignA, $clinicA);
        $bookingA  = $this->makeBooking($clinicA, $userA, $campaignA, $slotA, 'confirmed');

        // Clinic B has a campaign with same title — must not bleed into booking A's relationship
        $this->makeCampaign($clinicB, 'Beta Rel Campaign');

        Session::put('clinic_id', $clinicA->id);

        $loadedBooking = Booking::with('campaign')->find($bookingA->id);
        $this->assertNotNull($loadedBooking);
        $this->assertEquals('Alpha Rel Campaign', $loadedBooking->campaign->title);
    }

    /** @test */
    public function borrow_item_query_is_isolated_per_clinic(): void
    {
        $clinicA = $this->makeClinic('alpha', 'ALPHA');
        $clinicB = $this->makeClinic('beta',  'BETA');

        $catA = BorrowCategory::create(['clinic_id' => $clinicA->id, 'name' => 'Cat A', 'total_quantity' => 1, 'available_quantity' => 1, 'is_active' => true]);
        $catB = BorrowCategory::create(['clinic_id' => $clinicB->id, 'name' => 'Cat B', 'total_quantity' => 1, 'available_quantity' => 1, 'is_active' => true]);

        BorrowItem::create(['clinic_id' => $clinicA->id, 'category_id' => $catA->id, 'name' => 'Item Alpha', 'serial_number' => 'A001', 'status' => 'available']);
        BorrowItem::create(['clinic_id' => $clinicB->id, 'category_id' => $catB->id, 'name' => 'Item Beta',  'serial_number' => 'B001', 'status' => 'available']);

        Session::put('clinic_id', $clinicA->id);
        $items = BorrowItem::all();
        $this->assertCount(1, $items);
        $this->assertEquals('Item Alpha', $items->first()->name);

        Session::put('clinic_id', $clinicB->id);
        $items = BorrowItem::all();
        $this->assertCount(1, $items);
        $this->assertEquals('Item Beta', $items->first()->name);
    }
}
