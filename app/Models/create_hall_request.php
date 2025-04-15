<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class create_hall_request extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'capacity',
        'location',
        'photo',
        'policies',
        'open_Hours',
        'prices',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id'); // or 'user_name' if using name
    }

}
