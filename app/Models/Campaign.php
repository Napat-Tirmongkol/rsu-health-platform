<?php

namespace App\Models;

use App\Models\Traits\BelongsToClinic;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory, BelongsToClinic;

    protected $table = 'camp_list';

    protected $fillable = [
        'clinic_id',
        'title',
        'description',
        'total_capacity',
        'image_path',
        'type',
        'status',
        'is_auto_approve',
        'share_token',
        'starts_at',
        'ends_at',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($campaign) {
            $campaign->share_token ??= bin2hex(random_bytes(8));
        });
    }

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    /**
     * Get the clinic that owns the campaign.
     */
    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Get the slots for the campaign.
     */
    public function slots()
    {
        return $this->hasMany(Slot::class, 'camp_id');
    }

    /**
     * Get the bookings for the campaign.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'camp_id');
    }
}
