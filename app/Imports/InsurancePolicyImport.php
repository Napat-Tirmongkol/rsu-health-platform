<?php

namespace App\Imports;

use App\Models\InsuranceMember;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class InsurancePolicyImport implements ToModel, WithHeadingRow, WithUpserts, SkipsEmptyRows
{
    protected int $clinicId;

    public function __construct(int $clinicId)
    {
        $this->clinicId = $clinicId;
    }

    public function model(array $row): InsuranceMember
    {
        $memberId = trim($row['รหัส'] ?? $row['member_id'] ?? '');
        $policyNumber = trim($row['เลขกรมธรรม์'] ?? $row['policy_number'] ?? '');

        $member = InsuranceMember::withoutGlobalScopes()
            ->where('clinic_id', $this->clinicId)
            ->where('member_id', $memberId)
            ->first();

        if ($member && $policyNumber) {
            $member->policy_number = $policyNumber;
            $member->insurance_status = 'active';
            $member->coverage_start_date = $row['วันเริ่มคุ้มครอง'] ?? $row['coverage_start_date'] ?? now();
            $member->expires_at = $row['วันสิ้นสุดคุ้มครอง'] ?? $row['coverage_end_date'] ?? null;
            $member->save();
        }

        return new InsuranceMember();
    }

    public function uniqueBy(): array
    {
        return ['clinic_id', 'member_id'];
    }
}
