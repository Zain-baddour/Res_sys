<?php

namespace App\Models;

use App\Models\Hall_img;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class hall extends Model
{
    use HasFactory;

    protected $fillable = [
        'hall_image',
        'name',
        'owner_id',
        'location',
        'capacity',
        'contact',
        'type',
        'events',
        'pay_methods',
        'status',
        'rate',
    ];

    public $appends = [
        'hall_image_url',
        ];

    protected $casts = [
        'events' => 'array',
    ];

    public function getHallImageUrlAttribute()
    {
        return $this->hall_image ? asset($this->hall_image) : null;
    }

    public function owner(){
        return $this->belongsTo(User::class,'owner_id');
    }

    public function employees() {
        return $this->belongsToMany(User::class,'hall_employees');
    }

    public function employee() {
        return $this->hasMany(hall_employee::class);
    }



    public function images(): HasMany {
        return $this->hasMany(Hall_img::class, 'hall_id');
    }

    public function staff_requests(): HasMany {
        return $this->hasMany(staff_requests::class, 'hall_id');
    }

    public function offer(){
        return $this->hasMany(Offer::class);
    }
}
