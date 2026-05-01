<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clinic extends Model
{
    use HasFactory;

    protected $table = 'sys_clinics';

    protected $fillable = [
        'name',
        'slug',
        'code',
        'domain',
        'status',
        'description',
        'logo_url',
        'primary_color',
        'contact_email',
        'contact_phone',
    ];

    /**
     * Get the users for the clinic.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'clinic_id');
    }

    /**
     * Get the staff for the clinic.
     */
    public function staff()
    {
        return $this->hasMany(Staff::class, 'clinic_id');
    }
}
