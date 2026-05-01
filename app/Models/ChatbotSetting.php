<?php

namespace App\Models;

use App\Models\Traits\BelongsToClinic;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotSetting extends Model
{
    use BelongsToClinic;
    use HasFactory;

    protected $fillable = [
        'clinic_id',
        'system_prompt',
        'model',
        'temperature',
        'daily_quota',
    ];

    protected $casts = [
        'temperature' => 'float',
        'daily_quota' => 'integer',
    ];
}
