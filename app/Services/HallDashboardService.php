<?php

namespace App\Services;

use App\Models\hall;
use App\Models\Booking;
use App\Models\Review;
use App\Models\hall_employee;

class HallDashboardService
{

    public function getStatistics($hallId)
    {
        $bookingCount = Booking::where('hall_id', $hallId)->count();
        $averageRating = Review::where('hall_id', $hallId)->avg('rating');
        $employeeCount = hall_employee::where('hall_id', $hallId)->count();

        $totalRevenue = Booking::where('hall_id', $hallId)
            ->whereHas('payment') // تأكد أن الحجز إله دفع
            ->with('payment')
            ->get()
            ->sum(function($booking) {
                return $booking->payment->amount ?? 0;
            });

        return [
            'Confirmed Bookings' => $bookingCount,
            'ratings' => $averageRating,
            'employees' => $employeeCount,
            'revenue' => $totalRevenue
        ];
    }
}
