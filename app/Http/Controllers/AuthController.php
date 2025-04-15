<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use SebastianBergmann\Diff\Exception;
use Throwable;


class AuthController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService){
        $this->userService = $userService;
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'location' => 'required|string|max:255',
            'number' => 'required|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'id_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'role' => 'nullable|string',
        ]);

        $user = $this->userService->register($validated);

        return response()->json([
            'message' => 'User registered successfully.',
            'user' => $user,

        ]);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $token = $this->userService->login($validated);

        return response()->json([
            'message' => 'Login successful.',
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $this->userService->logout($request->user());

        return response()->json(['message' => 'Logged out successfully.']);
    }

}
