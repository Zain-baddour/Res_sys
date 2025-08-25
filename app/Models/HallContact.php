<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HallContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'hall_id',
        'telegram',
        'whatsUp',
    ];

    public function hall(){
        return $this->belongsTo(hall::class);
    }
    public function user(){
        return $this->belongsTo(User::class);
    }
}
