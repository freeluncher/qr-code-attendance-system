<?php
namespace App\Services;

use App\Repositories\AuthRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService {

    protected $authRepository;

    public function __construct(AuthRepository $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    // Proses Login untuk admin dan satpam, email/username, return user & token
    public function login($login, $password)
    {
        $user = $this->authRepository->findByLogin($login);

        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['Email/username atau password salah.'],
            ]);
        }

        // Generate Sanctum Token
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    // Proses Logout (revoke token)
    public function logout($user)
    {
        $user->currentAccessToken()->delete();
    }

    // Proses Register user generate username
    public function register($data)
    {
        // Generate username dari nama
        $username = $this->authRepository->generateUsername($data['name']);

        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'] ?? 'satpam', // Default role user
            'username' => $username,
            'password' => Hash::make($data['password']),
            'photo' => $data['photo'] ?? null,
        ];

        return $this->authRepository->create($userData);
    }

}
