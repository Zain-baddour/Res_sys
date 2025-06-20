<?php

// app/Services/StripeService.php
namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class StripeService
{
//    public function __construct()
//    {
//        Stripe::setApiKey(config('services.stripe.secret'));
//    }
//
//    public function createPaymentIntent($amount, $currency = 'usd')
//    {
//        return PaymentIntent::create([
//            'amount' => $amount * 100, // Stripe uses cents
//            'currency' => $currency,
//            'payment_method_types' => ['card'],
//        ]);
//    }

    public function createHallSubscriptionIntent($hall, $user)
    {
        $setting = AppSetting::first();

        if (!$setting || !$setting->subscription_value) {
            return response()->json(['error' => 'Subscription price not set'], 400);
        }

        $price = $setting->subscription_value;


        if ($price < 0.50) {
            throw new \Exception('the amount is low');
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        return PaymentIntent::create([
            'amount' => $price * 100, // cents
            'currency' => 'usd',
            'payment_method_types' => ['card'],
            'metadata' => [
                'hall_id' => $hall->id,
                'user_id' => $user->id,
                'type' => 'subscription_renewal'
            ]
        ]);
    }


    public function listPaymentIntents($limit = 10)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        return PaymentIntent::all([
            'limit' => $limit,
        ]);
    }
}
