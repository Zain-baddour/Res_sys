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


    public function testStripeCurl()
    {
        $response = Http::withBasicAuth(config('services.stripe.secret'), '')
            ->asForm()
            ->post('https://api.stripe.com/v1/payment_intents', [
                'amount' => 1000, // 10.00 USD
                'currency' => 'usd',
            ]);

        return $response->json();
    }






}
