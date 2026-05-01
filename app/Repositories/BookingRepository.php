<?php

namespace App\Repositories;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Collection;

class BookingRepository extends BaseRepository
{
    public function __construct(Booking $model)
    {
        parent::__construct($model);
    }

    /**
     * Get bookings by user ID.
     */
    public function getByUserId(int $userId): Collection
    {
        return $this->model->where('user_id', $userId)
            ->with(['campaign', 'slot'])
            ->latest()
            ->get();
    }

    /**
     * Find booking by code.
     */
    public function findByCode(string $code): ?Booking
    {
        return $this->model->where('booking_code', $code)
            ->with(['user', 'campaign', 'slot'])
            ->first();
    }

    /**
     * Get active bookings for a specific clinic.
     */
    public function getActiveBookings(): Collection
    {
        // หมายเหตุ: TenantScope จะกรอง clinic_id ให้อัตโนมัติอยู่แล้ว
        return $this->model->whereIn('status', ['pending', 'confirmed'])
            ->with(['user', 'campaign'])
            ->get();
    }
}
