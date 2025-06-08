<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255',
        ]);

        // Find or create user with the given username
        $user = User::firstOrCreate(
            ['name' => $request->username],
            [
                'email' => $request->username . '@chatapp.local',
                'password' => Hash::make('password'), // Default password for chat users
            ]
        );

        // Create token for API authentication
        $token = $user->createToken('chat-token')->plainTextToken;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'created_at' => $user->created_at,
            ],
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'created_at' => $request->user()->created_at,
            ],
        ]);
    }
}
