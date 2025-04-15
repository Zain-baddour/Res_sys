<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\payments;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use TheSeer\Tokenizer\Exception;

class PaymentService
{
    public function createPayment($data) {
        return DB::transaction(function () use ($data) {
            $payment = payments::create([
                'user_id' => Auth::id(),
                'booking_id' => $data['booking_id'] ?? null,
                'amount' => $data['amount'],
                'status' => 'pending',
                ]);
            return response()->json(['message' => 'تم إنشاء الدفع بنجاح.', 'payment_id' => $payment->id]);
        });
    }

    public function confirmPayment($paymentId) {
        try {
            $payment = payments::where('id', $paymentId)
                ->where('status', 'pending')
                ->firstOrFail();
            if ($payment) {
                $payment->status = 'completed';
                $payment->save();
            }
            $booking = Booking::where('id', $payment->booking_id)->first();
            if ($booking) {
                $booking->payment_confirmed = true;
                $booking->save();
            }
            return response()->json(['message' => 'تم تأكيد الدفع يمكنك الان تاكيد الحجز.']);
        }
        catch (Exception $e){
            Log::error('error :' . $e->getMessage());
            return response()->json(['error' => 'error'], 500);
        }
    }

    public function confirmPenaltyPayment($paymentId)
    {
        return DB::transaction(function () use ($paymentId) {
            $payment = payments::where('id', $paymentId)
                ->where('status', 'pending')
                ->firstOrFail();

            $payment->update(['status' => 'completed']);
            $booking = Booking::where('id', $payment->booking_id)->first();
            if ($booking) {
                $booking->delete();
            }

            return response()->json(['message' => 'تم تأكيد الدفع والحجز تم حذفه.']);
        });
    }

}
