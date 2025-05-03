<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Detail_img extends Model
{
    protected $fillable = [
        'detail_id',
        'image_path',
    ];

    public function getImagePathUrlAttribute()
    {
        return $this->image_path ? asset($this->image_path) : null;
    }

    public function detail() {
        $this->belongsTo(DetailsHall::class);
    }
}
