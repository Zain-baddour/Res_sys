<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable=[
'phone','description','office_id'
    ];
    protected $hidden=[
        'created_at','updated_at'
    ];
    public function office(){
        return $this->belongsTo(Office::class,'office_id');
    }
}
