<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Nette\Utils\Strings;
use Spatie\Permission\Models\Role;


class UserService
{
    public function register(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'location' => $data['location'],
            'number' => $data['number'],
            'role' => $data['role'],
        ]);

        //photo
        if (isset($data['photo'])) {
            $photoName = uniqid() . '_photo_.' . $data['photo']->getClientOriginalExtension();
            $data['photo']->move(public_path(), $photoName);
            $user->photo = $photoName;

//            $imagePath = $data['photo']->store('photo' , 'public');
//            $user->photo = $imagePath;
        }
        //id photo
        if (isset($data['id_image'])) {
            $photoName = uniqid() . '_id_.' . $data['id_image']->getClientOriginalExtension();
            $data['id_image']->move(public_path(), $photoName);
            $user->id_image = $photoName;
        }

        // Assign role
        $user->assignRole($data['role']);

        // Generate and save token
        $token = $user->createToken('auth_token')->plainTextToken;
        $user->forceFill(['api_token' => $token])->save();

        $user->save();
        return $user;
    }

    public function login(array $credentials): string
    {
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Revoke previous tokens
        $user->tokens()->delete();

        // Generate new token
        $token = $user->createToken('auth_token')->plainTextToken;
        $user->forceFill(['api_token' => $token])->save();

        return $token;
    }

    public function logout(User $user): void
    {
        // Revoke all tokens and clear the API token field
        $user->tokens()->delete();
        $user->forceFill(['api_token' => null])->save();
    }
}
