<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AdmineService;

class AdminController extends Controller
{
    protected $adminService;

    public function __construct(AdmineService $admineService){
        $this->adminService = $admineService;
    }

    public function getPendingHalls()
    {
        $halls = $this->adminService->getPendingHalls();
        return response()->json($halls);
    }

    public function updateHallStatus(Request $request , $id)
    {
        $request->validate([
            'status' => 'sometimes|string|in:approved,rejected',
        ]);

        $hall = $this->adminService->updateHallStatus($id, $request->status);

        return response()->json($hall);
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'monthly_subscription_price' => 'required|numeric|min:0',
            'trial_duration_days' => 'required|integer|min:0',
        ]);

        $this->adminService->updateSettings($request->only([
            'monthly_subscription_price',
            'trial_duration_days'
        ]));

        return response()->json(['message' => 'تم التحديث بنجاح']);
    }

    public function showSettings()
    {
        $settings = $this->adminService->getSettings();
        return response()->json($settings);
    }



}
