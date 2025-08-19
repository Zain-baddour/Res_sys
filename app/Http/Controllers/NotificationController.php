<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;
use App\Models\DeviceToken;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $userId = $request->user()->id; // جبت الـ id من الـ token

        $notifications = $this->notificationService->getUserNotifications($userId);

        return response()->json([
            'status' => 'success',
            'data' => $notifications
        ]);
    }

    public function saveToken(Request $request)
    {
        $request->validate([
            'device_token' => 'required|string',
        ]);

        DeviceToken::updateOrCreate(
            ['user_id' => auth()->id()],
            ['device_token' => $request->device_token]
        );

        return response()->json(['message' => 'Token saved successfully']);
    }
}
