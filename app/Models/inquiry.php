<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class inquiry extends Model
{
    use HasFactory;

    protected $fillable = [
      'hall_id',
      'user_id',
      'message',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function hall() {
        return $this->belongsTo(hall::class);
    }

    public function responses() {
        return $this->hasMany(inquiryResponse::class);
    }
}
