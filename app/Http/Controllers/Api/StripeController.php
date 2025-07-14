<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\hall;
use Illuminate\Http\Request;
use App\Services\StripeService;
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
            $this->stripeService->confirmAndRecord($request->payment_intent_id);

            return response()->json([
                'message' => 'Payment confirmed and subscription updated ✅',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 422);
        }
    }








}
