<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use Livewire\Component;
use Livewire\WithPagination;

class ActivityLogViewer extends Component
{
    use WithPagination;

    public $search = '';
    public $filterAction = 'all';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = ActivityLog::query()
            ->with(['actor', 'clinic'])
            ->where(function($q) {
                $q->where('action', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
                  ->orWhere('ip_address', 'like', '%' . $this->search . '%');
            });

        if ($this->filterAction !== 'all') {
            $query->where('action', $this->filterAction);
        }

        $logs = $query->latest()->paginate(20);

        // Fetch unique actions for filter dropdown
        $actions = ActivityLog::distinct()->pluck('action');

        return view('livewire.admin.activity-log-viewer', [
            'logs' => $logs,
            'actions' => $actions
        ]);
    }
}
