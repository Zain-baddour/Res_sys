<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Office extends Model
{
    use HasFactory;
    protected $fillable=[
        'car_image',
        'type_car',
        'num_ofcar'

    ];
    public function detail_booking(): HasMany {
        return $this->hasMany(Detail_booking::class, 'office_id');
    }

    public function contact() {
        return $this->hasMany(Contact::class);
    }
}
