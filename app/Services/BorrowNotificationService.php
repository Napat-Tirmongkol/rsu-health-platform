<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\BorrowRecord;
use Illuminate\Support\Facades\Auth;

class BorrowNotificationService
{
    public function __construct(
        private readonly IntegrationSettingsService $settings,
        private readonly NotificationDeliveryService $delivery,
    ) {
    }

    public function requestApproved(BorrowRecord $record): void
    {
        $this->deliver(
            module: 'borrow',
            event: 'request_approved',
            contextActionPrefix: 'borrow.request_approved',
            contextLabel: 'approved borrow request',
            clinicId: $record->clinic_id,
            referenceId: $record->id,
            referenceCode: 'borrow-record-'.$record->id,
            sendEmail: fn () => $this->delivery->sendBorrowRequestApprovedEmail($record),
            sendLine: fn () => $this->delivery->sendBorrowRequestApprovedLine($record),
        );
    }

    private function deliver(
        string $module,
        string $event,
        string $contextActionPrefix,
        string $contextLabel,
        int $clinicId,
        int $referenceId,
        string $referenceCode,
        callable $sendEmail,
        callable $sendLine,
    ): void {
        $sent = [];
        $failed = [];

        if ($this->settings->notificationEnabled($module, $event, 'email')) {
            try {
                $sendEmail();
                $sent[] = 'email';
            } catch (\Throwable $e) {
                $failed['email'] = $e->getMessage();
            }
        }

        if (
            $this->settings->notificationEnabled($module, $event, 'line')
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
                'clinic_id' => $clinicId,
                'actor_id' => $admin?->id,
                'actor_type' => $admin ? $admin::class : null,
                'action' => $contextActionPrefix.'_notification_sent',
                'description' => 'Sent '.$contextLabel.' notification for record #'.$referenceId,
                'properties' => [
                    'reference_id' => $referenceId,
                    'reference_code' => $referenceCode,
                    'channels' => $sent,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => (string) request()->userAgent(),
            ]);
        }

        if ($failed !== []) {
            ActivityLog::create([
                'clinic_id' => $clinicId,
                'actor_id' => $admin?->id,
                'actor_type' => $admin ? $admin::class : null,
                'action' => $contextActionPrefix.'_notification_failed',
                'description' => 'Failed '.$contextLabel.' notification for record #'.$referenceId,
                'properties' => [
                    'reference_id' => $referenceId,
                    'reference_code' => $referenceCode,
                    'errors' => $failed,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => (string) request()->userAgent(),
            ]);
        }
    }
}
