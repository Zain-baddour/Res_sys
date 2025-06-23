<?php

namespace App\Http\Controllers;

use App\Services\UserProfileService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserProfileService $userService)
    {
        $this->userService = $userService;
    }

    public function getProfile()
    {
        $user = auth()->user();

        $profile = $this->userService->getProfile($user);

        return response()->json([
            'status' => 'success',
            'data' => $profile
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        // تحقق من صحة البيانات بس للحقول يلي اجت
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'number' => 'sometimes|required|string|max:20',
            'photo' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // حدث البيانات
        $updatedUser = $this->userService->updateProfile($user, $validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated successfully',
            'data' => $updatedUser
        ]);
    }
}
