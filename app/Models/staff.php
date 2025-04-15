<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class staff extends Model
{
    use HasFactory;

    protected $fillable = [
      'hall_id',
      'user_id',
    ];

    public function hall(){
        return $this->belongsTo(hall::class);
    }
}
