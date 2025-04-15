<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class rates extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'hall_id',
        'rate',
        'comment',
    ];
}
