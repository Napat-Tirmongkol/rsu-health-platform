<?php

namespace Tests\Feature;

use App\Livewire\Admin\IntegrationSettingsManager;
use App\Mail\IntegrationTestMail;
use App\Models\Admin;
use App\Models\Clinic;
use App\Models\SiteSetting;
use App\Services\IntegrationSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
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
            ->assertSee('Gemini API')
            ->assertSee('Notification Rules · e-Campaign')
            ->assertSee('Notification Rules · e-Borrow');
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

    public function test_integration_settings_are_saved_with_encrypted_sensitive_values_and_notification_rules(): void
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
            ->set('settings.campaign_booking_cancelled_line_enabled', true)
            ->set('settings.campaign_booking_cancelled_email_enabled', true)
            ->set('settings.campaign_booking_confirmed_line_enabled', false)
            ->set('settings.campaign_booking_confirmed_email_enabled', true)
            ->set('settings.campaign_booking_reminder_line_enabled', true)
            ->set('settings.campaign_booking_reminder_email_enabled', false)
            ->set('settings.borrow_request_approved_line_enabled', true)
            ->set('settings.borrow_request_approved_email_enabled', true)
            ->set('settings.borrow_overdue_line_enabled', true)
            ->set('settings.borrow_overdue_email_enabled', false)
            ->set('settings.borrow_fine_created_line_enabled', false)
            ->set('settings.borrow_fine_created_email_enabled', true)
            ->call('save')
            ->assertSee('บันทึกค่า Integration Settings และ Notification Rules เรียบร้อยแล้ว');

        $mailPassword = SiteSetting::withoutGlobalScopes()->where('key', 'mail_password')->firstOrFail();
        $lineSecret = SiteSetting::withoutGlobalScopes()->where('key', 'line_channel_secret')->firstOrFail();
        $geminiApiKey = SiteSetting::withoutGlobalScopes()->where('key', 'gemini_api_key')->firstOrFail();
        $cancelledLineRule = SiteSetting::withoutGlobalScopes()->where('key', 'campaign_booking_cancelled_line_enabled')->firstOrFail();
        $borrowApprovedLine = SiteSetting::withoutGlobalScopes()->where('key', 'borrow_request_approved_line_enabled')->firstOrFail();

        $this->assertSame('encrypted', $mailPassword->type);
        $this->assertSame('encrypted', $lineSecret->type);
        $this->assertSame('encrypted', $geminiApiKey->type);
        $this->assertNotSame('super-secret-password', $mailPassword->value);
        $this->assertNotSame('line-secret', $lineSecret->value);
        $this->assertNotSame('gemini-secret-key', $geminiApiKey->value);
        $this->assertSame('boolean', $cancelledLineRule->type);
        $this->assertSame('1', $cancelledLineRule->value);
        $this->assertSame('1', $borrowApprovedLine->value);

        $service = app(IntegrationSettingsService::class);
        $values = $service->load();

        $this->assertSame('smtp', $values['mail_mailer']);
        $this->assertSame('super-secret-password', $values['mail_password']);
        $this->assertTrue($values['line_messaging_enabled']);
        $this->assertSame('line-secret', $values['line_channel_secret']);
        $this->assertTrue($values['gemini_enabled']);
        $this->assertSame('gemini-secret-key', $values['gemini_api_key']);
        $this->assertTrue($values['campaign_booking_cancelled_line_enabled']);
        $this->assertTrue($values['campaign_booking_cancelled_email_enabled']);
        $this->assertFalse($values['campaign_booking_confirmed_line_enabled']);
        $this->assertTrue($values['campaign_booking_confirmed_email_enabled']);
        $this->assertTrue($values['borrow_request_approved_line_enabled']);
        $this->assertTrue($values['borrow_request_approved_email_enabled']);
        $this->assertTrue($service->notificationEnabled('campaign', 'booking_cancelled', 'line'));
        $this->assertTrue($service->notificationEnabled('campaign', 'booking_cancelled', 'email'));
        $this->assertFalse($service->notificationEnabled('campaign', 'booking_confirmed', 'line'));
        $this->assertTrue($service->notificationEnabled('campaign', 'booking_confirmed', 'email'));
        $this->assertTrue($service->notificationEnabled('borrow', 'request_approved', 'line'));
        $this->assertTrue($service->notificationEnabled('borrow', 'request_approved', 'email'));
    }

    public function test_admin_can_send_test_email_from_integration_center(): void
    {
        Mail::fake();

        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $admin = Admin::create([
            'clinic_id' => $clinic->id,
            'name' => 'Platform Admin',
            'email' => 'platform-admin-mail@example.com',
            'google_id' => 'google-platform-admin-mail',
            'module_permissions' => ['*'],
        ]);

        app(IntegrationSettingsService::class)->save(array_merge(
            app(IntegrationSettingsService::class)->defaults(),
            [
                'mail_mailer' => 'smtp',
                'mail_host' => 'smtp.example.com',
                'mail_port' => 587,
                'mail_from_address' => 'clinic@example.com',
                'mail_from_name' => 'RSU Clinic',
            ]
        ));

        $this->actingAs($admin, 'admin')->withSession(['clinic_id' => $clinic->id]);
        Livewire::actingAs($admin, 'admin');

        Livewire::test(IntegrationSettingsManager::class)
            ->set('testEmailRecipient', 'test-recipient@example.com')
            ->call('sendTestEmail')
            ->assertSee('ส่งอีเมลทดสอบเรียบร้อยแล้ว');

        Mail::assertSent(IntegrationTestMail::class);
    }

    public function test_admin_can_send_test_line_from_integration_center(): void
    {
        Http::fake([
            'https://api.line.me/v2/bot/message/push' => Http::response(['ok' => true], 200),
        ]);

        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $admin = Admin::create([
            'clinic_id' => $clinic->id,
            'name' => 'Platform Admin',
            'email' => 'platform-admin-line@example.com',
            'google_id' => 'google-platform-admin-line',
            'module_permissions' => ['*'],
        ]);

        app(IntegrationSettingsService::class)->save(array_merge(
            app(IntegrationSettingsService::class)->defaults(),
            [
                'line_messaging_enabled' => true,
                'line_channel_access_token' => 'test-line-token',
            ]
        ));

        $this->actingAs($admin, 'admin')->withSession(['clinic_id' => $clinic->id]);
        Livewire::actingAs($admin, 'admin');

        Livewire::test(IntegrationSettingsManager::class)
            ->set('testLineRecipient', 'U1234567890')
            ->call('sendTestLine')
            ->assertSee('ส่ง LINE ทดสอบเรียบร้อยแล้ว');

        Http::assertSent(fn ($request) => $request->url() === 'https://api.line.me/v2/bot/message/push');
    }
}
