<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    // Login for admin and satpam (dapat menggunakan email atau username)
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required|string', //email atau username
            'password' => 'required|string',
        ]);

        // Cari user berdasarkan email atau username
        $user = User::where('email', $credentials['login'])
            ->orWhere('username', $credentials['login'])
            ->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Login gagal. Email/username atau password salah.'
            ], 401);
        }

        // Generate Sanctum Token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
                'email' => $user->email,
                'username' => $user->username,
                'photo' => $user->photo,
            ],
            'token' => $token
        ]);
    }

    // Logout User (revoke token)
    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            $user->currentAccessToken()->delete(); // Revoke current token
            return response()->json(['message' => 'Logout berhasil.']);
        }

        return response()->json(['message' => 'Unauthorized'], 401);
    }
}
