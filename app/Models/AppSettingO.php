<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSettingO extends Model
{
    protected $fillable = [
        'subscription_value',
        'subscription_duration_days',
        'currency',
    ];
}
