<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\DeviceToken;
use App\Models\Hall;
use App\Models\offer;
use App\Models\payments;
use App\Models\Servicetohall;
use App\Models\HallPrice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewBookingNotification;
use Illuminate\Validation\ValidationException;
use App\Services\PaymentService;

class BookingService
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }
    public function createBooking($data)
    {
        return DB::transaction(function () use ($data) {
            $guestCount = $data['guest_count'];
            $hallI = hall::find($data['hall_id']);
            if (!$hallI) {
                return response()->json(['message' => 'no hall found with that id'], 404);

            }
            $guestMax = $hallI->capacity;

            if ($guestMax < $guestCount) {
                return response()->json(['message' => 'the hall cannot take all the guest']);
            }


            $conflictingBooking = Booking::where('hall_id', $data['hall_id'])
                ->whereDate('event_date', $data['event_date'])
                ->where(function ($query) use ($data) {
                    $query->where(function ($q) use ($data) {
                        $q->where('from', '<', $data['to'])
                            ->where('to', '>', $data['from']);
                    });
                })
                ->exists();

            if ($conflictingBooking) {
                return response()->json(['message' => 'there is another reservation at this time'], 422);
            }

            $addNote = "nothing";
            if ($data['additional_notes']) {
                $addNote = $data['additional_notes'];
            }

            $booking = Booking::create([
                'user_id' => Auth::id(),
                'hall_id' => $data['hall_id'],
                'event_date' => $data['event_date'],
                'from' => $data['from'],
                'to' => $data['to'],
                'guest_count' => $data['guest_count'],
                'event_type' => $data['event_type'],
                'additional_notes' => $addNote,
                'status' => 'unconfirmed',
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

            $total_price = 0;
            foreach ($additionalServices as $serviceKey) {
                if (isset($data[$serviceKey])) {
                    $booking->services()->create([
                        'booking_id' => $booking->id,
                        'service_type' => $serviceKey,
                        'from_hall' => $data[$serviceKey]['from_hall'] ?? null,
                        'details' => isset($data[$serviceKey]['details']) ? json_encode($data[$serviceKey]['details']) : null,
                    ]);
                    $service = Servicetohall::where('hall_id', $data['hall_id'])->where('name', $serviceKey)->first();
                    if($service) {
                        $total_price += $service->service_price;
                    }
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

            // اضافة السعر المحدد من الصالة في حال ساعات اة بطاقات حسب عدد الحضور
            $addCost = 0;
            $hallPrice1 = HallPrice::where('hall_id', $data['hall_id'])->first();
            $hallPriceType = $hallPrice1->type;
            if($hallPriceType == 'cards'){
                $priceRow = HallPrice::where('hall_id', $data['hall_id'])
                    ->where('guest_count', '>=', $guestCount)
                    ->orderBy('guest_count', 'asc')
                    ->value('price');
                $addCost = $guestCount * $priceRow;
            }
            elseif ($hallPriceType == 'hours'){
                $fromT = Carbon::createFromTimeString($booking->from);
                $toT = Carbon::createFromTimeString($booking->to);
                $diffH = $fromT->diffInHours($toT);
                $addCost = $diffH * $hallPrice1->price / $hallPrice1->guest_count;
            }

            $total_price += $addCost;

            // تحقق من وجود عرض
            $now = Carbon::now();
            $offer = offer::where('hall_id', $data['hall_id'])
                ->where('start_offer', '<=', $now)
                ->where('end_offer', '>=' , $now)
                ->first();
            if($offer){
                $total_price -= $total_price * $offer->offer_val / 100 ;
            }


            payments::create([
                'user_id' => Auth::id(),
                'booking_id' => $booking->id,
                'amount' => $total_price,
                'status' => 'pending',
            ]);

            // إرسال إشعارات
//            $hall = Hall::with('owner', 'employees')->find($data['hall_id']);
//            $recipients = collect([$hall->owner])->merge($hall->employees);
//            foreach ($recipients as $recipient) {
//                $recipient->notify(new NewBookingNotification($booking));
//            }
            $deviceTokens = DeviceToken::where('user_id', $booking->user_id)->pluck('device_token');
            foreach ($deviceTokens as $token) {
                FirebaseNotificationService::sendNotification(
                    $token,
                    "Booking Stored",
                    "Your booking has been saved successfully. Please confirm by paying online or at the hall in 2 days or else the booking will be deleted."
                );
            }

            $hallN = hall::findOrFail($booking->hall_id);
            // توكنات الأونر
            $ownerTokens = DeviceToken::where('user_id', $hallN->owner_id)->pluck('device_token');

            // توكنات الموظفين
            $staffTokens = DeviceToken::whereIn('user_id', $hallN->employee()->pluck('user_id'))->pluck('device_token');

            // دمج الكل بمصفوفة وحدة
            $allTokens = $ownerTokens->merge($staffTokens);

            foreach ($allTokens as $token) {
                FirebaseNotificationService::sendNotification(
                    $token,
                    "New Booking Request",
                    "A new booking has been made for {$hallN->name} on {$booking->event_date}. Please review it."
                );
            }


            return $booking;
        });

    }

    public function getHallBookings() {
        $user = Auth::user();

        $hallId = $user->hallsAsEmployee()->first()?->id;
        if (!$hallId){
            return null;
        }

        return Booking::where('hall_id', $hallId)
            ->where('status', 'unconfirmed')
            ->with(['services' , 'songs', 'user' , 'payment'])
            ->get();
    }

    public function getHallConfirmedBookings() {
        $user = Auth::user();

        $hallId = $user->hallsAsEmployee()->first()?->id;
        if (!$hallId){
            return null;
        }

        return Booking::where('hall_id', $hallId)
            ->where('status', 'confirmed')
            ->with(['services' , 'songs', 'user' , 'payment'])
            ->get();
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
        $booking = Booking::where('id', $bookingId)->with('hall')->firstOrFail();
        $paymentId = payments::where('booking_id' , $bookingId)->value('id');
        $message = $this->paymentService->confirmPayment($paymentId);
        $booking->refresh()->load('hall');
        if(!$booking->payment_confirmed){
            return response()->json('payment error');
        }
        $booking->status = 'confirmed';
        $booking->save();

        $clientTokens = DeviceToken::where('user_id', $booking->user_id)->pluck('device_token');

        $firebase = new FirebaseNotificationService();

        foreach ($clientTokens as $token) {
            $firebase->sendNotification(
                $token,
                "Booking Confirmed ✅",
                "Your booking for {$booking->hall->name} on {$booking->date} has been confirmed."
            );
        }

        return response()->json(['message' => $message,
            'booking' => $booking]);
    }

    public function updateBooking($bookingId, $data)
    {
        $booking = Booking::where('id', $bookingId)->where('user_id', Auth::id())->firstOrFail();
        $hallU = hall::findOfFail($booking->hall_id);
        if ($booking->status == 'confirmed') {
            return response()->json('Sorry You cannot update a booking after confirmation');
        }

        $bookingDate = Carbon::parse($booking->event_date);
        if (now()->diffInDays($bookingDate,false) < 2) {
            return response()->json('you cannot update a booking in such short notice');
        }
        $data['to'] = $booking->to;
        $data['from'] = $booking->from;

        $conflictingBooking = Booking::where('id', $bookingId)
            ->whereDate('event_date', $data['event_date'])
            ->where(function ($query) use ($data) {
                $query->where(function ($q) use ($data) {
                    $q->where('from', '<', $data['to'])
                        ->where('to', '>', $data['from']);
                });
            })
            ->exists();

        if ($conflictingBooking) {
            return response()->json(['message' => 'there is another reservation at this time'], 422);
        }

        $guestMax = $hallU->capacity;

        if ($guestMax < $data['guest_count']) {
            return response()->json(['message' => 'the hall cannot take all the guest']);
        }

        $booking->update($data);

        $deviceTokens = DeviceToken::where('user_id', $booking->user_id)->pluck('device_token');
        foreach ($deviceTokens as $token) {
            FirebaseNotificationService::sendNotification(
                $token,
                "Booking Updated",
                "Your booking has been updated successfully."
            );
        }

        $hallN = hall::findOrFail($booking->hall_id);
        // توكنات الأونر
        $ownerTokens = DeviceToken::where('user_id', $hallN->owner_id)->pluck('device_token');

        // توكنات الموظفين
        $staffTokens = DeviceToken::whereIn('user_id', $hallN->employee()->pluck('user_id'))->pluck('device_token');

        // دمج الكل بمصفوفة وحدة
        $allTokens = $ownerTokens->merge($staffTokens);

        foreach ($allTokens as $token) {
            FirebaseNotificationService::sendNotification(
                $token,
                "Booking Updated",
                "A booking has been Updated for {$hallN->name} on {$booking->event_date}. Please review it."
            );
        }

        return $booking;
    }


    public function deleteBooking($bookingId, $confirmPenalty)
    {
        $booking = Booking::where('id', $bookingId)->where('user_id', Auth::id())->firstOrFail();

        if ($booking->status == 'confirmed'){
            return response()->json(['message' => 'you cannot delete a booking after confirmation or you will lose your money chick with tha hall first']);
        }

        $bookingDate = Carbon::parse($booking->event_date);
        $daysBeforeEvent = now()->diffInDays($bookingDate);

        if ($daysBeforeEvent < 5) {
            if (!$confirmPenalty) {
                return response()->json([
                    'message' => 'to delete a booking before 5 days it require a fee , would you like to proceed!',
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
