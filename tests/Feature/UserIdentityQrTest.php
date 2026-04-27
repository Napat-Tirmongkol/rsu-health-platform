<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\User;
use App\Services\IdentityQrCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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
            'student_personnel_id' => '6600001',
            'email' => 'qr-user@example.com',
            'line_user_id' => 'line-qr-user',
            'password' => Hash::make('password'),
        ]);

        $payload = json_decode(app(IdentityQrCode::class)->payload($user), true);

        $this->assertSame('rsu_health_identity', $payload['data']['type']);
        $this->assertSame($clinic->id, $payload['data']['clinic_id']);
        $this->assertSame($user->id, $payload['data']['user_id']);
        $this->assertSame('6600001', $payload['data']['student_personnel_id']);
        $this->assertNotEmpty($payload['signature']);

        $this->actingAs($user, 'user')
            ->withSession(['clinic_id' => $clinic->id])
            ->get(route('user.hub'))
            ->assertOk()
            ->assertSee('RSU Medical Identity')
            ->assertSee('<svg', false)
            ->assertSee('6600001');
    }
}
