<?php

namespace App\Livewire\User;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MyBookings extends Component
{
    public $tab = 'upcoming';

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

        if ($this->selectedBooking && $this->selectedBooking->id === $booking->id) {
            $this->selectedBooking = $booking->fresh(['campaign', 'slot']);
        }

        $this->dispatch('swal:success', message: 'ยกเลิกนัดหมายเรียบร้อยแล้ว');
    }

    public function showDetails($bookingId)
    {
        $this->selectedBooking = Booking::where('user_id', Auth::guard('user')->id())
            ->with(['campaign', 'slot'])
            ->findOrFail($bookingId);

        $this->dispatch('open-modal');
    }

    public function closeDetails()
    {
        $this->dispatch('close-modal');
    }

    public function render()
    {
        $today = Carbon::today()->format('Y-m-d');

        $allBookings = Booking::where('user_id', Auth::guard('user')->id())
            ->with(['campaign', 'slot'])
            ->get();

        $stats = [
            'upcoming' => $allBookings->filter(function ($booking) use ($today) {
                return in_array($booking->status, ['pending', 'confirmed']) && $booking->slot && $booking->slot->date >= $today;
            })->count(),
            'history' => $allBookings->filter(function ($booking) use ($today) {
                return $booking->status === 'completed'
                    || $booking->status === 'cancelled'
                    || ($booking->slot && $booking->slot->date < $today);
            })->count(),
            'checkin' => $allBookings->where('status', 'completed')->count(),
        ];

        if ($this->tab === 'upcoming') {
            $bookings = $allBookings->filter(function ($booking) use ($today) {
                return in_array($booking->status, ['pending', 'confirmed']) && $booking->slot && $booking->slot->date >= $today;
            })->sortBy(function ($booking) {
                return $booking->slot->date.' '.$booking->slot->start_time;
            });
        } else {
            $bookings = $allBookings->filter(function ($booking) use ($today) {
                return $booking->status === 'completed'
                    || $booking->status === 'cancelled'
                    || ($booking->slot && $booking->slot->date < $today);
            })->sortByDesc(function ($booking) {
                return optional($booking->slot)->date ?? $booking->created_at;
            });
        }

        return view('livewire.user.my-bookings', [
            'bookings' => $bookings,
            'stats' => $stats,
        ]);
    }
}
