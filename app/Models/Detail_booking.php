<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Detail_booking extends Model
{
    use HasFactory;
    protected $fillable=[
      'from',
      'to','date',
      'office_id',
      'user_id',
      'description','car_type','num_car','date_day'
      
    ];
    public function user(){
      return $this->belongsTo(User::class);
  }

}
