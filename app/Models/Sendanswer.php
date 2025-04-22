<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sendanswer extends Model
{
    use HasFactory;
    protected $fillable=[
        'answer',
    'office_id','detail_id','user_id'];
}
