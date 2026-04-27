<?php

namespace App\Models;

use App\Models\Traits\BelongsToClinic;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Staff extends Authenticatable
{
    use HasFactory, Notifiable, BelongsToClinic;

    protected $table = 'sys_staff';

    protected $fillable = [
        'clinic_id',
        'user_id',
        'username',
        'full_name',
        'email',
        'password',
        'role',
        'permissions',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'permissions' => 'array',
        'password' => 'hashed',
    ];

    /**
     * Get the clinic that owns the staff.
     */
    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Get the user record associated with the staff.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
