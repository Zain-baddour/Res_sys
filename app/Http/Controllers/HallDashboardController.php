<?php

namespace App\Http\Controllers;

use App\Models\hall;
use App\Services\HallDashboardService;
use Illuminate\Http\Request;

class HallDashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(HallDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function getStatistics(Request $request)
    {
        $userId =  auth()->user()->id;
        $hallId = hall::where('owner_id',$userId)->value('id'); // أو حسب نظامك لو في علاقة
        $stats = $this->dashboardService->getStatistics($hallId);

        return response()->json([
            'status' => 'success',
            'data' => $stats
        ]);
    }
}
