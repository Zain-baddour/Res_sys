<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HallServiceVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'hall_service_id',
        'video_path',
    ];

    public function service() {
        return $this->belongsTo(Servicetohall::class);
    }
}
