<?php

// app/Services/StripeService.php
namespace App\Services;

use App\Models\AppSetting;
use App\Models\paymentConfirm;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Subscription;
use Stripe\PaymentIntent;
use App\Models\hall;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StripeService
{


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
//            'automatic_payment_methods' => ['enabled' => true],
            'metadata' => [
                'hall_id' => $hall->id,
                'user_id' => $user->id,
                'type' => 'subscription_renewal'
            ]
        ]);
//        $user = auth()->user(); // أو User::find(id)
//
//        $priceId = 'price_1Rh7imRL6N1AQkjGp3nViS7Q'; // ← السعر من Stripe Dashboard
//
//        try {
//            $subscription = $user->newSubscription('default', $priceId)->create();
//
//            if (
//                $subscription->latest_invoice &&
//                $subscription->latest_invoice->payment_intent &&
//                isset($subscription->latest_invoice->payment_intent->client_secret)
//            ) {
//                return response()->json([
//                    'client_secret' => $subscription->latest_invoice->payment_intent->client_secret
//                ]);
//            } else {
//                return response()->json([
//                    'error' => 'No payment intent was generated'
//                ], 400);
//            }
//        } catch (\Exception $e) {
//            return response()->json([
//                'error' => $e->getMessage()
//            ], 500);
//        }
    }


    public function listPaymentIntents($limit = 10)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        return PaymentIntent::all([
            'limit' => $limit,
        ]);
    }

    public function confirmAndRecord($paymentIntentId): void
    {

        Stripe::setApiKey(config('services.stripe.secret'));
        $intent = PaymentIntent::retrieve($paymentIntentId);

        if ($intent->status !== 'succeeded') {
            throw new \Exception('Payment not completed (status: '.$intent->status.')');
        }

        $hallId = $intent->metadata->hall_id ?? null;
        $userId = $intent->metadata->user_id ?? null;

        if (!$hallId || !$userId) {
            throw new \Exception('Missing metadata (hall_id / user_id).');
        }

        DB::transaction(function () use ($intent, $hallId, $userId) {

            paymentConfirm::firstOrCreate(
                ['payment_intent_id' => $intent->id,],
                [
                    'hall_id'  => $hallId,
                    'user_id'  => $userId,
                    'amount'   => $intent->amount,   // بالسنت
                    'currency' => $intent->currency,
                    'status'   => $intent->status,
                ]
            );

            $hall = hall::findOrFail($hallId);

            $now           = Carbon::now();
            $currentExpiry = $hall->subscription_expires_at;
            $currentExpiry = Carbon::parse($currentExpiry);

            $hall->subscription_expires_at = $currentExpiry && $currentExpiry->isFuture()
                ? $currentExpiry->addMonth()
                : $now->addMonth();

            $hall->save();
        });
    }
}
