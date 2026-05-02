<?php

namespace App\Models;

use App\Models\Traits\BelongsToClinic;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsuranceMember extends Model
{
    use HasFactory, BelongsToClinic;

    protected $table = 'insurance_members';

    protected $fillable = [
        'clinic_id',
        'user_id',
        'member_id',
        'member_type',
        'first_name',
        'last_name',
        'national_id',
        'department',
        'member_status',
        'insurance_status',
        'policy_number',
        'provider_name',
        'coverage_start_date',
        'expires_at',
    ];

    protected $casts = [
        'coverage_start_date' => 'date',
        'expires_at' => 'date',
    ];

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }

    public function getMemberTypeLabeAttribute(): string
    {
        return $this->member_type === 'staff' ? 'บุคลากร' : 'นักศึกษา';
    }
}
