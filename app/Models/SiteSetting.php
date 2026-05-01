<?php

namespace App\Models;

use App\Models\Traits\BelongsToClinic;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    use HasFactory, BelongsToClinic;

    protected $table = 'sys_site_settings';

    protected $fillable = [
        'clinic_id',
        'key',
        'value',
        'type',
    ];

    /**
     * Get the value cast to its type.
     */
    public function getCastedValueAttribute()
    {
        return match ($this->type) {
            'boolean' => (bool) $this->value,
            'json' => json_decode($this->value, true),
            'integer' => (int) $this->value,
            default => $this->value,
        };
    }

    /**
     * Get the clinic that owns the site setting.
     */
    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }
}
