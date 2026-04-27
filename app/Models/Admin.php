<?php

namespace App\Models;

use App\Models\Traits\BelongsToClinic;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable, BelongsToClinic;

    protected $table = 'sys_admins';

    protected $fillable = [
        'clinic_id',
        'name',
        'email',
        'google_id',
        'profile_photo_path',
    ];

    /**
     * Get the clinic that owns the admin.
     */
    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }
}
