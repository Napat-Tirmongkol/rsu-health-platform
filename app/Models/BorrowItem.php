<?php

namespace App\Models;

use App\Models\Traits\BelongsToClinic;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BorrowItem extends Model
{
    use BelongsToClinic;
    use HasFactory;

    protected $fillable = [
        'clinic_id',
        'category_id',
        'name',
        'description',
        'image_path',
        'serial_number',
        'status',
    ];

    public function category()
    {
        return $this->belongsTo(BorrowCategory::class, 'category_id');
    }

    public function records()
    {
        return $this->hasMany(BorrowRecord::class, 'item_id');
    }
}
