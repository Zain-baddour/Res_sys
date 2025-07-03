<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sendanswer extends Model
{
    protected $fillable=[
        'answer','detail_booking_id','office_id','user_id'
            ];

            public function office()
            {
                return $this->belongsTo(Office::class);
            } 
            
            public function detail_booking(){
                return $this->belongsTo(Detail_booking::class);
            }


}
