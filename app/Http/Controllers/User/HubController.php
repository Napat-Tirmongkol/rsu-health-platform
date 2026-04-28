<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Booking;
use App\Models\BorrowRecord;
use App\Models\Campaign;
use App\Models\InsuranceMember;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class HubController extends Controller
{
    public function index()
    {
        $user = Auth::guard('user')->user();
        $today = Carbon::today();

        $campaigns = Campaign::where('status', 'active')
            ->where(function ($query) use ($today) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', $today);
            })
            ->latest()
            ->take(5)
            ->get();

        $announcements = Announcement::where('is_active', true)
            ->latest()
            ->take(5)
            ->get();

        $bookingList = Booking::where('user_id', $user->id)
            ->with(['campaign', 'slot'])
            ->orderByDesc('created_at')
            ->get();

        $upcomingBookings = $bookingList->whereIn('status', ['pending', 'confirmed'])
            ->filter(function ($booking) use ($today) {
                return $booking->slot && $booking->slot->date >= $today->format('Y-m-d');
            });

        $latestBooking = $upcomingBookings->first();
        $upcomingCount = $upcomingBookings->count();

        $insurance = InsuranceMember::where('member_id', $user->username)->first();
        $borrowCount = $this->resolveBorrowCount($user->id);
        $thaiDate = $this->formatThaiDate($today);

        return view('user.hub', compact(
            'user',
            'campaigns',
            'announcements',
            'bookingList',
            'latestBooking',
            'upcomingCount',
            'borrowCount',
            'insurance',
            'thaiDate'
        ));
    }

    private function resolveBorrowCount(int $userId): int
    {
        if (! Schema::hasTable('borrow_records')) {
            return 0;
        }

        try {
            return BorrowRecord::where('borrower_user_id', $userId)
                ->where('status', 'borrowed')
                ->count();
        } catch (QueryException) {
            return 0;
        }
    }

    private function formatThaiDate(Carbon $date): string
    {
        $days = ['อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์'];
        $months = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];

        return $days[$date->dayOfWeek].', '.$date->day.' '.$months[$date->month].' '.($date->year + 543);
    }
}
