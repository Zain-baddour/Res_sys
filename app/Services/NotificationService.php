<?php

namespace App\Services;

use App\Models\Notifications;
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

    public function getNonReadN($userId) {
        $user = \App\Models\User::find($userId);

        if (!$user) {
            return null; // أو throw exception لو بتحب
        }

        //non read notifications number
        return $user->notifications()->where('is_read',false)->count();

    }

    public function markAsRead($NId) {
        $note = Notifications::findOrFail($NId);

        if ($note->is_read) {
            return "already read";
        }else {
            $note->is_read = true;
            $note->save();
            return "turned read";
        }

    }
}
