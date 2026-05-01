<?php

namespace App\Models;

use App\Models\Traits\BelongsToClinic;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory, BelongsToClinic;

    protected $table = 'vac_appointments';

    protected $fillable = [
        'clinic_id',
        'user_id',
        'camp_id',
        'appointment_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'appointment_at' => 'datetime',
    ];

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class, 'camp_id');
    }
}
