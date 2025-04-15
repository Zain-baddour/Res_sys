<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hall_img extends Model
{
    use HasFactory;

    protected $fillable = [
        'hall_id',
        'image_path',
    ];

    public $appends = [
        'image_path_url'
    ];

    public function getImagePathUrlAttribute()
    {
        return $this->image_path ? asset($this->image_path) : null;
    }

    public function hall() {
        $this->belongsTo(hall::class);
    }

}
