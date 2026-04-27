<?php

namespace App\Models;

use App\Models\Traits\BelongsToClinic;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SatisfactionSurvey extends Model
{
    use HasFactory, BelongsToClinic;

    protected $table = 'satisfaction_surveys';

    protected $fillable = [
        'clinic_id',
        'booking_id',
        'score',
        'comment',
        'detailed_responses',
    ];

    protected $casts = [
        'detailed_responses' => 'array',
    ];

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
