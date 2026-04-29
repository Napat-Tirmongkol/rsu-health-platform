<?php

namespace App\Livewire\Admin;

use App\Models\Admin;
use Livewire\Component;
use Livewire\WithPagination;

class SystemAdminManager extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $editingId = null;
    public $name = '';
    public $email = '';
    public $google_id = '';
    public $fullPlatformAccess = true;
    public $selectedModules = [];
    public $selectedActions = [];
    public $defaultWorkspace = 'campaign';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|min:3',
            'email' => 'required|email|unique:sys_admins,email,'.$this->editingId,
            'google_id' => 'nullable|string|unique:sys_admins,google_id,'.$this->editingId,
            'selectedModules' => $this->fullPlatformAccess ? 'nullable|array' : 'required|array|min:1',
            'selectedActions' => $this->fullPlatformAccess ? 'nullable|array' : 'required|array|min:1',
            'defaultWorkspace' => 'required|in:'.implode(',', array_keys(config('admin_modules.modules', []))),
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

        $allowedActions = $this->availableActionKeysForSelectedModules();
        $this->selectedActions = array_values(array_intersect($this->selectedActions, $allowedActions));

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
        $admin = Admin::findOrFail($adminId);

        $this->editingId = $admin->id;
        $this->name = $admin->name;
        $this->email = $admin->email;
        $this->google_id = $admin->google_id ?? '';

        $permissions = $admin->module_permissions ?? [];
        $this->fullPlatformAccess = $permissions === [] || in_array('*', $permissions, true);
        $this->selectedModules = $this->fullPlatformAccess
            ? array_keys(config('admin_modules.modules', []))
            : array_values($permissions);

        $actionPermissions = $admin->action_permissions ?? [];
        $this->selectedActions = $this->fullPlatformAccess || $actionPermissions === []
            ? $this->actionsForModules($this->selectedModules)
            : array_values($actionPermissions);

        $this->defaultWorkspace = $admin->default_workspace ?: 'campaign';
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $availableModules = array_keys(config('admin_modules.modules', []));
        $permissions = $this->fullPlatformAccess
            ? ['*']
            : array_values(array_intersect($this->selectedModules, $availableModules));

        if (! $this->fullPlatformAccess && $permissions === []) {
            $this->addError('selectedModules', 'กรุณาเลือกอย่างน้อย 1 workspace');

            return;
        }

        if (! $this->fullPlatformAccess && ! in_array($this->defaultWorkspace, $permissions, true)) {
            $this->addError('defaultWorkspace', 'Default workspace ต้องอยู่ในรายการสิทธิ์ที่เลือก');

            return;
        }

        $allowedActions = $this->actionsForModules($permissions === ['*'] ? $availableModules : $permissions);
        $actionPermissions = $this->fullPlatformAccess
            ? ['*']
            : array_values(array_intersect($this->selectedActions, $allowedActions));

        if (! $this->fullPlatformAccess && $actionPermissions === []) {
            $this->addError('selectedActions', 'กรุณาเลือกอย่างน้อย 1 สิทธิ์การทำงาน');

            return;
        }

        $payload = [
            'clinic_id' => auth('admin')->user()->clinic_id ?? 1,
            'name' => $this->name,
            'email' => $this->email,
            'google_id' => $this->google_id ?: null,
            'module_permissions' => $permissions,
            'action_permissions' => $actionPermissions,
            'default_workspace' => $this->defaultWorkspace,
        ];

        if ($this->editingId) {
            Admin::findOrFail($this->editingId)->update($payload);
            session()->flash('system_admin_message', 'อัปเดตข้อมูล System Admin เรียบร้อยแล้ว');
        } else {
            Admin::create($payload);
            session()->flash('system_admin_message', 'เพิ่ม System Admin เรียบร้อยแล้ว');
        }

        $this->showModal = false;
        $this->resetForm();
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

    public function actionSummary(Admin $admin): string
    {
        $permissions = $admin->action_permissions ?? [];

        if ($admin->hasFullPlatformAccess() || $permissions === [] || in_array('*', $permissions, true)) {
            return 'All actions';
        }

        $labels = collect($this->availableActions())
            ->mapWithKeys(fn (array $group) => collect($group['actions'])->mapWithKeys(fn (array $action) => [$action['key'] => $action['label']]))
            ->only($permissions)
            ->values()
            ->all();

        return $labels === [] ? 'No action assigned' : implode(' · ', $labels);
    }

    public function workspaceLabel(?string $workspace): string
    {
        return config("admin_modules.modules.{$workspace}.label", 'Platform Home');
    }

    public function availableActions(): array
    {
        return config('admin_modules.actions', []);
    }

    protected function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'email', 'google_id']);
        $this->fullPlatformAccess = true;
        $this->selectedModules = array_keys(config('admin_modules.modules', []));
        $this->selectedActions = $this->allActionKeys();
        $this->defaultWorkspace = 'campaign';
        $this->resetErrorBag();
    }

    public function render()
    {
        $admins = Admin::query()
            ->where(function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            })
            ->latest()
            ->paginate(20);

        return view('livewire.admin.system-admin-manager', [
            'admins' => $admins,
            'availableModules' => config('admin_modules.modules', []),
            'availableActions' => $this->availableActions(),
        ]);
    }

    private function availableActionKeysForSelectedModules(): array
    {
        return $this->actionsForModules($this->selectedModules);
    }

    private function actionsForModules(array $modules): array
    {
        return collect($this->availableActions())
            ->filter(fn (array $group, string $moduleKey) => in_array($moduleKey, $modules, true))
            ->flatMap(fn (array $group) => collect($group['actions'])->pluck('key'))
            ->values()
            ->all();
    }

    private function allActionKeys(): array
    {
        return collect($this->availableActions())
            ->flatMap(fn (array $group) => collect($group['actions'])->pluck('key'))
            ->values()
            ->all();
    }
}
