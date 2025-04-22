<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class hall_employee extends Model
{
    protected $table = 'hall_employees';

    protected $fillable = [
        'hall_id',
        'user_id',
    ];

    public function hall() {
        return $this->belongsTo(hall::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

}
