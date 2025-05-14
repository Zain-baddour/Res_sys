<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HallServiceImage extends Model
{
    use HasFactory;

    protected $fillable = [
      'servicetohall_id',
      'image_path',
    ];

    public function service() {
        return $this->belongsTo(Servicetohall::class);
    }
}
