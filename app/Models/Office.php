<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Office extends Model
{ protected $fillable=[
        'name',
        'location','status',
       'photo','number', 'owner_id',
    ];
    public function services(){
      return $this->hasMany(Office_service::class) ;
    }
    public function contact(){
       return  $this->hasOne(Contact::class) ;
     }
     public function answer(){
      return  $this->hasOne(Sendanswer::class) ;
    }
    public function detail_booking(){
      return $this->hasMany(Detail_booking::class);
  }
}
