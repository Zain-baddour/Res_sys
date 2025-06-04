<?php

namespace App\Http\Controllers;

use App\Models\User;
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

    public function updateOfficeSettings(Request $request)
    {
        $request->validate([
            'subscription_duration_days' => 'required|integer|min:0',
            'subscription_value' => 'required|numeric|min:0',
            'currency' => 'required|string',
        ]);

        $this->adminService->updateOfficeSettings($request->only([
            'subscription_duration_days',
            'subscription_value',
            'currency'
        ]));

        return response()->json(['message' => 'تم التحديث بنجاح']);
    }

    public function showOfficeSettings()
    {
        $settings = $this->adminService->getOfficeSettings();
        return response()->json($settings);
    }

    public function getAllUsers() {
        return response()->json($this->adminService->getAllUsers());
    }

    public function getUserById($id) {
        return response()->json($this->adminService->getUserById($id));
    }


    public function deleteUser($id)
    {
        $this->adminService->deleteUser($id);
        return response()->json(['message' => 'User deleted successfully']);
    }

    public function blockUser($id)
    {
        $this->adminService->blockUser($id);
        return response()->json(['message' => 'User blocked successfully']);
    }

    public function unblockUser($id)
    {
        $this->adminService->unblockUser($id);
        return response()->json(['message' => 'User unblocked successfully']);
    }

    public function blockedUsers()
    {
        $blockedUsers = $this->adminService->getBlockedUsers();
        return response()->json($blockedUsers);
    }
}
