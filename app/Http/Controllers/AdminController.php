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
            'subscription_duration_days' => 'required|integer|min:0',
            'subscription_value' => 'required|numeric|min:0',
            'currency' => 'required|string',
        ]);

        $this->adminService->updateSettings($request->only([
            'subscription_duration_days',
            'subscription_value',
            'currency'
        ]));

        return response()->json(['message' => 'تم التحديث بنجاح']);
    }

    public function showSettings()
    {
        $settings = $this->adminService->getSettings();
        return response()->json($settings);
    }

    public function getAllUsers() {
        return response()->json($this->adminService->getAllUsers());
    }

}
