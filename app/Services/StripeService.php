<?php

// app/Services/StripeService.php
namespace App\Services;

use App\Models\AppSetting;
use App\Models\Booking;
use App\Models\DeviceToken;
use App\Models\paymentConfirm;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Account;
use Stripe\AccountLink;
use Stripe\Customer;
use Stripe\Subscription;
use Stripe\PaymentIntent;
use App\Models\hall;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\BookingService;


class StripeService
{

    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }


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

    public function confirmAndRecord($paymentIntentId)
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

            if ($hall->status = "expired"){
                $hall->status = "approved";
            }
            $hall->save();
            return $hall;
        });
        $hall = hall::findOrFail($hallId);

        $clientToken = DeviceToken::where('user_id', $hall->owner_id)->pluck('device_token');

        $firebase = new FirebaseNotificationService();

        foreach ($clientToken as $token) {
            $firebase->sendNotification(
                $token,
                "Subscription ReNewed",
                "Your hall {$hall->name} subscription ends at {$hall->subscription_expires_at}"
            );
        }

        return $hall;
    }

    public function createExpressAccount($user)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $account = Account::create([
            'type' => 'express',
            'country' => 'US',
            'email' => $user->email,
            'capabilities' => [
                'card_payments' => ['requested' => true],
                'transfers' => ['requested' => true],
            ],
        ]);

        // حفظ account id
        $user->stripe_account_id = $account->id;
        $user->save();

        return $account;
    }

    public function generateAccountLink($accountId, $refreshUrl, $returnUrl)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        return AccountLink::create([
            'account' => $accountId,
            'refresh_url' => $refreshUrl,
            'return_url' => $returnUrl,
            'type' => 'account_onboarding',
        ]);
    }

    public function verifyAccount(User $user): array
    {
        if (!$user->stripe_account_id) {
            return [
                'status' => false,
                'message' => 'User does not have a connected Stripe account.'
            ];
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        $account = Account::retrieve($user->stripe_account_id);

        $chargesEnabled = $account->charges_enabled;
        $detailsSubmitted = $account->details_submitted;

        if ($chargesEnabled && $detailsSubmitted) {
            $user->update(['is_stripe_verified' => true]);

            return [
                'status' => true,
                'message' => 'Account verified successfully.',
                'account' => [
                    'charges_enabled' => $chargesEnabled,
                    'details_submitted' => $detailsSubmitted
                ]
            ];
        }

        return [
            'status' => false,
            'message' => 'Account is not fully verified.',
            'account' => [
                'charges_enabled' => $chargesEnabled,
                'details_submitted' => $detailsSubmitted
            ]
        ];
    }

    public function createPaymentIntentForHall($booking,$hall)
    {
        Stripe::setApiKey(config('services.stripe.secret'));


        $price = $booking->payment->amount ;

        return PaymentIntent::create([
            'amount' => $price * 100, // cents
            'currency' => 'usd',
            'payment_method_types' => ['card'],
//            'automatic_payment_methods' => ['enabled' => true],
            'metadata' => [
                'hall_id' => $hall->id,
                'booking_id' => $booking->id,
                'type' => 'Booking payment'
            ]
        ]);

//        $paymentIntent = PaymentIntent::create([
//            'amount' => $amount * 100, // بالمراكز (100 = 1 دولار)
//            'currency' => 'usd',
//            'payment_method_types' => ['card'],
//            'application_fee_amount' => 0, // عمولتك أنت إن وجدت
//            'transfer_data' => [
//                'destination' => $connectedAccountId, // حساب صاحب الصالة
//            ],
//        ]);

    }

//    public function handleWebhook($payload, $sigHeader)
//    {
//        $endpointSecret = config('services.stripe.webhook_secret');
//
//        try {
//            $event = \Stripe\Webhook::constructEvent(
//                $payload,
//                $sigHeader,
//                $endpointSecret
//            );
//        } catch (\UnexpectedValueException $e) {
//            // JSON غير صالح
//            Log::error('Invalid payload: ' . $e->getMessage());
//            return response('Invalid payload', 400);
//        } catch (\Stripe\Exception\SignatureVerificationException $e) {
//            // التوقيع غلط
//            Log::error('Invalid signature: ' . $e->getMessage());
//            return response('Invalid signature', 400);
//        }
//
//        // التحقق من نوع الحدث
//        if ($event->type === 'payment_intent.succeeded') {
//            $paymentIntent = $event->data->object;
//
//            // هنا بتحط لوجيك تأكيد الحجز (السطر يلي عندك جاهز)
//            // مثال: $this->confirmBooking($paymentIntent->metadata->booking_id);
//            Log::info('✅ Payment succeeded for booking ID: ' . $paymentIntent->metadata->booking_id);
//
//            $this->confirmBooking($paymentIntent->metadata->booking_id);
//        }
//
//        return response('Webhook handled', 200);
//    }

    public function checkPaymentAndConfirmBooking($paymentIntentId, $bookingId)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            // استرجاع معلومات الدفع
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
            $booking = Booking::findOrFail($bookingId)->with(['hall']);

            // إذا الدفع ناجح
            if ($paymentIntent->status === 'succeeded') {
                // هون منستدعي لوجيك تأكيد الحجز
                $this->confirmBooking($bookingId);

                $clientToken = DeviceToken::where('user_id', $booking->user_id)->pluck('device_token');

                $firebase = new FirebaseNotificationService();

                foreach ($clientToken as $token) {
                    $firebase->sendNotification(
                        $token,
                        "Booking ",
                        "Your Booking for hall {$booking->hall()->name} at {$booking->event_date}"
                    );
                }

                return [
                    'status' => true,
                    'message' => 'Payment succeeded and booking confirmed',
                    'booking' => $booking
                ];
            }

            return [
                'status' => false,
                'message' => 'Payment not completed yet',
                'payment_status' => $paymentIntent->status
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function confirmBooking($bookingId)
    {
        $this->bookingService->confirmBooking($bookingId);
    }
}
