<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function confirmPenaltyPayment($paymentId)
    {
        return $this->paymentService->confirmPenaltyPayment($paymentId);
    }

    public function confirmPayment($paymentId)
    {
        return $this->paymentService->confirmPayment($paymentId);
    }

    public function createPayment(Request $request)
    {
        return $this->paymentService->createPayment($request->all());
    }
}
