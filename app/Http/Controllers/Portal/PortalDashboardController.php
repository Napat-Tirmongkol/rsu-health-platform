<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BorrowRecord;
use App\Models\Campaign;
use App\Models\Clinic;
use App\Models\SiteSetting;
use App\Models\Staff;
use App\Models\User;
use App\Scopes\TenantScope;

class PortalDashboardController extends Controller
{
    public function index()
    {
        $clinics = Clinic::query()
            ->withCount([
                'users',
                'staff',
            ])
            ->orderBy('name')
            ->get();

        $stats = [
            'total_clinics' => $clinics->count(),
            'active_clinics' => $clinics->where('status', 'active')->count(),
            'total_users' => User::withoutGlobalScope(TenantScope::class)->count(),
            'total_staff' => Staff::withoutGlobalScope(TenantScope::class)->count(),
            'active_campaigns' => Campaign::withoutGlobalScope(TenantScope::class)
                ->where('status', 'active')
                ->count(),
            'pending_bookings' => Booking::withoutGlobalScope(TenantScope::class)
                ->where('status', 'pending')
                ->count(),
            'active_borrow_records' => BorrowRecord::withoutGlobalScope(TenantScope::class)
                ->where('status', 'borrowed')
                ->count(),
            'global_settings' => SiteSetting::withoutGlobalScope(TenantScope::class)
                ->where('clinic_id', 0)
                ->count(),
        ];

        $clinicSnapshots = $clinics->map(function (Clinic $clinic) {
            return [
                'id' => $clinic->id,
                'name' => $clinic->name,
                'slug' => $clinic->slug,
                'status' => $clinic->status,
                'users_count' => $clinic->users_count,
                'staff_count' => $clinic->staff_count,
                'campaigns_count' => Campaign::withoutGlobalScope(TenantScope::class)
                    ->where('clinic_id', $clinic->id)
                    ->where('status', 'active')
                    ->count(),
                'pending_bookings_count' => Booking::withoutGlobalScope(TenantScope::class)
                    ->where('clinic_id', $clinic->id)
                    ->where('status', 'pending')
                    ->count(),
            ];
        });

        return view('portal.dashboard', [
            'stats' => $stats,
            'clinicSnapshots' => $clinicSnapshots,
        ]);
    }

}
