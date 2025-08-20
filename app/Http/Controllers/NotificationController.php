<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;
use App\Models\DeviceToken;
use App\Services\FirebaseNotificationService;

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

    public function send(Request $request, FirebaseNotificationService $fcm)
    {
        $request->validate([
            'device_token' => 'required|string',
            'title' => 'required|string',
            'body' => 'required|string',
        ]);

        $response = $fcm->sendN(
            $request->device_token,
            $request->title,
            $request->body
        );

        return response()->json($response);
    }

}
