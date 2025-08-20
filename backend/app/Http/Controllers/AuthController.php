<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    // Login endpoint
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required|string', //email atau username
            'password' => 'required|string',
        ]);

        try {
            $result = $this->authService->login($credentials['login'], $credentials['password']);
            $user = $result['user'];
            $token = $result['token'];

            return response()->json([
                'message' => 'Login berhasil.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'role' => $user->role,
                    'email' => $user->email,
                    'username' => $user->username,
                ],
                'token' => $token,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Login gagal. Email/username atau password salah.'
            ], 401);
        }
    }

    // Logout Endpoint
    public function logout(Request $request)
    {
        $user = $request->user();
        $this->authService->logout($user);

        return response()->json([
            'message' => 'Logout berhasil.'
        ]);
    }

}
