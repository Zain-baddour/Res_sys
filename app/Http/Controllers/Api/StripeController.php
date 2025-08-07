<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\hall;
use Illuminate\Http\Request;
use App\Services\StripeService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class StripeController extends Controller
{
    protected $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    public function createSubscriptionPayment(Request $request)
    {
        $user = auth()->user();

        $hall = hall::where('owner_id', $user->id)->first();
        if (!$hall) {
            return response()->json(['error' => 'You are not associated with any hall'], 403);
        }

        try {
            $paymentIntent = $this->stripeService->createHallSubscriptionIntent($hall, $user);

            return response()->json([
                'client_secret' => $paymentIntent->client_secret,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()],400);
        }

    }

    public function listPayments(Request $request)
    {
        $limit = $request->get('limit', 10);
        $payments = $this->stripeService->listPaymentIntents($limit);

        return response()->json($payments);
    }

    public function confirmPayment(Request $request)
    {
        $request->validate([
            'payment_intent_id' => 'required|string',
        ]);

        try {
            $hall = $this->stripeService->confirmAndRecord($request->payment_intent_id);

            return response()->json([
                'message' => 'Payment confirmed and subscription updated ✅',
                'hall'    => $hall,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function onboard(Request $request)
    {
        $user = auth()->user();

        // إذا ماعندو حساب Stripe
        if (!$user->stripe_account_id) {
            $this->stripeService->createExpressAccount($user);
        }

        // حط روابط مؤقتة إلى حين مايوصلك رد من الفرونت
        $refreshUrl = 'http://localhost:8000/stripe/refresh';
        $returnUrl = 'http://localhost:8000/stripe/return';

        $link = $this->stripeService->generateAccountLink($user->stripe_account_id, $refreshUrl, $returnUrl);

        return response()->json(['url' => $link->url]);
    }

    public function verifyAccount(Request $request)
    {
        $user = Auth::user(); // أو حسب حالتك، مثلاً من token أو session

        $result = $this->stripeService->verifyAccount($user);

        return response()->json($result);
    }

    public function payForHall($bookingId)
    {
        $booking = Booking::findOrFail($bookingId);
        $hall = $booking->hall;
        $owner = $hall->owner;



        // بدنا نتأكد إنو عندو Stripe Account ID
        if (!$owner->stripe_account_id) {
            return response()->json(['error' => 'Hall owner does not have a Stripe account connected.'], 400);
        }


        $paymentIntent = (new StripeService())->createPaymentIntentForHall(
            $booking->payment->amount,
            $owner->stripe_account_id
        );

        return response()->json([
            'clientSecret' => $paymentIntent->client_secret,
        ]);
    }




}
