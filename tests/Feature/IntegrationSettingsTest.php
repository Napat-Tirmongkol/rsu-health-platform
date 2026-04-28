<?php

namespace Tests\Feature;

use App\Livewire\Admin\IntegrationSettingsManager;
use App\Models\Admin;
use App\Models\Clinic;
use App\Models\SiteSetting;
use App\Services\IntegrationSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IntegrationSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_platform_admin_can_access_integration_settings_page(): void
    {
        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $admin = Admin::create([
            'clinic_id' => $clinic->id,
            'name' => 'Platform Admin',
            'email' => 'platform-admin@example.com',
            'google_id' => 'google-platform-admin',
            'module_permissions' => ['*'],
        ]);

        $this->actingAs($admin, 'admin')
            ->withSession(['clinic_id' => $clinic->id])
            ->get(route('admin.system_settings'))
            ->assertOk()
            ->assertSee('Integration Center')
            ->assertSee('LINE Messaging API')
            ->assertSee('Gemini API');
    }

    public function test_non_platform_admin_cannot_access_integration_settings_page(): void
    {
        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $admin = Admin::create([
            'clinic_id' => $clinic->id,
            'name' => 'Borrow Admin',
            'email' => 'borrow-admin@example.com',
            'google_id' => 'google-borrow-admin',
            'module_permissions' => ['borrow'],
        ]);

        $this->actingAs($admin, 'admin')
            ->withSession(['clinic_id' => $clinic->id])
            ->get(route('admin.system_settings'))
            ->assertForbidden();
    }

    public function test_integration_settings_are_saved_with_encrypted_sensitive_values(): void
    {
        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $admin = Admin::create([
            'clinic_id' => $clinic->id,
            'name' => 'Platform Admin',
            'email' => 'platform-admin-save@example.com',
            'google_id' => 'google-platform-admin-save',
            'module_permissions' => ['*'],
        ]);

        $this->actingAs($admin, 'admin')->withSession(['clinic_id' => $clinic->id]);
        Livewire::actingAs($admin, 'admin');

        Livewire::test(IntegrationSettingsManager::class)
            ->set('settings.mail_mailer', 'smtp')
            ->set('settings.mail_host', 'smtp.example.com')
            ->set('settings.mail_port', 587)
            ->set('settings.mail_username', 'mailer-user')
            ->set('settings.mail_password', 'super-secret-password')
            ->set('settings.mail_from_address', 'clinic@example.com')
            ->set('settings.mail_from_name', 'RSU Clinic')
            ->set('settings.mail_scheme', 'tls')
            ->set('settings.line_messaging_enabled', true)
            ->set('settings.line_channel_id', '1234567890')
            ->set('settings.line_channel_secret', 'line-secret')
            ->set('settings.line_channel_access_token', 'line-access-token')
            ->set('settings.line_webhook_url', 'https://example.com/webhook/line')
            ->set('settings.gemini_enabled', true)
            ->set('settings.gemini_api_key', 'gemini-secret-key')
            ->set('settings.gemini_model', 'gemini-1.5-pro')
            ->set('settings.gemini_base_url', 'https://generativelanguage.googleapis.com')
            ->set('settings.gemini_system_prompt', 'You are the clinic assistant.')
            ->call('save')
            ->assertSee('บันทึกค่า Integration Settings เรียบร้อยแล้ว');

        $mailPassword = SiteSetting::where('key', 'mail_password')->firstOrFail();
        $lineSecret = SiteSetting::where('key', 'line_channel_secret')->firstOrFail();
        $geminiApiKey = SiteSetting::where('key', 'gemini_api_key')->firstOrFail();

        $this->assertSame('encrypted', $mailPassword->type);
        $this->assertSame('encrypted', $lineSecret->type);
        $this->assertSame('encrypted', $geminiApiKey->type);
        $this->assertNotSame('super-secret-password', $mailPassword->value);
        $this->assertNotSame('line-secret', $lineSecret->value);
        $this->assertNotSame('gemini-secret-key', $geminiApiKey->value);

        $service = app(IntegrationSettingsService::class);
        $values = $service->load();

        $this->assertSame('smtp', $values['mail_mailer']);
        $this->assertSame('super-secret-password', $values['mail_password']);
        $this->assertTrue($values['line_messaging_enabled']);
        $this->assertSame('line-secret', $values['line_channel_secret']);
        $this->assertTrue($values['gemini_enabled']);
        $this->assertSame('gemini-secret-key', $values['gemini_api_key']);
    }
}
