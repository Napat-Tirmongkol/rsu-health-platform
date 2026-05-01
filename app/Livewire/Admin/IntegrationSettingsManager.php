<?php

namespace App\Livewire\Admin;

use App\Services\IntegrationSettingsService;
use App\Services\NotificationDeliveryService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class IntegrationSettingsManager extends Component
{
    public array $settings = [];
    public string $testEmailRecipient = '';
    public string $testLineRecipient = '';
    public string $testGeminiStatus = '';
    public string $testGeminiError = '';

    public function mount(IntegrationSettingsService $service): void
    {
        $this->settings = $service->load();
        $this->testEmailRecipient = (string) (Auth::guard('admin')->user()?->email ?? '');
    }

    public function save(IntegrationSettingsService $service): void
    {
        $this->validate($service->rules());

        $service->save($this->settings);
        $this->settings = $service->load();

        $message = 'บันทึก Integration Settings และ Notification Rules เรียบร้อยแล้ว';
        session()->flash('integration_settings_message', $message);
        $this->dispatch('swal:success', title: 'บันทึกสำเร็จ', message: $message);
    }

    public function sendTestEmail(NotificationDeliveryService $delivery): void
    {
        $this->validate([
            'settings.mail_mailer' => 'required|in:smtp,sendmail,log',
            'settings.mail_host' => 'nullable|string|max:255',
            'settings.mail_port' => 'nullable|integer|min:1|max:65535',
            'settings.mail_username' => 'nullable|string|max:255',
            'settings.mail_password' => 'nullable|string|max:255',
            'settings.mail_from_address' => 'nullable|email|max:255',
            'settings.mail_from_name' => 'nullable|string|max:255',
            'settings.mail_scheme' => 'nullable|in:,tls,ssl',
            'testEmailRecipient' => 'required|email',
        ]);

        try {
            app(IntegrationSettingsService::class)->save($this->settings);
            $result = $delivery->sendTestEmail($this->testEmailRecipient);
        } catch (\Throwable $e) {
            $message = 'ส่งอีเมลทดสอบไม่สำเร็จ: '.$e->getMessage();
            session()->flash('integration_test_error', $message);
            $this->dispatch('swal:error', title: 'ทดสอบ SMTP ไม่สำเร็จ', message: $message);

            return;
        }

        if (($result['mailer'] ?? '') === 'log') {
            $message = 'ระบบบันทึกอีเมลทดสอบลง laravel.log แล้ว เพราะ Mailer ยังเป็น log และยังไม่ได้ส่งออก SMTP จริง';
            session()->flash('integration_test_message', $message);
            $this->dispatch('swal:info', title: 'บันทึกลง Log', message: $message);

            return;
        }

        $message = 'ส่งอีเมลทดสอบเรียบร้อยแล้ว ผ่าน '.$result['mailer'].' จาก '.$result['from_name'].' <'.$result['from_address'].'>';
        session()->flash(
            'integration_test_message',
            $message
        );
        $this->dispatch('swal:success', title: 'ทดสอบ SMTP สำเร็จ', message: $message);
    }

    public function sendTestLine(NotificationDeliveryService $delivery): void
    {
        $this->validate([
            'settings.line_messaging_enabled' => 'nullable|boolean',
            'settings.line_channel_id' => 'nullable|string|max:255',
            'settings.line_channel_secret' => 'nullable|string|max:255',
            'settings.line_channel_access_token' => 'nullable|string|max:4000',
            'settings.line_webhook_url' => 'nullable|url|max:255',
            'testLineRecipient' => 'required|string|max:255',
        ]);

        try {
            app(IntegrationSettingsService::class)->save($this->settings);
            $delivery->sendTestLine($this->testLineRecipient);
        } catch (\Throwable $e) {
            $message = 'ส่ง LINE ทดสอบไม่สำเร็จ: '.$e->getMessage();
            session()->flash('integration_test_error', $message);
            $this->dispatch('swal:error', title: 'ทดสอบ LINE ไม่สำเร็จ', message: $message);

            return;
        }

        $message = 'ส่ง LINE ทดสอบเรียบร้อยแล้ว';
        session()->flash('integration_test_message', $message);
        $this->dispatch('swal:success', title: 'ทดสอบ LINE สำเร็จ', message: $message);
    }

    public function sendTestGemini(NotificationDeliveryService $delivery): void
    {
        $this->testGeminiStatus = '';
        $this->testGeminiError = '';

        try {
            $this->validate([
                'settings.gemini_enabled' => 'nullable|boolean',
                'settings.gemini_api_key' => 'nullable|string|max:4000',
                'settings.gemini_model' => 'nullable|string|max:255',
                'settings.gemini_base_url' => 'nullable|url|max:255',
                'settings.gemini_system_prompt' => 'nullable|string|max:10000',
            ]);

            app(IntegrationSettingsService::class)->save($this->settings);
            $result = $delivery->sendTestGemini();
        } catch (\Throwable $e) {
            $this->testGeminiError = $e->getMessage();
            $message = 'ทดสอบ Gemini ไม่สำเร็จ: '.$e->getMessage();
            session()->flash('integration_test_error', $message);
            $this->dispatch('swal:error', title: 'ทดสอบ Gemini ไม่สำเร็จ', message: $message);

            return;
        }

        $this->testGeminiStatus = trim(($result['model'] ?? '').' | '.($result['text'] ?? ''));
        $message = 'เชื่อมต่อ Gemini API สำเร็จแล้ว';
        session()->flash('integration_test_message', $message);
        $this->dispatch('swal:success', title: 'ทดสอบ Gemini สำเร็จ', message: $message);
    }

    public function render(IntegrationSettingsService $service)
    {
        return view('livewire.admin.integration-settings-manager', [
            'sections' => $service->sections(),
        ]);
    }
}
