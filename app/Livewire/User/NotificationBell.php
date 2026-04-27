<?php

namespace App\Livewire\User;

use App\Models\Announcement;
use Livewire\Component;

class NotificationBell extends Component
{
    public $isOpen = false;

    public function toggle()
    {
        $this->isOpen = ! $this->isOpen;
    }

    public function close()
    {
        $this->isOpen = false;
    }

    public function render()
    {
        $announcements = Announcement::where('is_active', true)
            ->latest()
            ->take(10)
            ->get();

        return view('livewire.user.notification-bell', [
            'announcements' => $announcements,
            'unreadCount' => $announcements->count(),
        ]);
    }
}
