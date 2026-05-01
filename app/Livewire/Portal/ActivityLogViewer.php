<?php

namespace App\Livewire\Portal;

use App\Models\ActivityLog;
use App\Models\Clinic;
use App\Scopes\TenantScope;
use Livewire\Component;
use Livewire\WithPagination;

class ActivityLogViewer extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterClinic = '';
    public string $filterAction = '';
    public string $dateFrom = '';
    public string $dateTo = '';

    public function updatingSearch(): void    { $this->resetPage(); }
    public function updatingFilterClinic(): void { $this->resetPage(); }
    public function updatingFilterAction(): void { $this->resetPage(); }
    public function updatingDateFrom(): void  { $this->resetPage(); }
    public function updatingDateTo(): void    { $this->resetPage(); }

    public function render()
    {
        $logs = ActivityLog::withoutGlobalScope(TenantScope::class)
            ->with('clinic')
            ->when($this->filterClinic, fn ($q) => $q->where('clinic_id', $this->filterClinic))
            ->when($this->filterAction, fn ($q) => $q->where('action', $this->filterAction))
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('action', 'like', "%{$this->search}%")
                  ->orWhere('description', 'like', "%{$this->search}%")
                  ->orWhere('ip_address', 'like', "%{$this->search}%");
            }))
            ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->latest()
            ->paginate(20);

        $clinics = Clinic::orderBy('name')->get(['id', 'name']);

        $actions = ActivityLog::withoutGlobalScope(TenantScope::class)
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return view('livewire.portal.activity-log-viewer', [
            'logs'    => $logs,
            'clinics' => $clinics,
            'actions' => $actions,
        ]);
    }
}
