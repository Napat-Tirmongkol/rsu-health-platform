<?php

namespace Tests\Feature;

use App\Livewire\User\NotificationBell;
use App\Models\Announcement;
use App\Models\Clinic;
use App\Models\User;
use App\Models\UserAnnouncementRead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class UserNotificationReadStateTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_bell_marks_visible_announcements_as_read(): void
    {
        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $user = User::create([
            'clinic_id' => $clinic->id,
            'name' => 'LINE User',
            'full_name' => 'LINE User',
            'email' => 'notif-user@example.com',
            'line_user_id' => 'line-notif-user',
            'password' => Hash::make('password'),
        ]);

        $first = Announcement::create([
            'clinic_id' => $clinic->id,
            'title' => 'Announcement One',
            'content' => 'First announcement',
            'is_active' => true,
        ]);

        $second = Announcement::create([
            'clinic_id' => $clinic->id,
            'title' => 'Announcement Two',
            'content' => 'Second announcement',
            'is_active' => true,
        ]);

        $this->actingAs($user, 'user');

        Livewire::test(NotificationBell::class)
            ->assertSee('2')
            ->call('toggle')
            ->assertSee('0');

        $this->assertDatabaseHas('user_announcement_reads', [
            'user_id' => $user->id,
            'announcement_id' => $first->id,
        ]);

        $this->assertDatabaseHas('user_announcement_reads', [
            'user_id' => $user->id,
            'announcement_id' => $second->id,
        ]);

        $this->assertSame(2, UserAnnouncementRead::where('user_id', $user->id)->count());
    }

    public function test_notification_bell_only_counts_unread_for_current_user(): void
    {
        $clinic = Clinic::create([
            'name' => 'RSU Medical Clinic',
            'slug' => 'medical',
            'code' => 'RSU-MED',
            'status' => 'active',
        ]);

        $user = User::create([
            'clinic_id' => $clinic->id,
            'name' => 'LINE User',
            'full_name' => 'LINE User',
            'email' => 'notif-user-2@example.com',
            'line_user_id' => 'line-notif-user-2',
            'password' => Hash::make('password'),
        ]);

        $otherUser = User::create([
            'clinic_id' => $clinic->id,
            'name' => 'Other User',
            'full_name' => 'Other User',
            'email' => 'other-user@example.com',
            'line_user_id' => 'line-other-user',
            'password' => Hash::make('password'),
        ]);

        $announcement = Announcement::create([
            'clinic_id' => $clinic->id,
            'title' => 'Target Announcement',
            'content' => 'Visible content',
            'is_active' => true,
        ]);

        UserAnnouncementRead::create([
            'user_id' => $otherUser->id,
            'announcement_id' => $announcement->id,
            'read_at' => now(),
        ]);

        $this->actingAs($user, 'user');

        Livewire::test(NotificationBell::class)
            ->assertSee('1');
    }
}
