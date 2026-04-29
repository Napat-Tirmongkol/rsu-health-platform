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

        session()->flash('integration_settings_message', 'บันทึกค่า Integration Settings และ Notification Rules เรียบร้อยแล้ว');
    }

    public function sendTestEmail(NotificationDeliveryService $delivery): void
    {
        $this->validate([
            'testEmailRecipient' => 'required|email',
        ]);

        try {
            $delivery->sendTestEmail($this->testEmailRecipient);
        } catch (\Throwable $e) {
            session()->flash('integration_test_error', 'ส่งอีเมลทดสอบไม่สำเร็จ: '.$e->getMessage());

            return;
        }

        session()->flash('integration_test_message', 'ส่งอีเมลทดสอบเรียบร้อยแล้ว');
    }

    public function sendTestLine(NotificationDeliveryService $delivery): void
    {
        $this->validate([
            'testLineRecipient' => 'required|string|max:255',
        ]);

        try {
            $delivery->sendTestLine($this->testLineRecipient);
        } catch (\Throwable $e) {
            session()->flash('integration_test_error', 'ส่ง LINE ทดสอบไม่สำเร็จ: '.$e->getMessage());

            return;
        }

        session()->flash('integration_test_message', 'ส่ง LINE ทดสอบเรียบร้อยแล้ว');
    }

    public function render(IntegrationSettingsService $service)
    {
        return view('livewire.admin.integration-settings-manager', [
            'sections' => $service->sections(),
        ]);
    }
}
