<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;

// Ari ang login ug logout sa user
class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    // Mag-login ang user
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        if ($result === 'inactive') {
            return response()->json(['message' => 'Account is deactivated. Contact your administrator.'], 403);
        }

        if (!$result) {
            return response()->json(['message' => 'Invalid email or password.'], 401);
        }

        return response()->json([
            'user'  => new UserResource($result['user']),
            'token' => $result['token'],
        ]);
    }

    // Mag-logout ang user
    public function logout(): JsonResponse
    {
        $this->authService->logout();
        return response()->json(['message' => 'Logged out successfully.']);
    }

    // Kuha sa imong info — walay sensitive fields
    public function me(): JsonResponse
    {
        return response()->json(new UserResource(auth()->user()));
    }
}
