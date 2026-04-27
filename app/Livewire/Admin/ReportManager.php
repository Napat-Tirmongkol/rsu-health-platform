<?php

namespace App\Livewire\Admin;

use App\Models\Campaign;
use App\Models\Booking;
use App\Exports\CampaignReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\Component;
use Livewire\WithPagination;

class ReportManager extends Component
{
    use WithPagination;

    public $selectedCampaignId;
    public $stats = [];

    public function mount()
    {
        $latestCamp = Campaign::latest()->first();
        if ($latestCamp) {
            $this->selectedCampaignId = $latestCamp->id;
        }
        $this->updateStats();
    }

    public function updatedSelectedCampaignId()
    {
        $this->updateStats();
        $this->resetPage();
    }

    public function updateStats()
    {
        if (!$this->selectedCampaignId) {
            $this->stats = [
                'total' => 0,
                'attended' => 0,
                'absent' => 0,
                'pending' => 0,
            ];
            return;
        }

        $this->stats = [
            'total' => Booking::where('camp_id', $this->selectedCampaignId)->count(),
            'attended' => Booking::where('camp_id', $this->selectedCampaignId)->where('status', 'attended')->count(),
            'absent' => Booking::where('camp_id', $this->selectedCampaignId)->where('status', 'absent')->count(),
            'pending' => Booking::where('camp_id', $this->selectedCampaignId)->whereIn('status', ['pending', 'confirmed'])->count(),
        ];
    }

    public function export()
    {
        if (!$this->selectedCampaignId) return;

        $campaign = Campaign::find($this->selectedCampaignId);
        $filename = 'Report_' . str_replace(' ', '_', $campaign->title) . '_' . now()->format('YmdHis') . '.xlsx';

        return Excel::download(new CampaignReportExport($this->selectedCampaignId), $filename);
    }

    public function render()
    {
        $campaigns = Campaign::latest()->get();
        
        $bookings = [];
        if ($this->selectedCampaignId) {
            $bookings = Booking::where('camp_id', $this->selectedCampaignId)
                ->with(['user', 'slot'])
                ->latest()
                ->paginate(20);
        }

        return view('livewire.admin.report-manager', [
            'campaigns' => $campaigns,
            'bookings' => $bookings
        ]);
    }
}
