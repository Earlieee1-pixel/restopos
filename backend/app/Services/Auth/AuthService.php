<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Ari ang lohika para sa pag-authenticate
class AuthService
{
    // I-check kung husto ang credentials, ibalik ang token
    public function login(array $credentials): array|string|false
    {
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return false;
        }

        // I-check kung aktibo pa ang account
        if (!$user->is_active) {
            return 'inactive';
        }

        // Tangtangon ang daan nga token, buhatan ug bag-o
        $user->tokens()->delete();
        $token = $user->createToken('pos-token')->plainTextToken;

        return [
            'user'  => $user,
            'token' => $token,
        ];
    }

    // Tangtangon ang token sa pag-logout
    public function logout(): void
    {
        auth()->user()->currentAccessToken()->delete();
    }
}
