<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HallVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'hall_id',
        'video_path',
    ];

    public function hall() {
        return $this->belongsTo(hall::class);
    }
}
