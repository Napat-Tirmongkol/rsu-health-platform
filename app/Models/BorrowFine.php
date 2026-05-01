<?php

namespace App\Models;

use App\Models\Traits\BelongsToClinic;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BorrowFine extends Model
{
    use BelongsToClinic;
    use HasFactory;

    protected $fillable = [
        'clinic_id',
        'borrow_record_id',
        'user_id',
        'amount',
        'status',
        'notes',
        'created_by_staff_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function record()
    {
        return $this->belongsTo(BorrowRecord::class, 'borrow_record_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function createdByStaff()
    {
        return $this->belongsTo(Staff::class, 'created_by_staff_id');
    }

    public function payments()
    {
        return $this->hasMany(BorrowPayment::class, 'fine_id');
    }
}
