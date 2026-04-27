<?php

namespace App\Models;

use App\Models\Traits\BelongsToClinic;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    use HasFactory, BelongsToClinic;

    protected $table = 'sys_email_logs';

    protected $fillable = [
        'clinic_id',
        'user_id',
        'recipient',
        'subject',
        'status',
        'error_message',
    ];

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
