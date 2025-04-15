<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class inquiryResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'inquiry_id',
        'user_id',
        'response',
    ];

    public function inquiry() {
        return $this->belongsTo(inquiry::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
