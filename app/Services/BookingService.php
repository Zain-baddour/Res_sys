<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Hall;
use App\Models\payments;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewBookingNotification;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function createBooking($data)
    {
        return DB::transaction(function () use ($data) {

            // التحقق من عدم وجود حجز بنفس التاريخ والوقت للصالة
            $existingBooking = Booking::where('hall_id', $data['hall_id'])
                ->where('event_date', $data['event_date'])
                ->first();

            if ($existingBooking) {
                throw ValidationException::withMessages([
                    'event_date' => 'تم حجز هذه الصالة في هذا الوقت مسبقاً.'
                ]);
            }

            $booking = Booking::create([
                'user_id' => Auth::id(),
                'hall_id' => $data['hall_id'],
                'event_date' => $data['event_date'],
                'guest_count' => $data['guest_count'],
                'event_type' => $data['event_type'],
                'status' => 'unconfirmed' ]);

            payments::create([
                'user_id' => Auth::id(),
                'booking_id' => $booking->id,
                'amount' => 100.00, // المبلغ المبدئي للحجز
                'status' => 'pending'
            ]);

            // إرسال إشعار إلى صاحب الصالة والموظفين

            $hall = Hall::with('owner', 'employees')->find($data['hall_id']);
            $recipients = collect([$hall->owner])->merge($hall->employees);

            foreach ($recipients as $recipient) {
                $recipient->notify(new NewBookingNotification($booking));
            }

            return $booking;
        });
    }

    public function confirmBooking($bookingId)
    {
        $booking = Booking::where('id', $bookingId)->where('user_id', Auth::id())->firstOrFail();
        if (!$booking->payment_confirmed) {
            return response()->json('يجب دفع المبلغ إلكترونياً أو في الصالة قبل تأكيد الحجز.');
        }

        $booking->status = 'confirmed';
        $booking->save();
        return $booking;
    }

    public function updateBooking($bookingId, $data)
    {
        $booking = Booking::where('id', $bookingId)->where('user_id', Auth::id())->firstOrFail();

        if ($booking->status == 'confirmed') {
            return response()->json('لا يمكنك تعديل الحجز بعد تأكيده يمكنك التواصل مع الصالة وحذفه ثم انشاء حجز جديد بعد موافقة الصالة لتجنب خسارة مالك.');
        }

        $bookingDate = Carbon::parse($booking->event_date);
        if (now()->diffInDays($bookingDate,false) < 2) {
            return response()->json('لا يمكنك تعديل الحجز قبل يومين من الموعد.');
        }

        $booking->update($data);
        return $booking;
    }


    public function deleteBooking($bookingId, $confirmPenalty)
    {
        $booking = Booking::where('id', $bookingId)->where('user_id', Auth::id())->firstOrFail();

        if ($booking->status == 'confirmed') {
            return response()->json('لا يمكنك حذف الحجز بعد تأكيده يمكنك التواصل مع الصالة لتجنب خسارة مالك.');
        }
        $bookingDate = Carbon::parse($booking->event_date);
        $daysBeforeEvent = now()->diffInDays($bookingDate);

        if ($daysBeforeEvent < 2) {
            if (!$confirmPenalty) {
                return response()->json([
                    'message' => 'إلغاء الحجز قبل 2 يوم يتطلب دفع غرامة مالية. هل ترغب في المتابعة؟',
                    'confirm_penalty_required' => true
                ], 400);
            }

            // إنشاء سجل الدفع إذا وافق المستخدم
            $payment = payments::create([
                'user_id' => Auth::id(),
                'booking_id' => $bookingId,
                'amount' => 50.00, // قيمة الغرامة، يمكن تعديلها
                'status' => 'pending',
            ]);

            return response()->json([
                'message' => 'تم تسجيل طلب الحذف، في انتظار تأكيد الدفع.',
                'payment_id' => $payment->id
            ]);
        }

        // حذف الحجز مباشرة بدون غرامة
        $booking->delete();
        return response()->json(['message' => 'تم حذف الحجز بنجاح.']);
    }
}
