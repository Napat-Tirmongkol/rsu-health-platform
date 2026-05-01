<?php

namespace App\Services;

use App\Mail\BookingCancelledMail;
use App\Mail\BookingConfirmedMail;
use App\Mail\BookingReminderMail;
use App\Mail\BorrowRequestApprovedMail;
use App\Mail\IntegrationTestMail;
use App\Models\Booking;
use App\Models\BorrowRecord;
use App\Models\EmailLog;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class NotificationDeliveryService
{
    public function __construct(private readonly IntegrationSettingsService $settings)
    {
    }

    public function sendTestEmail(string $recipient): void
    {
        $this->applyMailConfiguration();
        Mail::mailer($this->mailerName())->to($recipient)->send(new IntegrationTestMail());
    }

    public function sendBookingCancelledEmail(Booking $booking): void
    {
        $email = $booking->user?->email;

        if (blank($email)) {
            return;
        }

        $this->sendMailWithLogging(
            clinicId: $booking->clinic_id,
            userId: $booking->user_id,
            recipient: $email,
            subject: 'แจ้งยกเลิกการจองนัดหมาย '.$booking->booking_code,
            callback: fn () => Mail::mailer($this->mailerName())->to($email)->send(new BookingCancelledMail($booking))
        );
    }

    public function sendBookingConfirmedEmail(Booking $booking): void
    {
        $email = $booking->user?->email;

        if (blank($email)) {
            return;
        }

        $this->sendMailWithLogging(
            clinicId: $booking->clinic_id,
            userId: $booking->user_id,
            recipient: $email,
            subject: 'ยืนยันการจองนัดหมาย '.$booking->booking_code,
            callback: fn () => Mail::mailer($this->mailerName())->to($email)->send(new BookingConfirmedMail($booking))
        );
    }

    public function sendBookingReminderEmail(Booking $booking): void
    {
        $email = $booking->user?->email;

        if (blank($email)) {
            return;
        }

        $this->sendMailWithLogging(
            clinicId: $booking->clinic_id,
            userId: $booking->user_id,
            recipient: $email,
            subject: 'เตือนการนัดหมาย '.$booking->booking_code,
            callback: fn () => Mail::mailer($this->mailerName())->to($email)->send(new BookingReminderMail($booking))
        );
    }

    public function sendBorrowRequestApprovedEmail(BorrowRecord $record): void
    {
        $email = $record->borrower?->email;

        if (blank($email)) {
            return;
        }

        $this->sendMailWithLogging(
            clinicId: $record->clinic_id,
            userId: $record->borrower_user_id,
            recipient: $email,
            subject: 'แจ้งอนุมัติคำขอยืมอุปกรณ์',
            callback: fn () => Mail::mailer($this->mailerName())->to($email)->send(new BorrowRequestApprovedMail($record))
        );
    }

    public function sendTestLine(string $lineUserId): array
    {
        return $this->sendLineText($lineUserId, 'LINE Messaging API test from RSU Health Platform');
    }

    public function sendBookingCancelledLine(Booking $booking): array
    {
        $lineUserId = $booking->user?->line_user_id;

        if (blank($lineUserId)) {
            return ['skipped' => true, 'reason' => 'missing_line_user_id'];
        }

        $date = $booking->slot?->date?->format('d/m/Y') ?? '-';
        $time = $booking->slot ? substr((string) $booking->slot->start_time, 0, 5) : '-';

        return $this->sendLineText(
            $lineUserId,
            "รายการนัดหมายของคุณถูกยกเลิกแล้ว\nรหัสการจอง: {$booking->booking_code}\nบริการ: {$booking->campaign?->title}\nวันที่: {$date} เวลา {$time} น."
        );
    }

    public function sendBookingConfirmedLine(Booking $booking): array
    {
        $lineUserId = $booking->user?->line_user_id;

        if (blank($lineUserId)) {
            return ['skipped' => true, 'reason' => 'missing_line_user_id'];
        }

        $date = $booking->slot?->date?->format('d/m/Y') ?? '-';
        $time = $booking->slot ? substr((string) $booking->slot->start_time, 0, 5) : '-';

        return $this->sendLineText(
            $lineUserId,
            "การจองของคุณได้รับการยืนยันแล้ว\nรหัสการจอง: {$booking->booking_code}\nบริการ: {$booking->campaign?->title}\nวันที่: {$date} เวลา {$time} น."
        );
    }

    public function sendBookingReminderLine(Booking $booking): array
    {
        $lineUserId = $booking->user?->line_user_id;

        if (blank($lineUserId)) {
            return ['skipped' => true, 'reason' => 'missing_line_user_id'];
        }

        $date = $booking->slot?->date?->format('d/m/Y') ?? '-';
        $time = $booking->slot ? substr((string) $booking->slot->start_time, 0, 5) : '-';

        return $this->sendLineText(
            $lineUserId,
            "เตือนการนัดหมายของคุณในวันพรุ่งนี้\nรหัสการจอง: {$booking->booking_code}\nบริการ: {$booking->campaign?->title}\nวันที่: {$date} เวลา {$time} น."
        );
    }

    public function sendBorrowRequestApprovedLine(BorrowRecord $record): array
    {
        $lineUserId = $record->borrower?->line_user_id;

        if (blank($lineUserId)) {
            return ['skipped' => true, 'reason' => 'missing_line_user_id'];
        }

        $dueDate = $record->due_date?->format('d/m/Y') ?? '-';
        $itemName = $record->item?->name ?? $record->category?->name ?? 'อุปกรณ์';

        return $this->sendLineText(
            $lineUserId,
            "คำขอยืมของคุณได้รับการอนุมัติแล้ว\nอุปกรณ์: {$itemName}\nกำหนดคืน: {$dueDate}"
        );
    }

    private function sendLineText(string $lineUserId, string $message): array
    {
        $token = (string) $this->settings->load()['line_channel_access_token'];

        $response = Http::withToken($token)
            ->acceptJson()
            ->post('https://api.line.me/v2/bot/message/push', [
                'to' => $lineUserId,
                'messages' => [
                    ['type' => 'text', 'text' => $message],
                ],
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('LINE push failed: '.$response->body());
        }

        return $response->json() ?? ['ok' => true];
    }

    private function sendMailWithLogging(int $clinicId, ?int $userId, string $recipient, string $subject, callable $callback): void
    {
        try {
            $this->applyMailConfiguration();
            $callback();

            EmailLog::create([
                'clinic_id' => $clinicId,
                'user_id' => $userId,
                'recipient' => $recipient,
                'subject' => $subject,
                'status' => 'sent',
            ]);
        } catch (\Throwable $e) {
            EmailLog::create([
                'clinic_id' => $clinicId,
                'user_id' => $userId,
                'recipient' => $recipient,
                'subject' => $subject,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function applyMailConfiguration(): void
    {
        $settings = $this->settings->load();

        Config::set('mail.default', $this->mailerName());
        Config::set('mail.mailers.smtp.transport', 'smtp');
        Config::set('mail.mailers.smtp.host', $settings['mail_host'] ?: '127.0.0.1');
        Config::set('mail.mailers.smtp.port', (int) ($settings['mail_port'] ?: 2525));
        Config::set('mail.mailers.smtp.username', $settings['mail_username'] ?: null);
        Config::set('mail.mailers.smtp.password', $settings['mail_password'] ?: null);
        Config::set('mail.mailers.smtp.scheme', $settings['mail_scheme'] ?: null);
        Config::set('mail.from.address', $settings['mail_from_address'] ?: 'hello@example.com');
        Config::set('mail.from.name', $settings['mail_from_name'] ?: config('app.name', 'RSU Health Platform'));
    }

    private function mailerName(): string
    {
        $mailer = (string) ($this->settings->load()['mail_mailer'] ?? 'log');

        return in_array($mailer, ['smtp', 'sendmail', 'log'], true) ? $mailer : 'log';
    }
}
