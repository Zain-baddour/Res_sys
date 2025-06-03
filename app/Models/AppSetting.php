<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $fillable = [
        'subscription_value',
        'subscription_duration_days',
        'currency',
    ];


}
