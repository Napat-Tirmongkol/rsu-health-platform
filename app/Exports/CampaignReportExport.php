<?php

namespace App\Exports;

use App\Models\Booking;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CampaignReportExport implements FromQuery, WithHeadings, WithMapping
{
    protected $campaignId;

    public function __construct($campaignId)
    {
        $this->campaignId = $campaignId;
    }

    public function query()
    {
        return Booking::query()
            ->where('camp_id', $this->campaignId)
            ->with(['user', 'slot']);
    }

    public function headings(): array
    {
        return [
            'ID การจอง',
            'รหัสนักศึกษา/บุคลากร',
            'ชื่อ-นามสกุล',
            'เบอร์โทรศัพท์',
            'แคมเปญ',
            'วันที่จัดงาน',
            'เวลา',
            'สถานะ',
        ];
    }

    public function map($booking): array
    {
        return [
            $booking->id,
            $booking->user ? $booking->user->student_personnel_id : '-',
            $booking->user ? $booking->user->full_name : '-',
            $booking->user ? ($booking->user->phone_number ?: $booking->user->phone ?: '-') : '-',
            $booking->campaign ? $booking->campaign->title : '-',
            $booking->slot ? $booking->slot->date->format('d/m/Y') : '-',
            $booking->slot ? substr($booking->slot->start_time, 0, 5) . ' - ' . substr($booking->slot->end_time, 0, 5) : '-',
            strtoupper($booking->status),
        ];
    }
}
