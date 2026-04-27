<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Booking;
use App\Models\Campaign;
use App\Models\InsuranceMember;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class HubController extends Controller
{
    public function index()
    {
        $user = Auth::guard('user')->user();
        $today = Carbon::today();

        // 1. แคมเปญที่กำลังเปิดอยู่
        $campaigns = Campaign::where('status', 'active')
            ->where(function ($query) use ($today) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', $today);
            })
            ->latest()
            ->take(5)
            ->get();

        // 2. ประกาศล่าสุด (ที่ยังไม่ได้อ่าน)
        $announcements = Announcement::where('is_active', true)
            ->latest()
            ->take(5)
            ->get();

        // 3. รายการจองทั้งหมด (สำหรับสถิติ)
        $bookingList = Booking::where('user_id', $user->id)
            ->with(['campaign', 'slot'])
            ->orderBy('created_at', 'desc')
            ->get();

        // 4. นัดหมายที่กำลังจะมาถึง (Upcoming)
        $upcomingBookings = $bookingList->whereIn('status', ['pending', 'confirmed'])
            ->filter(function($b) use ($today) {
                return $b->slot && $b->slot->date >= $today->format('Y-m-d');
            });

        $latestBooking = $upcomingBookings->first();
        $upcomingCount = $upcomingBookings->count();

        // 5. ข้อมูลประกัน (Insurance)
        $insurance = InsuranceMember::where('member_id', $user->username) // ใช้ username/student_id เป็น member_id
            ->first();

        // 6. ข้อมูลอื่นๆ (จำลองค่าสำหรับดีไซน์)
        $borrowCount = 0; // ใน Phase ถัดไปจะดึงจากตาราง borrow_records
        
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

    private function formatThaiDate($date)
    {
        $days = ["อาทิตย์", "จันทร์", "อังคาร", "พุธ", "พฤหัสบดี", "ศุกร์", "เสาร์"];
        $months = ["", "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"];
        
        return $days[$date->dayOfWeek] . ", " . $date->day . " " . $months[$date->month] . " " . ($date->year + 543);
    }
}
