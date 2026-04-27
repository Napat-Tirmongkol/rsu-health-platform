<?php

namespace App\Livewire\User;

use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Carbon\Carbon;

class MyBookings extends Component
{
    public $tab = 'upcoming'; // 'upcoming' or 'history'
    public $selectedBooking = null;

    protected $listeners = ['refreshBookings' => '$refresh'];

    public function switchTab($tab)
    {
        $this->tab = $tab;
    }

    public function cancelBooking($bookingId)
    {
        $booking = Booking::where('user_id', Auth::guard('user')->id())
            ->findOrFail($bookingId);

        if ($booking->status === 'completed') {
            $this->dispatch('swal:error', message: 'ไม่สามารถยกเลิกนัดหมายที่เสร็จสิ้นแล้วได้');
            return;
        }

        $booking->update(['status' => 'cancelled']);
        $this->dispatch('swal:success', message: 'ยกเลิกนัดหมายเรียบร้อยแล้ว');
    }

    public function showDetails($bookingId)
    {
        $this->selectedBooking = Booking::with(['campaign', 'slot'])->find($bookingId);
        $this->dispatch('openModal');
    }

    public function render()
    {
        $today = Carbon::today()->format('Y-m-d');
        
        $allBookings = Booking::where('user_id', Auth::guard('user')->id())
            ->with(['campaign', 'slot'])
            ->get();

        // คำนวณสถิติ
        $stats = [
            'upcoming' => $allBookings->filter(function($b) use ($today) {
                return in_array($b->status, ['pending', 'confirmed']) && $b->slot && $b->slot->date >= $today;
            })->count(),
            'history' => $allBookings->filter(function($b) use ($today) {
                return $b->status === 'completed' || $b->status === 'cancelled' || ($b->slot && $b->slot->date < $today);
            })->count(),
            'checkin' => $allBookings->where('status', 'completed')->count(),
        ];

        // กรองตาม Tab
        if ($this->tab === 'upcoming') {
            $bookings = $allBookings->filter(function($b) use ($today) {
                return in_array($b->status, ['pending', 'confirmed']) && $b->slot && $b->slot->date >= $today;
            })->sortBy(function($b) {
                return $b->slot->date . ' ' . $b->slot->start_time;
            });
        } else {
            $bookings = $allBookings->filter(function($b) use ($today) {
                return $b->status === 'completed' || $b->status === 'cancelled' || ($b->slot && $b->slot->date < $today);
            })->sortByDesc('created_at');
        }

        return view('livewire.user.my-bookings', [
            'bookings' => $bookings,
            'stats' => $stats
        ]);
    }
}
