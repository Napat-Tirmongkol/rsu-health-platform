<?php

namespace App\Models;

use App\Models\Traits\BelongsToClinic;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BorrowPayment extends Model
{
    use BelongsToClinic;
    use HasFactory;

    protected $fillable = [
        'clinic_id',
        'fine_id',
        'amount_paid',
        'payment_method',
        'payment_slip_path',
        'payment_date',
        'received_by_staff_id',
        'receipt_number',
        'payment_notes',
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'payment_date' => 'datetime',
    ];

    public function fine()
    {
        return $this->belongsTo(BorrowFine::class, 'fine_id');
    }

    public function receivedByStaff()
    {
        return $this->belongsTo(Staff::class, 'received_by_staff_id');
    }
}
