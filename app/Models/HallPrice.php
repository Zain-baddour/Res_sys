<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HallPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'hall_id',
        'guest_count',
        'price',
        'type',
    ];

    public function hall() {
        return $this->belongsTo(hall::class);
    }
}
