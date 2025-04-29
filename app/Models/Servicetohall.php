<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Servicetohall extends Model
{ protected $fillable = [
    'name','video_path',
    'price','description','hall_id'
];
}
