<?php

namespace App\Models;

use App\Models\Traits\BelongsToClinic;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BorrowRecord extends Model
{
    use BelongsToClinic;
    use HasFactory;

    protected $fillable = [
        'clinic_id',
        'category_id',
        'item_id',
        'borrower_user_id',
        'lending_staff_id',
        'approver_staff_id',
        'return_staff_id',
        'quantity',
        'reason',
        'borrowed_at',
        'due_date',
        'returned_at',
        'status',
        'approval_status',
        'attachment_path',
        'fine_status',
        'notes',
    ];

    protected $casts = [
        'borrowed_at' => 'datetime',
        'due_date' => 'date',
        'returned_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(BorrowCategory::class, 'category_id');
    }

    public function item()
    {
        return $this->belongsTo(BorrowItem::class, 'item_id');
    }

    public function borrower()
    {
        return $this->belongsTo(User::class, 'borrower_user_id');
    }

    public function lendingStaff()
    {
        return $this->belongsTo(Staff::class, 'lending_staff_id');
    }

    public function approverStaff()
    {
        return $this->belongsTo(Staff::class, 'approver_staff_id');
    }

    public function returnStaff()
    {
        return $this->belongsTo(Staff::class, 'return_staff_id');
    }

    public function fines()
    {
        return $this->hasMany(BorrowFine::class, 'borrow_record_id');
    }
}
