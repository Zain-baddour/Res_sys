<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingSong extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'person_name',
        'song_name',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
