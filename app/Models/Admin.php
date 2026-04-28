<?php

namespace App\Models;

use App\Models\Traits\BelongsToClinic;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable, BelongsToClinic;

    protected $table = 'sys_admins';

    protected $fillable = [
        'clinic_id',
        'name',
        'email',
        'google_id',
        'profile_photo_path',
        'module_permissions',
        'default_workspace',
    ];

    protected $casts = [
        'module_permissions' => 'array',
    ];

    /**
     * Get the clinic that owns the admin.
     */
    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function hasModuleAccess(string $module): bool
    {
        if (! Schema::hasTable($this->getTable()) || ! Schema::hasColumn($this->getTable(), 'module_permissions')) {
            return true;
        }

        $permissions = $this->module_permissions;

        if (! is_array($permissions) || $permissions === []) {
            return true;
        }

        return in_array('*', $permissions, true) || in_array($module, $permissions, true);
    }

    public function hasFullPlatformAccess(): bool
    {
        if (! Schema::hasTable($this->getTable()) || ! Schema::hasColumn($this->getTable(), 'module_permissions')) {
            return true;
        }

        $permissions = $this->module_permissions;

        return ! is_array($permissions) || $permissions === [] || in_array('*', $permissions, true);
    }

    public function assignModulePermissions(array $modules): void
    {
        $this->module_permissions = array_values(array_unique($modules));
    }

    public function preferredWorkspace(): ?string
    {
        $workspace = $this->default_workspace;

        if ($workspace && $this->hasModuleAccess($workspace)) {
            return $workspace;
        }

        foreach (array_keys(config('admin_modules.modules', [])) as $module) {
            if ($this->hasModuleAccess($module)) {
                return $module;
            }
        }

        return null;
    }

    public function landingRouteName(): string
    {
        return match ($this->preferredWorkspace()) {
            'campaign' => 'admin.workspace.campaign',
            'borrow' => 'admin.workspace.borrow',
            default => 'admin.dashboard',
        };
    }
}
