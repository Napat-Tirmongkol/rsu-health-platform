<?php

namespace App\Livewire\Admin;

use App\Models\Admin;
use Livewire\Component;
use Livewire\WithPagination;

class AdminAccessManager extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $editingId = null;
    public $fullPlatformAccess = true;
    public $selectedModules = [];

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage('adminsPage');
    }

    public function openAccessModal(int $adminId): void
    {
        $admin = Admin::findOrFail($adminId);

        $this->editingId = $admin->id;
        $permissions = $admin->module_permissions ?? [];
        $this->fullPlatformAccess = $permissions === [] || in_array('*', $permissions, true);
        $this->selectedModules = $this->fullPlatformAccess
            ? array_keys(config('admin_modules.modules', []))
            : array_values($permissions);

        $this->showModal = true;
    }

    public function saveAccess(): void
    {
        $this->validate([
            'selectedModules' => $this->fullPlatformAccess ? 'nullable|array' : 'required|array|min:1',
        ], [
            'selectedModules.required' => 'กรุณาเลือกอย่างน้อย 1 workspace',
            'selectedModules.min' => 'กรุณาเลือกอย่างน้อย 1 workspace',
        ]);

        $admin = Admin::findOrFail($this->editingId);

        $permissions = $this->fullPlatformAccess
            ? ['*']
            : array_values(array_intersect(
                $this->selectedModules,
                array_keys(config('admin_modules.modules', []))
            ));

        if ($permissions === []) {
            $this->addError('selectedModules', 'กรุณาเลือกอย่างน้อย 1 workspace');
            return;
        }

        $admin->update([
            'module_permissions' => $permissions,
        ]);

        $this->showModal = false;
        session()->flash('admin_access_message', 'บันทึกสิทธิ์การเข้าถึงโมดูลเรียบร้อยแล้ว');
    }

    public function moduleSummary(Admin $admin): string
    {
        $permissions = $admin->module_permissions ?? [];

        if ($permissions === [] || in_array('*', $permissions, true)) {
            return 'Full platform access';
        }

        $labels = collect(config('admin_modules.modules', []))
            ->only($permissions)
            ->pluck('label')
            ->values()
            ->all();

        return $labels === [] ? 'No workspace assigned' : implode(' · ', $labels);
    }

    public function render()
    {
        $admins = Admin::query()
            ->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(20, ['*'], 'adminsPage');

        return view('livewire.admin.admin-access-manager', [
            'admins' => $admins,
            'availableModules' => config('admin_modules.modules', []),
        ]);
    }
}
