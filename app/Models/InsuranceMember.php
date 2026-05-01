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
        'policy_number',
        'provider_name',
        'expires_at',
        'status',
    ];

    protected $casts = [
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
}
