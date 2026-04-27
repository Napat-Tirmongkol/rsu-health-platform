<?php

namespace App\Models;

use App\Models\Traits\BelongsToClinic;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Booking extends Model
{
    use HasFactory, BelongsToClinic;

    protected $table = 'camp_bookings';

    protected $fillable = [
        'clinic_id',
        'user_id',
        'camp_id',
        'slot_id',
        'booking_code',
        'status',
        'notes',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        static::creating(function ($booking) {
            if (empty($booking->booking_code)) {
                $booking->booking_code = 'BK-' . strtoupper(Str::random(8));
            }
        });
    }

    /**
     * Get the clinic that owns the booking.
     */
    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Get the user that made the booking.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the campaign for the booking.
     */
    public function campaign()
    {
        return $this->belongsTo(Campaign::class, 'camp_id');
    }

    /**
     * Get the slot for the booking.
     */
    public function slot()
    {
        return $this->belongsTo(Slot::class, 'slot_id');
    }
}
