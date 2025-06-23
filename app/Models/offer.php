<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class offer extends Model
{
    use HasFactory;
    protected $fillable = [
        'start_offer',
        'end_offer',
        'offer_val',
        //'removable',
      //  'description',
        'hall_id'
    ];

    public function hall() {
        return $this->belongsTo(hall::class);
    }
}
