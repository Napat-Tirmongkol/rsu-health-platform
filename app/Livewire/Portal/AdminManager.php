<?php

namespace App\Livewire\Portal;

use App\Models\Admin;
use App\Models\Clinic;
use Livewire\Component;
use Livewire\WithPagination;

class AdminManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterClinic = '';

    public bool $showModal = false;
    public bool $showDeleteModal = false;

    public ?int $editingId = null;
    public ?int $deletingId = null;

    public string $name = '';
    public string $email = '';
    public string $googleId = '';
    public int $clinicId = 1;
    public bool $fullPlatformAccess = true;
    public array $selectedModules = [];
    public array $selectedActions = [];
    public string $defaultWorkspace = 'campaign';

    protected $queryString = [
        'search'        => ['except' => ''],
        'filterClinic'  => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterClinic(): void
    {
        $this->resetPage();
    }

    protected function rules(): array
    {
        return [
            'name'             => 'required|string|min:3',
            'email'            => 'required|email|unique:sys_admins,email,' . $this->editingId,
            'googleId'         => 'nullable|string|unique:sys_admins,google_id,' . $this->editingId,
            'clinicId'         => 'required|integer|exists:sys_clinics,id',
            'selectedModules'  => $this->fullPlatformAccess ? 'nullable|array' : 'required|array|min:1',
            'selectedActions'  => $this->fullPlatformAccess ? 'nullable|array' : 'required|array|min:1',
            'defaultWorkspace' => 'required|in:' . implode(',', array_keys(config('admin_modules.modules', []))),
        ];
    }

    public function updatedFullPlatformAccess(bool $value): void
    {
        if ($value) {
            $this->selectedModules = array_keys(config('admin_modules.modules', []));
            $this->selectedActions = $this->allActionKeys();
        }
    }

    public function updatedSelectedModules(): void
    {
        if ($this->fullPlatformAccess) {
            return;
        }

        $allowed = $this->actionKeysForModules($this->selectedModules);
        $this->selectedActions = array_values(array_intersect($this->selectedActions, $allowed));

        if (! in_array($this->defaultWorkspace, $this->selectedModules, true)) {
            $this->defaultWorkspace = $this->selectedModules[0] ?? 'campaign';
        }
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEditModal(int $adminId): void
    {
        $admin = Admin::withoutGlobalScopes()->findOrFail($adminId);

        $this->editingId = $admin->id;
        $this->name      = $admin->name;
        $this->email     = $admin->email;
        $this->googleId  = $admin->google_id ?? '';
        $this->clinicId  = $admin->clinic_id ?? 1;

        $perms = $admin->module_permissions ?? [];
        $this->fullPlatformAccess = $perms === [] || in_array('*', $perms, true);
        $this->selectedModules = $this->fullPlatformAccess
            ? array_keys(config('admin_modules.modules', []))
            : array_values($perms);

        $actionPerms = $admin->action_permissions ?? [];
        $this->selectedActions = $this->fullPlatformAccess || $actionPerms === []
            ? $this->actionKeysForModules($this->selectedModules)
            : array_values($actionPerms);

        $this->defaultWorkspace = $admin->default_workspace ?: 'campaign';
        $this->showModal = true;
    }

    public function confirmDelete(int $adminId): void
    {
        $this->deletingId    = $adminId;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        Admin::withoutGlobalScopes()->findOrFail($this->deletingId)->delete();
        $this->showDeleteModal = false;
        $this->deletingId = null;
        session()->flash('portal_admin_message', 'ลบ Admin เรียบร้อยแล้ว');
    }

    public function save(): void
    {
        $this->validate();

        $availableModules = array_keys(config('admin_modules.modules', []));
        $modulePerms = $this->fullPlatformAccess
            ? ['*']
            : array_values(array_intersect($this->selectedModules, $availableModules));

        if (! $this->fullPlatformAccess && $modulePerms === []) {
            $this->addError('selectedModules', 'กรุณาเลือกอย่างน้อย 1 workspace');
            return;
        }

        if (! $this->fullPlatformAccess && ! in_array($this->defaultWorkspace, $modulePerms, true)) {
            $this->addError('defaultWorkspace', 'Default workspace ต้องอยู่ในรายการสิทธิ์ที่เลือก');
            return;
        }

        $effectiveModules = $modulePerms === ['*'] ? $availableModules : $modulePerms;
        $allowedActions   = $this->actionKeysForModules($effectiveModules);
        $actionPerms = $this->fullPlatformAccess
            ? ['*']
            : array_values(array_intersect($this->selectedActions, $allowedActions));

        if (! $this->fullPlatformAccess && $actionPerms === []) {
            $this->addError('selectedActions', 'กรุณาเลือกอย่างน้อย 1 สิทธิ์การทำงาน');
            return;
        }

        $payload = [
            'clinic_id'          => $this->clinicId,
            'name'               => $this->name,
            'email'              => $this->email,
            'google_id'          => $this->googleId ?: null,
            'module_permissions' => $modulePerms,
            'action_permissions' => $actionPerms,
            'default_workspace'  => $this->defaultWorkspace,
        ];

        if ($this->editingId) {
            Admin::withoutGlobalScopes()->findOrFail($this->editingId)->update($payload);
            session()->flash('portal_admin_message', 'อัปเดตข้อมูล Admin เรียบร้อยแล้ว');
        } else {
            Admin::create($payload);
            session()->flash('portal_admin_message', 'เพิ่ม Admin เรียบร้อยแล้ว');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function moduleSummary(Admin $admin): string
    {
        $perms = $admin->module_permissions ?? [];
        if ($perms === [] || in_array('*', $perms, true)) {
            return 'Full platform access';
        }
        $labels = collect(config('admin_modules.modules', []))->only($perms)->pluck('label')->all();
        return $labels === [] ? 'ไม่มี workspace' : implode(' · ', $labels);
    }

    public function render()
    {
        $admins = Admin::withoutGlobalScopes()
            ->with('clinic')
            ->when($this->search, fn ($q) =>
                $q->where(fn ($q2) =>
                    $q2->where('name', 'like', '%' . $this->search . '%')
                       ->orWhere('email', 'like', '%' . $this->search . '%')
                )
            )
            ->when($this->filterClinic !== '', fn ($q) =>
                $q->where('clinic_id', $this->filterClinic)
            )
            ->latest()
            ->paginate(20);

        $clinics = Clinic::orderBy('name')->get(['id', 'name']);

        return view('livewire.portal.admin-manager', [
            'admins'           => $admins,
            'clinics'          => $clinics,
            'availableModules' => config('admin_modules.modules', []),
            'availableActions' => config('admin_modules.actions', []),
        ]);
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'email', 'googleId']);
        $this->clinicId           = 1;
        $this->fullPlatformAccess = true;
        $this->selectedModules    = array_keys(config('admin_modules.modules', []));
        $this->selectedActions    = $this->allActionKeys();
        $this->defaultWorkspace   = 'campaign';
        $this->resetErrorBag();
    }

    private function actionKeysForModules(array $modules): array
    {
        return collect(config('admin_modules.actions', []))
            ->filter(fn ($group, $key) => in_array($key, $modules, true))
            ->flatMap(fn ($group) => collect($group['actions'])->pluck('key'))
            ->values()
            ->all();
    }

    private function allActionKeys(): array
    {
        return collect(config('admin_modules.actions', []))
            ->flatMap(fn ($group) => collect($group['actions'])->pluck('key'))
            ->values()
            ->all();
    }
}
