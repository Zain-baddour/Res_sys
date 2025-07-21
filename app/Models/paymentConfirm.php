<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class paymentConfirm extends Model
{
    protected $fillable = [
        'payment_intent_id',
        'hall_id',
        'user_id',
        'amount',
        'currency',
        'status',
    ];

    public function hall() {
        return $this->belongsTo(hall::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
