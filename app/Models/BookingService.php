<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingService extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'service_type',
        'from_hall',
        'details',
    ];

    protected $casts = [
        'details' => 'array', // لتخزين التفاصيل كـ JSON
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
