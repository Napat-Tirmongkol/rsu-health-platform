<?php

namespace App\Models;

use App\Models\Traits\BelongsToClinic;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class BorrowCategory extends Model
{
    use BelongsToClinic;
    use HasFactory;

    protected $fillable = [
        'clinic_id',
        'name',
        'description',
        'image_path',
        'total_quantity',
        'available_quantity',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        return Storage::disk('public')->url($this->image_path);
    }

    public function items()
    {
        return $this->hasMany(BorrowItem::class, 'category_id');
    }

    public function records()
    {
        return $this->hasMany(BorrowRecord::class, 'category_id');
    }
}
