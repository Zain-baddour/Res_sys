<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\hall;
use Illuminate\Http\Request;
use App\Services\StripeService;

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
        $limit = $request->get('limit', 10); // ممكن يمرر عدد العمليات
        $payments = $this->stripeService->listPaymentIntents($limit);

        return response()->json($payments);
    }



//    public function createPaymentIntent(Request $request)
//    {
//        $request->validate([
//            'amount' => 'required|numeric|min:1',
//        ]);
//
//        $paymentIntent = $this->stripeService->createPaymentIntent($request->amount);
//
//        return response()->json([
//            'client_secret' => $paymentIntent->client_secret
//        ]);
//    }



}
