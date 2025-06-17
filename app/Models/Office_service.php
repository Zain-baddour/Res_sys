<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Office_service extends Model
{
    protected $fillable=[
'type_car','car_image','office_id'
    ];
    public function office()
    {
        return $this->belongsTo(Office::class);
    }
    public function detail_booking(){
        return $this->hasMany(Detail_booking::class);
    }
}
