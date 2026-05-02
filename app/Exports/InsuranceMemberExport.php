<?php

namespace App\Exports;

use App\Models\InsuranceMember;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InsuranceMemberExport implements FromQuery, WithHeadings, WithMapping
{
    protected int $clinicId;
    protected string $memberType;

    public function __construct(int $clinicId, string $memberType = 'all')
    {
        $this->clinicId = $clinicId;
        $this->memberType = $memberType;
    }

    public function query()
    {
        $query = InsuranceMember::withoutGlobalScopes()
            ->where('clinic_id', $this->clinicId)
            ->where('member_status', 'active');

        if ($this->memberType !== 'all') {
            $query->where('member_type', $this->memberType);
        }

        return $query->orderBy('member_type')->orderBy('member_id');
    }

    public function headings(): array
    {
        return [
            'รหัส',
            'ประเภท',
            'คำนำหน้า',
            'ชื่อ',
            'นามสกุล',
            'เลขบัตรประชาชน',
            'คณะ/แผนก',
            'สถานะสมาชิก',
            'เลขกรมธรรม์',
        ];
    }

    public function map($member): array
    {
        return [
            $member->member_id,
            $member->member_type === 'staff' ? 'บุคลากร' : 'นักศึกษา',
            '',
            $member->first_name,
            $member->last_name,
            $member->national_id,
            $member->department,
            $member->member_status,
            $member->policy_number,
        ];
    }
}
