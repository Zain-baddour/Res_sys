<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;
    protected $fillable=[
        'phone',
        'description',
    'office_id'];
    protected $hidden=[
        'updated_at','created_at'
    ];

        public function office(){
            return $this->hasOne(Office::class,'office_id');
        }

}
