<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class staff_requests extends Model
{
    use HasFactory;

    protected $fillable = [
        'hall_id',
        'user_id',
        'status',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function hall(){
        return $this->belongsTo(hall::class);
    }

}
