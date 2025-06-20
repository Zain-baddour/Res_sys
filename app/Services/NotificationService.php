<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

class NotificationService
{
    public function getUserNotifications($userId)
    {
        $user = \App\Models\User::find($userId);

        if (!$user) {
            return null; // أو throw exception لو بتحب
        }

        // رجع الإشعارات الأحدث أولاً
        return $user->notifications()->orderBy('created_at', 'desc')->get();
    }
}
