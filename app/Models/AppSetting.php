<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $fillable = [
        'monthly_subscription_price',
        'trial_duration_days',
    ];
}
