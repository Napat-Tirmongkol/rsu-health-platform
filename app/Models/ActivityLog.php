<?php

namespace App\Models;

use App\Models\Traits\BelongsToClinic;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory, BelongsToClinic;

    protected $table = 'sys_activity_logs';

    protected $fillable = [
        'clinic_id',
        'actor_id',
        'actor_type',
        'action',
        'description',
        'properties',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    /**
     * Get the clinic that owns the activity log.
     */
    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Get the actor who performed the action.
     */
    public function actor()
    {
        return $this->morphTo();
    }
}
