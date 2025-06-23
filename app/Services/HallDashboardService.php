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
        $now = now();
        $currentMonth = $now->month;
        $lastMonth = $now->subMonth()->month;

        // عدد الحجوزات للشهر الحالي
        $currentMonthBookings = Booking::where('hall_id', $hallId)
            ->whereMonth('created_at', $currentMonth)
            ->count();

        // عدد الحجوزات للشهر الماضي
        $lastMonthBookings = Booking::where('hall_id', $hallId)
            ->whereMonth('created_at', $lastMonth)
            ->count();

        // حساب نسبة الزيادة أو النقصان
        if ($lastMonthBookings > 0) {
            $bookingChange = (($currentMonthBookings - $lastMonthBookings) / $lastMonthBookings) * 100;
        } else {
            $bookingChange = $currentMonthBookings > 0 ? 100 : 0; // إذا ما في حجوزات الشهر الماضي
        }

        // العائدات لكل شهر (آخر 6 أشهر مثلاً)
        $monthlyRevenues = Booking::where('hall_id', $hallId)
            ->whereHas('payment')
            ->with('payment')
            ->get()
            ->groupBy(function($booking) {
                return $booking->created_at->format('Y-m');
            })
            ->map(function($group) {
                return $group->sum(function($booking) {
                    return $booking->payment->amount ?? 0;
                });
            });

        // متوسط التقييمات
        $averageRating = Review::where('hall_id', $hallId)->avg('rating');

        return [
            'monthly_bookings' => $currentMonthBookings,
            'booking_change_percentage' => round($bookingChange, 2),
            'monthly_revenues' => $monthlyRevenues,
            'average_rating' => round($averageRating, 1)
        ];
    }
}
