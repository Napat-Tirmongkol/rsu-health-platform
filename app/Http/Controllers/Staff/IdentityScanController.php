<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\Campaign;
use App\Models\User;
use App\Services\CampaignNotificationService;
use App\Services\IdentityQrCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class IdentityScanController extends Controller
{
    public function show(?Campaign $campaign = null)
    {
        return view('staff.scan', [
            'campaigns' => Campaign::query()
                ->where('status', 'active')
                ->orderBy('title')
                ->get(['id', 'title']),
            'boundCampaign' => $campaign?->only(['id', 'title']),
            'recentScans' => $this->recentScans(),
        ]);
    }

    public function verify(Request $request, IdentityQrCode $identityQrCode): JsonResponse
    {
        $validated = $request->validate([
            'qr_payload' => ['required', 'string'],
            'campaign_id' => ['nullable', 'integer', 'exists:camp_list,id'],
            'today_only' => ['nullable', 'boolean'],
        ]);

        $payload = $validated['qr_payload'];

        try {
            $user = $identityQrCode->verifyUser($payload);
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        $identity = $user->resolveIdentity();

        $query = Booking::with(['campaign', 'slot'])
            ->where('user_id', $user->id)
            ->whereIn('status', ['pending', 'confirmed', 'attended'])
            ->orderByDesc('created_at');

        if (! empty($validated['campaign_id'])) {
            $query->where('camp_id', $validated['campaign_id']);
        }

        if (! empty($validated['today_only'])) {
            $query->whereHas('slot', fn ($slotQuery) => $slotQuery->whereDate('date', today()));
        }

        $bookings = $query->get()
            ->map(fn (Booking $booking) => [
                'id' => $booking->id,
                'status' => $booking->status,
                'campaign_title' => $booking->campaign?->title ?? '-',
                'campaign_id' => $booking->camp_id,
                'slot_label' => $this->slotLabel($booking),
                'can_check_in' => in_array($booking->status, ['pending', 'confirmed'], true),
            ])
            ->values();

        $recentUserScan = $this->recentUserScan($user->id);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->full_name ?: $user->name,
                'person_type' => $user->status === 'other' ? 'general' : ($user->status ?: 'general'),
                'identity_type' => $identity['type'],
                'identity_label' => $this->identityLabel($identity['type']),
                'identity_value' => $identity['value'],
            ],
            'bookings' => $bookings,
            'duplicate_warning' => $recentUserScan ? [
                'message' => 'This identity was checked in recently.',
                'checked_in_at' => $recentUserScan['checked_in_at'],
                'relative_time' => $recentUserScan['relative_time'],
                'booking_code' => $recentUserScan['booking_code'],
                'campaign_title' => $recentUserScan['campaign_title'],
            ] : null,
            'recent_scans' => $this->recentScans(),
        ]);
    }

    public function checkIn(Request $request, IdentityQrCode $identityQrCode): JsonResponse
    {
        $data = $request->validate([
            'qr_payload' => ['required', 'string'],
            'booking_id' => ['required', 'integer'],
            'check_in_note' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $user = $identityQrCode->verifyUser($data['qr_payload']);
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        $booking = Booking::with(['campaign', 'slot', 'user.primaryIdentity'])->findOrFail($data['booking_id']);

        if ((int) $booking->user_id !== (int) $user->id) {
            return response()->json([
                'message' => 'Booking does not belong to the scanned identity.',
            ], 422);
        }

        if ($booking->status === 'attended') {
            return response()->json([
                'message' => 'This booking has already been checked in.',
            ], 409);
        }

        if ($booking->status === 'cancelled') {
            return response()->json([
                'message' => 'Cancelled bookings cannot be checked in.',
            ], 422);
        }

        if (! in_array($booking->status, ['pending', 'confirmed'], true)) {
            return response()->json([
                'message' => 'This booking is not eligible for check-in.',
            ], 422);
        }

        $note = trim((string) ($data['check_in_note'] ?? ''));

        $booking->update([
            'status' => 'attended',
            'notes' => $note !== ''
                ? trim(implode("\n", array_filter([
                    $booking->notes,
                    '['.now()->format('Y-m-d H:i').'] Check-in note: '.$note,
                ])))
                : $booking->notes,
        ]);

        ActivityLog::create([
            'clinic_id' => $booking->clinic_id,
            'actor_id' => $request->user('staff')->id,
            'actor_type' => $request->user('staff')::class,
            'action' => 'booking.checked_in',
            'description' => 'Checked in booking '.$booking->booking_code,
            'properties' => [
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
                'campaign_id' => $booking->camp_id,
                'slot_id' => $booking->slot_id,
                'check_in_note' => $note !== '' ? $note : null,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ]);

        try {
            app(CampaignNotificationService::class)->bookingCheckedIn($booking);
        } catch (\Throwable) {
        }

        return response()->json([
            'message' => 'Check-in completed.',
            'booking' => [
                'id' => $booking->id,
                'status' => $booking->status,
                'campaign_title' => $booking->campaign?->title ?? '-',
                'slot_label' => $this->slotLabel($booking),
                'notes' => $booking->notes,
            ],
            'recent_scans' => $this->recentScans(),
        ]);
    }

    private function recentScans(int $limit = 8): array
    {
        $logs = ActivityLog::query()
            ->where('action', 'booking.checked_in')
            ->latest()
            ->limit($limit)
            ->get();

        if ($logs->isEmpty()) {
            return [];
        }

        $bookingIds = $logs->pluck('properties.booking_id')->filter()->map(fn ($id) => (int) $id)->all();
        $userIds = $logs->pluck('properties.user_id')->filter()->map(fn ($id) => (int) $id)->all();

        $bookings = Booking::with(['campaign', 'slot'])
            ->withoutGlobalScopes()
            ->whereIn('id', $bookingIds)
            ->get()
            ->keyBy('id');

        $users = User::with('primaryIdentity')
            ->withoutGlobalScopes()
            ->whereIn('id', $userIds)
            ->get()
            ->keyBy('id');

        return $logs->map(function (ActivityLog $log) use ($bookings, $users) {
            $properties = $log->properties ?? [];
            $booking = $bookings->get((int) ($properties['booking_id'] ?? 0));
            $user = $users->get((int) ($properties['user_id'] ?? 0));
            $identity = $user?->resolveIdentity();

            return [
                'booking_id' => $booking?->id,
                'booking_code' => $booking?->booking_code,
                'user_id' => $user?->id,
                'user_name' => $user?->full_name ?: $user?->name ?: 'Unknown user',
                'identity_label' => $identity['label'] ?? 'Identity ID',
                'identity_value' => $identity['value'] ?? '-',
                'campaign_title' => $booking?->campaign?->title ?? '-',
                'slot_label' => $booking ? $this->slotLabel($booking) : '-',
                'checked_in_at' => $log->created_at?->format('Y-m-d H:i:s'),
                'relative_time' => $log->created_at?->diffForHumans(),
                'staff_name' => $log->actor?->full_name ?: $log->actor?->email ?: 'Staff',
            ];
        })->values()->all();
    }

    private function recentUserScan(int $userId): ?array
    {
        $recentScan = collect($this->recentScans(20))
            ->first(fn (array $scan) => (int) ($scan['user_id'] ?? 0) === $userId);

        if (! $recentScan) {
            return null;
        }

        $checkedInAt = isset($recentScan['checked_in_at']) ? Carbon::parse($recentScan['checked_in_at']) : null;

        if (! $checkedInAt || $checkedInAt->lt(now()->subMinutes(30))) {
            return null;
        }

        return $recentScan;
    }

    private function slotLabel(Booking $booking): string
    {
        if (! $booking->slot) {
            return '-';
        }

        $date = $booking->slot->date instanceof Carbon
            ? $booking->slot->date->format('d M Y')
            : Carbon::parse($booking->slot->date)->format('d M Y');

        return trim($date.' '.$booking->slot->start_time.'-'.$booking->slot->end_time);
    }

    private function identityLabel(string $type): string
    {
        return match ($type) {
            'student_id' => 'Student ID',
            'staff_id' => 'Staff ID',
            'citizen_id' => 'Citizen ID',
            'passport' => 'Passport',
            default => 'Identity ID',
        };
    }
}
