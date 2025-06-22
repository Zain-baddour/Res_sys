<?php

namespace App\Services;

use App\Models\User;

class UserProfileService
{
    public function getProfile(User $user)
    {
        // لو حابب لاحقاً تعالج البيانات أو تضيف عمليات معينة
        return $user->makeHidden(['password', 'api_token']);
    }


    public function updateProfile(User $user, array $data)
    {
        // بس حدث الحقول يلي بدنا ياها
        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'location' => $data['location'],
            'number' => $data['number'],
        ]);

        return $user;
    }
}
