<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sendanswer extends Model
{
    protected $fillable=[
        'answer','detail_id','office_id','user_id'
            ];

            public function office()
            {
                return $this->belongsTo(Office::class);
            }
}
