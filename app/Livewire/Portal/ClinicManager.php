<?php

namespace App\Livewire\Portal;

use App\Models\Clinic;
use Livewire\Component;
use Livewire\WithPagination;

class ClinicManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';

    public bool $showModal = false;
    public bool $showDeleteModal = false;

    public ?int $editingId = null;
    public ?int $deletingId = null;

    public string $name          = '';
    public string $slug          = '';
    public string $code          = '';
    public string $domain        = '';
    public string $status        = 'active';
    public string $description   = '';
    public string $logoUrl       = '';
    public string $primaryColor  = '#0ea5e9';
    public string $contactEmail  = '';
    public string $contactPhone  = '';

    public function updatingSearch(): void      { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->reset(['name', 'slug', 'code', 'domain', 'description', 'logoUrl', 'contactEmail', 'contactPhone', 'editingId']);
        $this->status       = 'active';
        $this->primaryColor = '#0ea5e9';
        $this->showModal    = true;
    }

    public function openEdit(int $id): void
    {
        $clinic              = Clinic::findOrFail($id);
        $this->editingId     = $id;
        $this->name          = $clinic->name;
        $this->slug          = $clinic->slug;
        $this->code          = $clinic->code;
        $this->domain        = $clinic->domain ?? '';
        $this->status        = $clinic->status;
        $this->description   = $clinic->description ?? '';
        $this->logoUrl       = $clinic->logo_url ?? '';
        $this->primaryColor  = $clinic->primary_color ?? '#0ea5e9';
        $this->contactEmail  = $clinic->contact_email ?? '';
        $this->contactPhone  = $clinic->contact_phone ?? '';
        $this->showModal     = true;
    }

    public function save(): void
    {
        $uniqueSlug = 'unique:sys_clinics,slug' . ($this->editingId ? ",{$this->editingId}" : '');
        $uniqueCode = 'unique:sys_clinics,code' . ($this->editingId ? ",{$this->editingId}" : '');

        $this->validate([
            'name'         => 'required|string|max:255',
            'slug'         => "required|string|max:100|alpha_dash|{$uniqueSlug}",
            'code'         => "required|string|max:50|{$uniqueCode}",
            'domain'       => 'nullable|string|max:255',
            'status'       => 'required|in:active,inactive',
            'description'  => 'nullable|string|max:1000',
            'logoUrl'      => 'nullable|url|max:500',
            'primaryColor' => 'nullable|string|max:20',
            'contactEmail' => 'nullable|email|max:255',
            'contactPhone' => 'nullable|string|max:30',
        ]);

        $data = [
            'name'          => $this->name,
            'slug'          => $this->slug,
            'code'          => $this->code,
            'domain'        => $this->domain ?: null,
            'status'        => $this->status,
            'description'   => $this->description ?: null,
            'logo_url'      => $this->logoUrl ?: null,
            'primary_color' => $this->primaryColor ?: null,
            'contact_email' => $this->contactEmail ?: null,
            'contact_phone' => $this->contactPhone ?: null,
        ];

        if ($this->editingId) {
            Clinic::findOrFail($this->editingId)->update($data);
            session()->flash('clinic_msg', 'แก้ไขข้อมูลคลินิกเรียบร้อย');
        } else {
            Clinic::create($data);
            session()->flash('clinic_msg', 'เพิ่มคลินิกใหม่เรียบร้อย');
        }

        $this->showModal = false;
        $this->reset(['name', 'slug', 'code', 'domain', 'description', 'logoUrl', 'contactEmail', 'contactPhone', 'editingId']);
        $this->status       = 'active';
        $this->primaryColor = '#0ea5e9';
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId      = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            Clinic::findOrFail($this->deletingId)->delete();
            session()->flash('clinic_msg', 'ลบคลินิกเรียบร้อยแล้ว');
        }
        $this->showDeleteModal = false;
        $this->deletingId      = null;
    }

    public function render()
    {
        $clinics = Clinic::withCount(['users', 'staff'])
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('slug', 'like', "%{$this->search}%")
                  ->orWhere('code', 'like', "%{$this->search}%");
            }))
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->orderBy('name')
            ->paginate(20);

        return view('livewire.portal.clinic-manager', ['clinics' => $clinics]);
    }
}
