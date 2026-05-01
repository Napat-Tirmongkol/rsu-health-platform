<?php

namespace App\Livewire\User;

use App\Models\Booking;
use App\Models\Slot;
use App\Services\CampaignNotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class TimeSlotPicker extends Component
{
    public $campaignId;

    public $date;

    public $slots = [];

    public $selectedSlotId = null;

    #[On('dateSelected')]
    public function updateSlots($campaignId, $date)
    {
        $this->campaignId = $campaignId;
        $this->date = $date;
        $this->selectedSlotId = null;

        $this->slots = Slot::where('camp_id', $campaignId)
            ->whereDate('date', $date)
            ->where('status', 'available')
            ->orderBy('start_time')
            ->get();
    }

    public function selectSlot($slotId)
    {
        $this->selectedSlotId = $slotId;
    }

    public function confirmBooking()
    {
        $this->validate([
            'selectedSlotId' => 'required|exists:camp_slots,id',
        ]);

        $booking = DB::transaction(function () {
            $slot = Slot::whereKey($this->selectedSlotId)
                ->where('camp_id', $this->campaignId)
                ->whereDate('date', $this->date)
                ->lockForUpdate()
                ->firstOrFail();

            if ($slot->isFull()) {
                return null;
            }

            return Booking::create([
                'clinic_id' => currentClinicId(),
                'user_id' => Auth::guard('user')->id(),
                'camp_id' => $this->campaignId,
                'slot_id' => $this->selectedSlotId,
                'status' => 'pending',
            ]);
        });

        if (! $booking) {
            session()->flash('error', 'ช่วงเวลานี้เต็มแล้ว กรุณาเลือกช่วงเวลาอื่น');

            return;
        }

        try {
            $booking->load(['user', 'campaign', 'slot']);
            app(CampaignNotificationService::class)->bookingSubmitted($booking);
        } catch (\Throwable) {
        }

        session()->flash('message', 'จองคิวนัดหมายเรียบร้อยแล้ว');

        return redirect()->route('user.hub');
    }

    public function render()
    {
        return view('livewire.user.time-slot-picker');
    }
}
