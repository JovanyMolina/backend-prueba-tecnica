<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class RegisteredUserController extends Controller
{
    public function store(Request $request): JsonResponse
{
    $validated = $request->validate([
        'name' => ['required','string','max:255'],
        'email' => ['required','string','lowercase','email','max:255','unique:users,email'],
        'password' => ['required','confirmed', Password::defaults()],
        'role' => ['sometimes','string', Rule::in(['admin','colaborador'])],
    ]);

    $role = 'colaborador';
    
    if ($token = $request->bearerToken()) {
        try {
            $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
            if ($accessToken && $accessToken->tokenable && $accessToken->tokenable->role === 'admin') {
                $role = $validated['role'] ?? 'colaborador';
            }
        } catch (\Exception $e) {
        }
    }

    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
        'role' => $role,
        'active' => true,
    ]);

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'token' => $token,
        'user'  => $user,
    ], 201);
}
}
