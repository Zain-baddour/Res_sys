<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'hall_id',
        'event_date',
        'guest_count',
        'event_type',
        'status',
        'payment_confirmed',
    ];

    protected $casts = [
        'payment_confirmed' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function hall()
    {
        return $this->belongsTo(hall::class);
    }

    public function payment()
    {
        return $this->hasOne(payments::class);
    }
}
