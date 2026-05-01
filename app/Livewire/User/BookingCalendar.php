<?php

namespace App\Livewire\User;

use App\Models\Campaign;
use App\Models\Slot;
use Livewire\Component;

class BookingCalendar extends Component
{
    public $campaigns;

    public $selectedCampaign = null;

    public $availableDates = [];

    public $selectedDate = null;

    public function mount()
    {
        $this->campaigns = Campaign::where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->get();

        if ($this->campaigns->count() === 1) {
            $this->selectCampaign($this->campaigns->first()->id);
        }
    }

    public function selectCampaign($campaignId)
    {
        $this->selectedCampaign = Campaign::findOrFail($campaignId);
        $this->selectedDate = null;
        $this->availableDates = $this->loadAvailableDates($campaignId);
    }

    public function selectDate($date)
    {
        $this->selectedDate = $date;

        $this->dispatch('dateSelected', date: $date, campaignId: $this->selectedCampaign->id);
    }

    public function render()
    {
        return view('livewire.user.booking-calendar');
    }

    protected function loadAvailableDates(int $campaignId): array
    {
        return Slot::where('camp_id', $campaignId)
            ->whereDate('date', '>=', now()->format('Y-m-d'))
            ->where('status', 'available')
            ->select('date')
            ->distinct()
            ->orderBy('date')
            ->get()
            ->pluck('date')
            ->map(fn ($date) => $date->format('Y-m-d'))
            ->toArray();
    }
}
