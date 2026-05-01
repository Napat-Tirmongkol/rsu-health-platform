<?php

namespace App\Livewire\Portal;

use App\Models\Clinic;
use App\Models\SiteSetting;
use App\Scopes\TenantScope;
use Livewire\Component;
use Livewire\WithPagination;

class ClinicDataManager extends Component
{
    use WithPagination;

    public int $selectedClinicId = 0;
    public string $search = '';

    public bool $showModal = false;
    public bool $showDeleteModal = false;

    public ?int $editingId = null;
    public ?int $deletingId = null;

    public string $key   = '';
    public string $value = '';
    public string $type  = 'string';

    public function updatingSelectedClinicId(): void { $this->resetPage(); }
    public function updatingSearch(): void           { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->reset(['key', 'value', 'editingId']);
        $this->type      = 'string';
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $setting         = SiteSetting::withoutGlobalScope(TenantScope::class)->findOrFail($id);
        $this->editingId = $id;
        $this->key       = $setting->key;
        $this->value     = $setting->value ?? '';
        $this->type      = $setting->type ?? 'string';
        $this->showModal = true;
    }

    public function save(): void
    {
        $uniqueKey = 'unique:sys_site_settings,key,' . ($this->editingId ?? 'NULL') . ',id,clinic_id,' . $this->selectedClinicId;

        $this->validate([
            'key'   => "required|string|max:100|{$uniqueKey}",
            'value' => 'nullable|string|max:65535',
            'type'  => 'required|in:string,boolean,integer,json',
        ]);

        $data = [
            'clinic_id' => $this->selectedClinicId,
            'key'       => $this->key,
            'value'     => $this->value,
            'type'      => $this->type,
        ];

        if ($this->editingId) {
            SiteSetting::withoutGlobalScope(TenantScope::class)
                ->findOrFail($this->editingId)
                ->update($data);
            session()->flash('setting_msg', 'แก้ไข setting เรียบร้อย');
        } else {
            SiteSetting::withoutGlobalScope(TenantScope::class)->create($data);
            session()->flash('setting_msg', 'เพิ่ม setting เรียบร้อย');
        }

        $this->showModal = false;
        $this->reset(['key', 'value', 'editingId']);
        $this->type = 'string';
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId      = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            SiteSetting::withoutGlobalScope(TenantScope::class)
                ->findOrFail($this->deletingId)
                ->delete();
            session()->flash('setting_msg', 'ลบ setting เรียบร้อย');
        }
        $this->showDeleteModal = false;
        $this->deletingId      = null;
    }

    public function render()
    {
        $clinics = Clinic::orderBy('name')->get(['id', 'name']);

        $settings = SiteSetting::withoutGlobalScope(TenantScope::class)
            ->where('clinic_id', $this->selectedClinicId)
            ->when($this->search, fn ($q) => $q->where('key', 'like', "%{$this->search}%"))
            ->orderBy('key')
            ->paginate(20);

        return view('livewire.portal.clinic-data-manager', [
            'clinics'  => $clinics,
            'settings' => $settings,
        ]);
    }
}
