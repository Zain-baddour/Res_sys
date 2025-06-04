<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class hallEventImages extends Model
{
    protected $fillable = [
        'hall_id',
        'image_path',
        'event_type',
    ];

    public function hall() {
        return $this->belongsTo(hall::class);
    }
}
