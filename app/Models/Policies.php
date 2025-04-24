<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Policies extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'hall_id'
    ];

    public function hall(){
        return $this->belongsTo(hall::class);
    }
}
