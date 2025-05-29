<?php

// app/Services/StripeService.php
namespace App\Services;

use Stripe\Stripe;
use Stripe\PaymentIntent;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createPaymentIntent($amount, $currency = 'usd')
    {
        return PaymentIntent::create([
            'amount' => $amount * 100, // Stripe uses cents
            'currency' => $currency,
            'payment_method_types' => ['card'],
        ]);
    }
}
