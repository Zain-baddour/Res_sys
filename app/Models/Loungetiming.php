<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Loungetiming extends Model
{
    protected $fillable = [
        'type',
        'from','to','hall_id'
    ];
}
