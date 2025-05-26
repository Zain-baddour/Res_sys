<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servicetohall extends Model
{
    use HasFactory;

    protected $fillable = [
    'hall_id',
    'name',
    'service_price',
    'description',
    'is_fixed',
];

    protected $casts = [
        'description' => 'array',
    ];

    public function hall() {
        return $this->belongsTo(hall::class);
    }

    public function images() {
        return $this->hasMany(HallServiceImage::class);
    }

    public function video() {
        return $this->hasOne(HallServiceVideo::class);
    }

}
