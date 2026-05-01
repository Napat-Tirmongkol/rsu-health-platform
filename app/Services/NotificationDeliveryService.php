<?php

namespace App\Services;

use App\Mail\BookingCancelledMail;
use App\Mail\BookingCheckedInMail;
use App\Mail\BookingConfirmedMail;
use App\Mail\BookingReminderMail;
use App\Mail\BookingSubmittedMail;
use App\Mail\BorrowRequestApprovedMail;
use App\Mail\IntegrationTestMail;
use App\Models\Booking;
use App\Models\BorrowRecord;
use App\Models\EmailLog;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class NotificationDeliveryService
{
    public function __construct(private readonly IntegrationSettingsService $settings)
    {
    }

    public function sendTestEmail(string $recipient): array
    {
        $this->applyMailConfiguration();
        Mail::mailer($this->mailerName())->to($recipient)->send(new IntegrationTestMail());

        return [
            'mailer' => $this->mailerName(),
            'from_address' => (string) config('mail.from.address'),
            'from_name' => (string) config('mail.from.name'),
        ];
    }

    public function sendBookingSubmittedEmail(Booking $booking): void
    {
        $email = $booking->user?->email;

        if (blank($email)) {
            return;
        }

        $this->sendMailWithLogging(
            clinicId: $booking->clinic_id,
            userId: $booking->user_id,
            recipient: $email,
            subject: 'รับคำขอจองนัดหมาย '.$booking->booking_code,
            callback: fn () => Mail::mailer($this->mailerName())->to($email)->send(new BookingSubmittedMail($booking))
        );
    }

    public function sendBookingSubmittedLine(Booking $booking): array
    {
        $lineUserId = $booking->user?->line_user_id;

        if (blank($lineUserId)) {
            return ['skipped' => true, 'reason' => 'missing_line_user_id'];
        }

        $date = $booking->slot?->date?->format('d/m/Y') ?? '-';
        $time = $booking->slot ? substr((string) $booking->slot->start_time, 0, 5) : '-';

        return $this->sendLineText(
            $lineUserId,
            "รับคำขอจองนัดหมายแล้ว\nรหัสการจอง: {$booking->booking_code}\nบริการ: {$booking->campaign?->title}\nวันที่: {$date} เวลา {$time} น.\nสถานะ: รอการยืนยัน"
        );
    }

    public function sendBookingCheckedInEmail(Booking $booking): void
    {
        $email = $booking->user?->email;

        if (blank($email)) {
            return;
        }

        $this->sendMailWithLogging(
            clinicId: $booking->clinic_id,
            userId: $booking->user_id,
            recipient: $email,
            subject: 'ยืนยัน Check-in สำเร็จ '.$booking->booking_code,
            callback: fn () => Mail::mailer($this->mailerName())->to($email)->send(new BookingCheckedInMail($booking))
        );
    }

    public function sendBookingCheckedInLine(Booking $booking): array
    {
        $lineUserId = $booking->user?->line_user_id;

        if (blank($lineUserId)) {
            return ['skipped' => true, 'reason' => 'missing_line_user_id'];
        }

        $date = $booking->slot?->date?->format('d/m/Y') ?? '-';
        $time = $booking->slot ? substr((string) $booking->slot->start_time, 0, 5) : '-';

        return $this->sendLineText(
            $lineUserId,
            "✅ Check-in สำเร็จแล้ว\nรหัสการจอง: {$booking->booking_code}\nบริการ: {$booking->campaign?->title}\nวันที่: {$date} เวลา {$time} น.\nขอบคุณที่มารับบริการครับ/ค่ะ"
        );
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

    public function sendTestGemini(): array
    {
        $settings = $this->settings->load();

        if (! ($settings['gemini_enabled'] ?? false)) {
            throw new \RuntimeException('Gemini API ยังไม่ได้เปิดใช้งาน');
        }

        $apiKey = trim((string) ($settings['gemini_api_key'] ?? ''));
        $model = trim((string) ($settings['gemini_model'] ?? 'gemini-1.5-flash'));
        $baseUrl = rtrim((string) ($settings['gemini_base_url'] ?: 'https://generativelanguage.googleapis.com'), '/');
        $instruction = trim((string) ($settings['gemini_system_prompt'] ?? ''));

        if ($apiKey === '') {
            throw new \RuntimeException('ยังไม่ได้กำหนด Gemini API key');
        }

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => 'Reply with exactly: RSU Health Platform Gemini test OK'],
                    ],
                ],
            ],
        ];

        if ($instruction !== '') {
            $payload['systemInstruction'] = [
                'parts' => [
                    ['text' => $instruction],
                ],
            ];
        }

        $response = Http::acceptJson()
            ->timeout(20)
            ->post("{$baseUrl}/v1beta/models/{$model}:generateContent?key={$apiKey}", $payload);

        if ($response->failed()) {
            throw new \RuntimeException('Gemini request failed: '.$response->body());
        }

        $text = data_get($response->json(), 'candidates.0.content.parts.0.text', '');

        return [
            'model' => $model,
            'text' => Str::limit(trim((string) $text), 140),
        ];
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
        Config::set('mail.mailers.smtp.scheme', $this->normalizeMailScheme($settings['mail_scheme'] ?? null));
        Config::set('mail.from.address', $settings['mail_from_address'] ?: 'hello@example.com');
        Config::set('mail.from.name', $settings['mail_from_name'] ?: config('app.name', 'RSU Health Platform'));
    }

    private function normalizeMailScheme(mixed $scheme): ?string
    {
        return match ((string) $scheme) {
            'tls' => 'smtp',
            'ssl' => 'smtps',
            'smtp', 'smtps' => (string) $scheme,
            default => null,
        };
    }

    private function mailerName(): string
    {
        $mailer = (string) ($this->settings->load()['mail_mailer'] ?? 'log');

        return in_array($mailer, ['smtp', 'sendmail', 'log'], true) ? $mailer : 'log';
    }
}
