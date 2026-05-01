<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\User;
use App\Services\IdentityQrCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UserIdentityQrTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_layout_renders_signed_identity_qr_svg(): void
    {
        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $user = User::create([
            'clinic_id' => $clinic->id,
            'name' => 'Test Patient',
            'full_name' => 'Test Patient',
            'status' => 'student',
            'student_personnel_id' => '6600001',
            'email' => 'qr-user@example.com',
            'line_user_id' => 'line-qr-user',
            'password' => Hash::make('password'),
        ]);

        $payload = json_decode(app(IdentityQrCode::class)->payload($user), true);

        $this->assertSame('rsu_health_identity', $payload['data']['type']);
        $this->assertSame($clinic->id, $payload['data']['clinic_id']);
        $this->assertSame($user->id, $payload['data']['user_id']);
        $this->assertSame('student', $payload['data']['person_type']);
        $this->assertSame('student_id', $payload['data']['identity_type']);
        $this->assertNotEmpty($payload['signature']);

        $this->actingAs($user, 'user')
            ->withSession(['clinic_id' => $clinic->id])
            ->get(route('user.hub'))
            ->assertOk()
            ->assertSee('RSU Medical Identity')
            ->assertSee('<svg', false)
            ->assertSee('6600001');
    }

    public function test_user_identity_falls_back_to_passport_for_general_user(): void
    {
        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $user = User::create([
            'clinic_id' => $clinic->id,
            'name' => 'Foreign Visitor',
            'full_name' => 'Foreign Visitor',
            'status' => 'other',
            'citizen_id' => 'AB1234567',
            'email' => 'passport-user@example.com',
            'line_user_id' => 'line-passport-user',
            'password' => Hash::make('password'),
        ]);

        $identity = $user->resolveIdentity();
        $payload = json_decode(app(IdentityQrCode::class)->payload($user), true);

        $this->assertSame('passport', $identity['type']);
        $this->assertSame('Passport', $identity['label']);
        $this->assertSame('AB1234567', $identity['value']);
        $this->assertSame('general', $payload['data']['person_type']);
        $this->assertSame('passport', $payload['data']['identity_type']);
    }

    public function test_user_pages_still_render_when_identity_table_is_not_migrated(): void
    {
        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $user = User::create([
            'clinic_id' => $clinic->id,
            'name' => 'Legacy User',
            'full_name' => 'Legacy User',
            'status' => 'student',
            'student_personnel_id' => '6600999',
            'email' => 'legacy-user@example.com',
            'line_user_id' => 'line-legacy-user',
            'password' => Hash::make('password'),
        ]);

        Schema::drop('user_identities');

        $this->actingAs($user, 'user')
            ->withSession(['clinic_id' => $clinic->id])
            ->get(route('user.profile'))
            ->assertOk()
            ->assertSee('6600999');
    }
}
