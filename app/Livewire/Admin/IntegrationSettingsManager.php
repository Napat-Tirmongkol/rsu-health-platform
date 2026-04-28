<?php

namespace App\Livewire\Admin;

use App\Services\IntegrationSettingsService;
use Livewire\Component;

class IntegrationSettingsManager extends Component
{
    public array $settings = [];

    public function mount(IntegrationSettingsService $service): void
    {
        $this->settings = $service->load();
    }

    public function save(IntegrationSettingsService $service): void
    {
        $this->validate($service->rules());

        $service->save($this->settings);

        session()->flash('integration_settings_message', 'บันทึกค่า Integration Settings เรียบร้อยแล้ว');
    }

    public function render(IntegrationSettingsService $service)
    {
        return view('livewire.admin.integration-settings-manager', [
            'sections' => $service->sections(),
        ]);
    }
}
