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
        // تحديث الحقول الموجودة فقط
        if (isset($data['name'])) {
            $user->name = $data['name'];
        }

        if (isset($data['email'])) {
            $user->email = $data['email'];
        }


        if (isset($data['number'])) {
            $user->number = $data['number'];
        }

        // معالجة الصورة إن وجدت
        if (isset($data['photo'])) {
            // حذف الصورة القديمة إن وجدت
            if ($user->photo && file_exists(public_path($user->photo))) {
                unlink(public_path($user->photo));
            }

            // تخزين الصورة الجديدة
            $photoName = uniqid() . '_photo_.' . $data['photo']->getClientOriginalExtension();
            $data['photo']->move(public_path(), $photoName);
            $user->photo = $photoName;
        }

        $user->save();

        return $user;
    }
}
