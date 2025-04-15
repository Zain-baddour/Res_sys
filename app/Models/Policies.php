<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Policies extends Model
{
    use HasFactory;

    protected $fillable = [
        'period_of_pay',
        'panalty',
        'stutas_pay',
        'description',
        'hall_id'
    ];

    public function hall(){
        return $this->belongsTo(hall::class);
    }
}
