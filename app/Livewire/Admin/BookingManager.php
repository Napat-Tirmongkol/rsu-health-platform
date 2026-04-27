<?php

namespace App\Livewire\Admin;

use App\Models\Booking;
use Livewire\Component;
use Livewire\WithPagination;

class BookingManager extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = 'all';
    public $selectedBookings = [];
    public $showDrawer = false;
    public $selectedBookingDetails = null;

    protected $queryString = ['statusFilter', 'search'];

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function openDetails($id)
    {
        $this->selectedBookingDetails = Booking::with(['user', 'campaign', 'slot'])->findOrFail($id);
        $this->showDrawer = true;
    }

    public function closeDrawer()
    {
        $this->showDrawer = false;
        $this->selectedBookingDetails = null;
    }

    public function approve($id)
    {
        $booking = Booking::findOrFail($id);
        $booking->update(['status' => 'confirmed']);
        session()->flash('message', 'อนุมัติการจองเรียบร้อยแล้ว');
        $this->closeDrawer();
    }

    public function cancel($id)
    {
        $booking = Booking::findOrFail($id);
        $booking->update(['status' => 'cancelled']);
        session()->flash('message', 'ยกเลิกการจองเรียบร้อยแล้ว');
        $this->closeDrawer();
    }

    public function bulkApprove()
    {
        Booking::whereIn('id', $this->selectedBookings)->update(['status' => 'confirmed']);
        $this->selectedBookings = [];
        session()->flash('message', 'อนุมัติรายการที่เลือกเรียบร้อยแล้ว');
    }

    public function bulkCancel()
    {
        Booking::whereIn('id', $this->selectedBookings)->update(['status' => 'cancelled']);
        $this->selectedBookings = [];
        session()->flash('message', 'ยกเลิกรายการที่เลือกเรียบร้อยแล้ว');
    }

    public function render()
    {
        $query = Booking::with(['user', 'campaign', 'slot'])
            ->when($this->statusFilter !== 'all', function($q) {
                return $q->where('status', $this->statusFilter);
            })
            ->when($this->search, function($q) {
                return $q->whereHas('user', function($uq) {
                    $uq->where('full_name', 'like', '%' . $this->search . '%')
                       ->orWhere('student_personnel_id', 'like', '%' . $this->search . '%');
                })->orWhereHas('campaign', function($cq) {
                    $cq->where('title', 'like', '%' . $this->search . '%');
                });
            });

        $stats = [
            'pending' => Booking::where('status', 'pending')->count(),
            'confirmed' => Booking::where('status', 'confirmed')->count(),
            'cancelled' => Booking::where('status', 'cancelled')->count(),
            'today' => Booking::whereHas('slot', fn($s) => $s->whereDate('date', now()))->count(),
        ];

        return view('livewire.admin.booking-manager', [
            'bookings' => $query->latest()->paginate(20),
            'stats' => $stats
        ]);
    }
}
