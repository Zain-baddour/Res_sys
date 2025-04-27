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
                'additional_notes' => $data['additional_notes'],
                'status' => 'unconfirmed',
            ]);

            payments::create([
                'user_id' => Auth::id(),
                'booking_id' => $booking->id,
                'amount' => 100.00,
                'status' => 'pending',
            ]);

            // حفظ الخدمات الإضافية
            $additionalServices = [
                'buffet_service',
                'hospitality_services',
                'performance_service',
                'car_service',
                'decoration_service',
                'photographer_service',
                'protection_service',
                'promo_service',
                'reader_service',
                'condolence_photographer_service',
                'condolence_hospitality_services',
            ];

            foreach ($additionalServices as $serviceKey) {
                if (isset($data[$serviceKey])) {
                    $booking->services()->create([
                        'booking_id' => $booking->id,
                        'service_type' => $serviceKey,
                        'from_hall' => $data[$serviceKey]['from_hall'] ?? null,
                        'details' => isset($data[$serviceKey]['details']) ? json_encode($data[$serviceKey]['details']) : null,
                    ]);
                }
            }

            // حفظ الأغاني
            if (isset($data['songs'])) {
                foreach ($data['songs'] as $song) {
                    $booking->songs()->create([
                        'booking_id' => $booking->id,
                        'person_name' => $song['person_name'],
                        'song_name' => $song['song_name'],
                    ]);
                }
            }

            // حفظ الملاحظات الإضافية
            if (isset($data['additional_notes'])) {
                $booking->update(['additional_notes' => $data['additional_notes']]);
            }
            if (isset($data['condolence_additional_notes'])) {
                $booking->update(['condolence_additional_notes' => $data['condolence_additional_notes']]);
            }

            // إرسال إشعارات
            $hall = Hall::with('owner', 'employees')->find($data['hall_id']);
            $recipients = collect([$hall->owner])->merge($hall->employees);
            foreach ($recipients as $recipient) {
                $recipient->notify(new NewBookingNotification($booking));
            }

            return $booking;
        });
    }

    protected function storeServices($booking, $data)
    {
        $services = [];

        if (!empty($data['buffet_enabled'])) {
            $services[] = [
                'service_type' => 'buffet',
                'from_hall' => true,
                'details' => $data['buffet_notes'] ?? null,
            ];
        }

        if (!empty($data['hospitality_services'])) {
            foreach ($data['hospitality_services'] as $item) {
                $services[] = [
                    'service_type' => 'hospitality',
                    'from_hall' => $item['from_hall'] ?? true,
                    'details' => 'hospitality_id:' . $item['id']
                ];
            }
        }

        if (!empty($data['performance_service'])) {
            $services[] = [
                'service_type' => 'performance',
                'from_hall' => $data['performance_service']['from_hall'] ?? true,
                'details' => json_encode($data['performance_service']),
                'price' => $data['performance_service']['price'] ?? null
            ];
        }

        if (!empty($data['car_service'])) {
            $services[] = [
                'service_type' => 'car',
                'from_hall' => $data['car_service']['from_hall'] ?? true,
                'details' => json_encode($data['car_service']),
                'price' => $data['car_service']['price'] ?? null
            ];
        }

        if (!empty($data['decoration_enabled'])) {
            $services[] = [
                'service_type' => 'decoration',
                'from_hall' => true
            ];
        }

        if (!empty($data['photographer'])) {
            $services[] = [
                'service_type' => 'photographer',
                'from_hall' => $data['photographer']['from_hall'] ?? true,
                'details' => json_encode($data['photographer'])
            ];
        }

        if (!empty($data['anti_photography']['enabled'])) {
            $services[] = [
                'service_type' => 'anti_photography',
                'from_hall' => true,
                'details' => json_encode($data['anti_photography']),
                'price' => $data['anti_photography']['price'] ?? null
            ];
        }

        if (!empty($data['promo']['enabled'])) {
            $services[] = [
                'service_type' => 'promo',
                'from_hall' => true,
                'details' => json_encode($data['promo']),
                'price' => $data['promo']['price'] ?? null
            ];
        }
        if (isset($data['reader_from_hall'])) {
            $services[] = [
                'service_type' => 'reader',
                'from_hall' => $data['reader_from_hall']
            ];
        }

        if (isset($data['mourning_photographer_from_hall'])) {
            $services[] = [
                'service_type' => 'mourning_photographer',
                'from_hall' => $data['mourning_photographer_from_hall']
            ];
        }

        if (!empty($data['mourning_hospitality_services'])) {
            foreach ($data['mourning_hospitality_services'] as $item) {
                $services[] = [
                    'service_type' => 'mourning_hospitality',
                    'from_hall' => $item['from_hall'] ?? true,
                    'details' => 'hospitality_id:' . $item['id']
                ];
            }
        }

        foreach ($services as $service) {
            $booking->services()->create($service);
        }
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
