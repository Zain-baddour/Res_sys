<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;

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
}
