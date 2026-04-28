<?php

namespace App\Livewire\User;

use App\Models\Announcement;
use App\Models\UserAnnouncementRead;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationBell extends Component
{
    public $isOpen = false;

    public function toggle()
    {
        $this->isOpen = ! $this->isOpen;

        if ($this->isOpen) {
            $this->markVisibleAnnouncementsAsRead();
        }
    }

    public function close()
    {
        $this->isOpen = false;
    }

    public function markAllAsRead()
    {
        $this->markVisibleAnnouncementsAsRead();
    }

    public function render()
    {
        $user = Auth::guard('user')->user();

        $announcements = Announcement::query()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->with(['reads' => fn ($query) => $query->where('user_id', $user->id)])
            ->latest()
            ->take(10)
            ->get();

        $unreadCount = $announcements->filter(fn ($announcement) => $announcement->reads->isEmpty())->count();

        return view('livewire.user.notification-bell', [
            'announcements' => $announcements,
            'unreadCount' => $unreadCount,
        ]);
    }

    private function markVisibleAnnouncementsAsRead(): void
    {
        $user = Auth::guard('user')->user();

        $announcementIds = Announcement::query()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->latest()
            ->take(10)
            ->pluck('id');

        if ($announcementIds->isEmpty()) {
            return;
        }

        $existing = UserAnnouncementRead::query()
            ->where('user_id', $user->id)
            ->whereIn('announcement_id', $announcementIds)
            ->pluck('announcement_id')
            ->all();

        $rows = $announcementIds
            ->reject(fn ($id) => in_array($id, $existing, true))
            ->map(fn ($id) => [
                'user_id' => $user->id,
                'announcement_id' => $id,
                'read_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->values()
            ->all();

        if ($rows !== []) {
            UserAnnouncementRead::insert($rows);
        }
    }
}
