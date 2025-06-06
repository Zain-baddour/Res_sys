<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paymentway extends Model
{
    protected $fillable = [
        'type',
        'hall_id'
    ];


    public function hall() {
        return $this->belongsTo(hall::class);
    }
}
