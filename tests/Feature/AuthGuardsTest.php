<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Portal;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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
}
