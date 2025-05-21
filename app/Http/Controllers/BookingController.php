<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BookingService;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function create(Request $request)
    {
        $data = $request->validate([
            'hall_id' => 'required|exists:halls,id',
            'event_date' => 'required|date',
            'from' => 'required|date_format:H:i',
            'to' => 'required|date_format:H:i|after:from',
            'guest_count' => 'required|integer|min:1',
            'event_type' => 'required|string',

            // خدمات الأفراح
            'buffet_service.from_hall' => 'nullable|boolean',
            'buffet_service.details' => 'nullable|array',
            'buffet_service.details.*' => 'string',

            'hospitality_services.from_hall' => 'nullable|boolean',
            'hospitality_services.details' => 'nullable|array',
            'hospitality_services.details.*' => 'string',

            'performance_service.from_hall' => 'nullable|boolean',

            'car_service.from_hall' => 'nullable|boolean',

            'decoration_service.from_hall' => 'nullable|boolean',

            'photographer_service.from_hall' => 'nullable|boolean',

            'protection_service.from_hall' => 'nullable|boolean',

            'promo_service.from_hall' => 'nullable|boolean',

            'songs' => 'nullable|array',
            'songs.*.person_name' => 'required|string',
            'songs.*.song_name' => 'required|string',

            'additional_notes' => 'nullable|string',

            // خدمات الأتراح
            'reader_service.from_hall' => 'nullable|boolean',

            'condolence_photographer_service.from_hall' => 'nullable|boolean',

            'condolence_hospitality_services.from_hall' => 'nullable|boolean',
            'condolence_hospitality_services.details' => 'nullable|array',
            'condolence_hospitality_services.details.*' => 'string',

            'condolence_additional_notes' => 'nullable|string',
        ]);

        $book = $this->bookingService->createBooking($data);
        return response()->json($book->load(['services','songs']));
    }

    public function getHallBookings() {
        $bookings = $this->bookingService->getHallBookings();
        if (is_null($bookings)) {
            return response()->json(['message' => 'this user is not an employee in any hall'], 404);
        }
        return response()->json($bookings);
    }

    public function getHallConfirmedBookings() {
        $bookings = $this->bookingService->getHallConfirmedBookings();
        if (is_null($bookings)) {
            return response()->json(['message' => 'this user is not an employee in any hall'], 404);
        }
        return response()->json($bookings);
    }

    public function confirm($bookingId)
    {
        return response()->json($this->bookingService->confirmBooking($bookingId));
    }

    public function update(Request $request, $bookingId)
    {
        $data = $request->validate([
            'event_date' => 'required|date',
            'guest_count' => 'required|integer|min:1',
            'event_type' => 'required|string',
        ]);

        return response()->json($this->bookingService->updateBooking($bookingId, $data));
    }

    public function delete(Request $request, $bookingId)
    {
        $confirmPenalty = $request->input('confirm_penalty', false);
        return response()->json($this->bookingService->deleteBooking($bookingId, $confirmPenalty));
    }
}
