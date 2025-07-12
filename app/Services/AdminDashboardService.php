<?php
namespace App\Services;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Booking;
use App\Models\hall;
use App\Models\Office;   // غيّر الاسم إذا نموذجك مختلف

class AdminDashboardService
{
    /* -----------------------------------------------------------------
     |  Dashboard #1 – إحصاءات عامة
     * ----------------------------------------------------------------*/
    public function getGeneralStatistics(): array
    {
        $now               = Carbon::now();
        $startThisMonth    = $now->copy()->startOfMonth();
        $startLastMonth    = $now->copy()->subMonth()->startOfMonth();
        $endLastMonth      = $startLastMonth->copy()->endOfMonth();

        /* مجموع المستخدمين (أو “Number of people”) */
        $userCount = User::count();

        /* عدد الحجوزات الشهرية */
        $currentBookings  = Booking::whereBetween('created_at', [$startThisMonth, $now])->count();
        $lastBookings     = Booking::whereBetween('created_at', [$startLastMonth,  $endLastMonth])->count();
        $bookPct          = $this->percentChange($currentBookings, $lastBookings);

        /* الإيرادات الشهرية */
        $currentRevenue = $this->sumRevenueBetween($startThisMonth, $now);
        $lastRevenue    = $this->sumRevenueBetween($startLastMonth, $endLastMonth);
        $revPct         = $this->percentChange($currentRevenue, $lastRevenue);

        /* حجوزات آخر 12 شهر للرسم البياني */
        $bookingsByMonth = Booking::selectRaw('DATE_FORMAT(created_at,"%Y-%m") as ym, COUNT(*) as total')
            ->where('created_at', '>=', $now->copy()->subMonths(11)->startOfMonth())
            ->groupBy('ym')
            ->orderBy('ym')
            ->pluck('total', 'ym');                     // يُرجع [ '2025-01' => 40, … ]

        return [
            'user_count'               => $userCount,

            'current_bookings'         => $currentBookings,
            'bookings_change_pct'      => $bookPct,     // موجب أو سالب

            'current_revenue'          => $currentRevenue,
            'revenue_change_pct'       => $revPct,

            'bookings_by_month'        => $bookingsByMonth,
            'average_monthly_bookings' => round($bookingsByMonth->avg() ?: 0),
            'top_month'                => $bookingsByMonth->sortDesc()->keys()->first(),
            'top_month_bookings'       => $bookingsByMonth->max() ?: 0,
        ];
    }

    /* -----------------------------------------------------------------
     |  Dashboard #2 – إحصاءات القاعات
     * ----------------------------------------------------------------*/
    public function getLoungeStatistics(): array
    {
        return $this->genericSectionStats(hall::class);
    }

    /* -----------------------------------------------------------------
     |  Dashboard #2 – إحصاءات مكاتب السيارات
     * ----------------------------------------------------------------*/
    public function getOfficeStatistics(): array
    {
        return $this->genericSectionStats(Office::class);
    }

    /** يحسب التغيير بالنسبة المئوية */
    private function percentChange($current, $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100.0 : 0.0;
        }
        return round((($current - $previous) / $previous) * 100, 2);
    }

    /** مجموع الإيراد في فترة زمنية */
    private function sumRevenueBetween($from, $to): float
    {
        return Booking::whereBetween('created_at', [$from, $to])
            ->whereHas('payment')
            ->with('payment')
            ->get()
            ->sum(fn($b) => $b->payment->amount ?? 0);
    }

    /** تجميع إحصاءات الأقسام (قاعات / مكاتب) */
    private function genericSectionStats($modelClass): array
    {
        $now      = Carbon::now();
        $last30   = $now->copy()->subDays(30);
        $prev30   = $last30->copy()->subDays(30);
        $requestsNow   = $modelClass::where('status', 'pending')
            ->where('created_at', '>=', $last30)->count();
        $requestsPrev  = $modelClass::where('status', 'pending')
            ->whereBetween('created_at', [$prev30, $last30])->count();

        $acceptedNow   = $modelClass::where('status', 'approved')
            ->where('created_at', '>=', $last30)->count();
        $acceptedPrev  = $modelClass::where('status', 'approved')
            ->whereBetween('created_at', [$prev30, $last30])->count();

        $activeNow     = $modelClass::where('status', 'approved')->count();
        $activePrev    = $modelClass::where('status', 'approved')
            ->where('updated_at', '<', $last30)->count();

        return [
            'requests_last_30'  => $requestsNow,
            'requests_change'   => $this->percentChange($requestsNow,  $requestsPrev),

            'accepted_last_30'  => $acceptedNow,
            'accepted_change'   => $this->percentChange($acceptedNow, $acceptedPrev),

            'active_total'      => $activeNow,
            'active_change'     => $this->percentChange($activeNow,  $activePrev),
        ];
    }
}
