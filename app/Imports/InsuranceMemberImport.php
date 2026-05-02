<?php

namespace App\Imports;

use App\Models\InsuranceMember;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class InsuranceMemberImport implements ToModel, WithHeadingRow, WithUpserts, SkipsEmptyRows
{
    protected int $clinicId;
    protected string $memberType;
    protected string $memberStatus;

    public function __construct(int $clinicId, string $memberType, string $memberStatus = 'active')
    {
        $this->clinicId = $clinicId;
        $this->memberType = $memberType;
        $this->memberStatus = $memberStatus;
    }

    public function model(array $row): InsuranceMember
    {
        return new InsuranceMember([
            'clinic_id'     => $this->clinicId,
            'member_id'     => trim($row['รหัส'] ?? $row['member_id'] ?? ''),
            'member_type'   => $this->memberType,
            'first_name'    => trim($row['ชื่อ'] ?? $row['first_name'] ?? ''),
            'last_name'     => trim($row['นามสกุล'] ?? $row['last_name'] ?? ''),
            'national_id'   => trim($row['เลขบัตรประชาชน'] ?? $row['national_id'] ?? ''),
            'department'    => trim($row['คณะ_แผนก'] ?? $row['คณะ/แผนก'] ?? $row['department'] ?? ''),
            'member_status' => $this->memberStatus,
            'provider_name' => 'เมืองไทยประกันภัย',
        ]);
    }

    public function uniqueBy(): array
    {
        return ['clinic_id', 'member_id'];
    }
}
