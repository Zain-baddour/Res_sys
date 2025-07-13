<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\AdminDashboardService;

class AdminDashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(AdminDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /* Dashboard #1 – الإحصاءات العامة */
    public function general(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data'   => $this->dashboardService->getGeneralStatistics()
        ]);
    }

    /* Dashboard #2 – القاعات */
    public function lounges(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data'   => $this->dashboardService->getLoungeStatistics()
        ]);
    }

    /* Dashboard #2 – مكاتب السيارات */
    public function offices(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data'   => $this->dashboardService->getOfficeStatistics()
        ]);
    }
}
