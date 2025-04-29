<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailsHall extends Model
{
    use HasFactory;
    protected $fillable=[
        'type_hall',
        'card_price',
        'res_price',
        'number',
        'num_person',
        'location',
        'path_image',
        'hall_id'
    ] ;
    public function hall(){
        return $this->belongsTo(hall::class);
    }
}
