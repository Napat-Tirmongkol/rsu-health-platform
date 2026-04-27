<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Models\Booking;
use Livewire\Component;
use Livewire\WithPagination;

class UserManager extends Component
{
    use WithPagination;

    public $search = '';
    public $showHistoryModal = false;
    public $selectedUser = null;
    public $userBookings = [];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function viewHistory($userId)
    {
        $this->selectedUser = User::findOrFail($userId);
        $this->userBookings = Booking::where('user_id', $userId)
            ->with(['campaign', 'slot'])
            ->latest()
            ->get();
        $this->showHistoryModal = true;
    }

    public function render()
    {
        $users = User::where(function($q) {
                $q->where('full_name', 'like', '%' . $this->search . '%')
                  ->orWhere('student_personnel_id', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('phone_number', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(20);

        return view('livewire.admin.user-manager', [
            'users' => $users
        ]);
    }
}
