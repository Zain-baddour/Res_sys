<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Detail_booking extends Model
{
    use HasFactory;
    protected $fillable=[
      'from',
      'to','time',
      'office_service_id',
      'office_id',
      'status',
      'user_id',
      'description','car_type','num_car','date_day'
      
    ];
    public function user(){
      return $this->belongsTo(User::class);
  }
  public function office()
  {
      return $this->belongsTo(Office::class);
  }

  public function office_service()
  {
      return $this->belongsTo(Office_service::class);
  }

  public function sendanswers()
  {
      return $this->hasMany(Sendanswer::class); 
  }
}
