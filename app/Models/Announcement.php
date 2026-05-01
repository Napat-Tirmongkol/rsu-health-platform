<?php

namespace App\Models;

use App\Models\Traits\BelongsToClinic;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory, BelongsToClinic;

    protected $table = 'sys_announcements';

    protected $fillable = [
        'clinic_id',
        'title',
        'content',
        'type',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    /**
     * Get the clinic that owns the announcement.
     */
    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function reads()
    {
        return $this->hasMany(UserAnnouncementRead::class, 'announcement_id');
    }
}
