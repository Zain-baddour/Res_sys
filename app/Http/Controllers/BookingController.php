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
            'performance_service.details' => 'nullable|array',
            'performance_service.details.*' => 'string',

            'car_service.from_hall' => 'nullable|boolean',
            'car_service.details' => 'nullable|array',
            'car_service.details.*' => 'string',

            'decoration_service.from_hall' => 'nullable|boolean',
            'decoration_service.details' => 'nullable|array',
            'decoration_service.details.*' => 'string',

            'photographer_service.from_hall' => 'nullable|boolean',
            'photographer_service.details' => 'nullable|array',
            'photographer_service.details.*' => 'string',

            'protection_service.from_hall' => 'nullable|boolean',
            'protection_service.details' => 'nullable|array',
            'protection_service.details.*' => 'string',

            'promo_service.from_hall' => 'nullable|boolean',
            'promo_service.details' => 'nullable|array',
            'promo_service.details.*' => 'string',

            'songs' => 'nullable|array',
            'songs.*.person_name' => 'required|string',
            'songs.*.song_name' => 'required|string',

            'additional_notes' => 'nullable|string',

            // خدمات الأتراح
            'reader_service.from_hall' => 'nullable|boolean',
            'reader_service.details' => 'nullable|array',
            'reader_service.details.*' => 'string',

            'condolence_photographer_service.from_hall' => 'nullable|boolean',
            'condolence_photographer_service.details' => 'nullable|array',
            'condolence_photographer_service.details.*' => 'string',

            'condolence_hospitality_services.from_hall' => 'nullable|boolean',
            'condolence_hospitality_services.details' => 'nullable|array',
            'condolence_hospitality_services.details.*' => 'string',

            'condolence_additional_notes' => 'nullable|string',
        ]);

        return response()->json($this->bookingService->createBooking($data));
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
