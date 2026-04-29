<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\Booking;
use App\Services\CampaignNotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendCampaignBookingReminders extends Command
{
    protected $signature = 'campaign:send-reminders {--date=}';

    protected $description = 'Send booking reminder notifications for confirmed campaign bookings';

    public function handle(CampaignNotificationService $notifications): int
    {
        $targetDate = $this->option('date')
            ? Carbon::parse($this->option('date'))->toDateString()
            : now()->addDay()->toDateString();

        $bookings = Booking::with(['user', 'campaign', 'slot'])
            ->where('status', 'confirmed')
            ->whereHas('slot', fn ($slot) => $slot->whereDate('date', $targetDate))
            ->get();

        $sentCount = 0;

        foreach ($bookings as $booking) {
            $alreadySent = ActivityLog::withoutGlobalScopes()
                ->where('clinic_id', $booking->clinic_id)
                ->where('action', 'campaign.booking_reminder_notification_sent')
                ->where('properties->booking_id', $booking->id)
                ->whereDate('created_at', now()->toDateString())
                ->exists();

            if ($alreadySent) {
                continue;
            }

            $notifications->bookingReminder($booking);
            $sentCount++;
        }

        $this->info("Processed {$bookings->count()} bookings, attempted reminders for {$sentCount} bookings.");

        return self::SUCCESS;
    }
}
