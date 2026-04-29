<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;

class CampaignNotificationService
{
    public function __construct(
        private readonly IntegrationSettingsService $settings,
        private readonly NotificationDeliveryService $delivery,
    ) {
    }

    public function bookingCancelled(Booking $booking): void
    {
        $this->deliver(
            event: 'booking_cancelled',
            actionPrefix: 'campaign.booking_cancelled',
            label: 'cancelled booking',
            booking: $booking,
            sendEmail: fn () => $this->delivery->sendBookingCancelledEmail($booking),
            sendLine: fn () => $this->delivery->sendBookingCancelledLine($booking),
        );
    }

    public function bookingConfirmed(Booking $booking): void
    {
        $this->deliver(
            event: 'booking_confirmed',
            actionPrefix: 'campaign.booking_confirmed',
            label: 'confirmed booking',
            booking: $booking,
            sendEmail: fn () => $this->delivery->sendBookingConfirmedEmail($booking),
            sendLine: fn () => $this->delivery->sendBookingConfirmedLine($booking),
        );
    }

    public function bookingReminder(Booking $booking): void
    {
        $this->deliver(
            event: 'booking_reminder',
            actionPrefix: 'campaign.booking_reminder',
            label: 'booking reminder',
            booking: $booking,
            sendEmail: fn () => $this->delivery->sendBookingReminderEmail($booking),
            sendLine: fn () => $this->delivery->sendBookingReminderLine($booking),
        );
    }

    private function deliver(
        string $event,
        string $actionPrefix,
        string $label,
        Booking $booking,
        callable $sendEmail,
        callable $sendLine,
    ): void {
        $sent = [];
        $failed = [];

        if ($this->settings->notificationEnabled('campaign', $event, 'email')) {
            try {
                $sendEmail();
                $sent[] = 'email';
            } catch (\Throwable $e) {
                $failed['email'] = $e->getMessage();
            }
        }

        if (
            $this->settings->notificationEnabled('campaign', $event, 'line')
            && ($this->settings->load()['line_messaging_enabled'] ?? false)
        ) {
            try {
                $sendLine();
                $sent[] = 'line';
            } catch (\Throwable $e) {
                $failed['line'] = $e->getMessage();
            }
        }

        $admin = Auth::guard('admin')->user();

        if ($sent !== []) {
            ActivityLog::create([
                'clinic_id' => $booking->clinic_id,
                'actor_id' => $admin?->id,
                'actor_type' => $admin ? $admin::class : null,
                'action' => $actionPrefix.'_notification_sent',
                'description' => 'Sent '.$label.' notification for booking #'.$booking->id,
                'properties' => [
                    'booking_id' => $booking->id,
                    'booking_code' => $booking->booking_code,
                    'channels' => $sent,
                    'event' => $event,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => (string) request()->userAgent(),
            ]);
        }

        if ($failed !== []) {
            ActivityLog::create([
                'clinic_id' => $booking->clinic_id,
                'actor_id' => $admin?->id,
                'actor_type' => $admin ? $admin::class : null,
                'action' => $actionPrefix.'_notification_failed',
                'description' => 'Failed '.$label.' notification for booking #'.$booking->id,
                'properties' => [
                    'booking_id' => $booking->id,
                    'booking_code' => $booking->booking_code,
                    'errors' => $failed,
                    'event' => $event,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => (string) request()->userAgent(),
            ]);
        }
    }
}
