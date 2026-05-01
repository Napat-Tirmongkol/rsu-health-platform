<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slot extends Model
{
    use HasFactory;

    protected $table = 'camp_slots';

    protected $fillable = [
        'camp_id',
        'date',
        'start_time',
        'end_time',
        'max_slots',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Get the campaign that owns the slot.
     */
    public function campaign()
    {
        return $this->belongsTo(Campaign::class, 'camp_id');
    }

    /**
     * Get the bookings for the slot.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'slot_id');
    }

    /**
     * Check if the slot is full.
     */
    public function isFull(): bool
    {
        return $this->bookings()
            ->whereNotIn('status', ['cancelled'])
            ->count() >= $this->max_slots;
    }

    public function remainingCapacity(): int
    {
        $booked = $this->bookings()
            ->whereNotIn('status', ['cancelled'])
            ->count();

        return max(0, $this->max_slots - $booked);
    }
}
