<?php

namespace App\Livewire\Portal;

use App\Models\Announcement;
use App\Models\Clinic;
use App\Models\SiteSetting;
use App\Scopes\TenantScope;
use Livewire\Component;
use Livewire\WithPagination;

class MaintenanceManager extends Component
{
    use WithPagination;

    public bool $maintenanceMode = false;
    public string $maintenanceMessage = '';

    public string $filterClinic = '';

    public bool $showModal = false;
    public bool $showDeleteModal = false;

    public ?int $editingId   = null;
    public ?int $deletingId  = null;

    public int    $announcementClinicId = 1;
    public string $title     = '';
    public string $content   = '';
    public string $type      = 'info';
    public bool   $isActive  = true;
    public string $startsAt  = '';
    public string $endsAt    = '';

    public function mount(): void
    {
        $setting = SiteSetting::withoutGlobalScope(TenantScope::class)
            ->where('clinic_id', 0)
            ->where('key', 'maintenance_mode')
            ->first();

        $this->maintenanceMode    = $setting ? (bool) $setting->value : false;
        $this->maintenanceMessage = SiteSetting::withoutGlobalScope(TenantScope::class)
            ->where('clinic_id', 0)
            ->where('key', 'maintenance_message')
            ->value('value') ?? '';
    }

    public function updatingFilterClinic(): void { $this->resetPage(); }

    public function toggleMaintenance(): void
    {
        $this->maintenanceMode = ! $this->maintenanceMode;

        SiteSetting::withoutGlobalScope(TenantScope::class)->updateOrCreate(
            ['clinic_id' => 0, 'key' => 'maintenance_mode'],
            ['value' => $this->maintenanceMode ? '1' : '0', 'type' => 'boolean']
        );

        session()->flash('maint_msg', $this->maintenanceMode
            ? 'เปิด Maintenance Mode แล้ว'
            : 'ปิด Maintenance Mode แล้ว');
    }

    public function saveMessage(): void
    {
        SiteSetting::withoutGlobalScope(TenantScope::class)->updateOrCreate(
            ['clinic_id' => 0, 'key' => 'maintenance_message'],
            ['value' => $this->maintenanceMessage, 'type' => 'string']
        );
        session()->flash('maint_msg', 'บันทึกข้อความ Maintenance เรียบร้อย');
    }

    public function openCreate(): void
    {
        $this->reset(['title', 'content', 'editingId', 'startsAt', 'endsAt']);
        $this->type                = 'info';
        $this->isActive            = true;
        $this->announcementClinicId = 1;
        $this->showModal           = true;
    }

    public function openEdit(int $id): void
    {
        $a = Announcement::withoutGlobalScope(TenantScope::class)->findOrFail($id);
        $this->editingId            = $id;
        $this->announcementClinicId = $a->clinic_id;
        $this->title                = $a->title;
        $this->content              = $a->content ?? '';
        $this->type                 = $a->type;
        $this->isActive             = $a->is_active;
        $this->startsAt             = $a->starts_at?->format('Y-m-d\TH:i') ?? '';
        $this->endsAt               = $a->ends_at?->format('Y-m-d\TH:i') ?? '';
        $this->showModal            = true;
    }

    public function save(): void
    {
        $this->validate([
            'announcementClinicId' => 'required|integer|exists:sys_clinics,id',
            'title'                => 'required|string|max:255',
            'content'              => 'nullable|string',
            'type'                 => 'required|in:info,warning,danger',
            'isActive'             => 'boolean',
            'startsAt'             => 'nullable|date',
            'endsAt'               => 'nullable|date|after_or_equal:startsAt',
        ]);

        $data = [
            'clinic_id' => $this->announcementClinicId,
            'title'     => $this->title,
            'content'   => $this->content,
            'type'      => $this->type,
            'is_active' => $this->isActive,
            'starts_at' => $this->startsAt ?: null,
            'ends_at'   => $this->endsAt   ?: null,
        ];

        if ($this->editingId) {
            Announcement::withoutGlobalScope(TenantScope::class)
                ->findOrFail($this->editingId)
                ->update($data);
            session()->flash('maint_msg', 'แก้ไขประกาศเรียบร้อย');
        } else {
            Announcement::withoutGlobalScope(TenantScope::class)->create($data);
            session()->flash('maint_msg', 'เพิ่มประกาศเรียบร้อย');
        }

        $this->showModal = false;
        $this->reset(['title', 'content', 'editingId', 'startsAt', 'endsAt']);
        $this->type     = 'info';
        $this->isActive = true;
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId      = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            Announcement::withoutGlobalScope(TenantScope::class)
                ->findOrFail($this->deletingId)
                ->delete();
            session()->flash('maint_msg', 'ลบประกาศเรียบร้อย');
        }
        $this->showDeleteModal = false;
        $this->deletingId      = null;
    }

    public function render()
    {
        $clinics = Clinic::orderBy('name')->get(['id', 'name']);

        $announcements = Announcement::withoutGlobalScope(TenantScope::class)
            ->with('clinic')
            ->when($this->filterClinic, fn ($q) => $q->where('clinic_id', $this->filterClinic))
            ->latest()
            ->paginate(20);

        return view('livewire.portal.maintenance-manager', [
            'clinics'       => $clinics,
            'announcements' => $announcements,
        ]);
    }
}
