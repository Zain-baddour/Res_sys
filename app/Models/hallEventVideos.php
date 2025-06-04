<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class hallEventVideos extends Model
{
    protected $fillable = [
        'hall_id',
        'video_path',
        'event_type',
    ];

    public function hall() {
        return $this->belongsTo(hall::class);
    }
}
