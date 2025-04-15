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
