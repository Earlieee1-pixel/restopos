<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

// Ari ang pag-manage sa mga user account (admin only)
class UserController extends Controller
{
    // Tanan users
    public function index(): JsonResponse
    {
        $users = User::orderBy('name')->get();
        return response()->json(UserResource::collection($users));
    }

    // Bag-ong user account
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', Password::min(8)],
            'role'     => ['required', 'in:cashier,manager,admin'],
        ]);

        $user = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'role'      => $data['role'],
            'is_active' => true,
        ]);

        return response()->json(new UserResource($user), 201);
    }

    // I-update ang user info o role
    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'name'     => ['sometimes', 'string', 'max:255'],
            'email'    => ['sometimes', 'email', 'unique:users,email,' . $id],
            'role'     => ['sometimes', 'in:cashier,manager,admin'],
            'password' => ['sometimes', Password::min(8)],
        ]);

        // I-hash ang password kung gi-update
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);
        return response()->json(new UserResource($user));
    }

    // I-toggle ang is_active sa user
    public function toggleActive(int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        // Dili pwede i-deactivate ang kaugalingon
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'You cannot deactivate your own account.'], 422);
        }

        $user->update(['is_active' => !$user->is_active]);
        return response()->json(new UserResource($user));
    }

    // I-change ang password sa current user
    public function changePassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password'     => ['required', Password::min(8), 'confirmed'],
        ]);

        $user = auth()->user();

        // I-verify ang current password
        if (!Hash::check($data['current_password'], $user->password)) {
            return response()->json(['message' => 'Current password is incorrect.'], 422);
        }

        $user->update(['password' => Hash::make($data['new_password'])]);

        // I-delete ang tanan tokens — i-force logout sa tanan sessions including current
        $user->tokens()->delete();

        return response()->json(['message' => 'Password changed successfully. Please log in again.']);
    }
}
