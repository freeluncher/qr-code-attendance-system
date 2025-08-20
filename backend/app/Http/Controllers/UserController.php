<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UserService;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    // Ambil semua user dengan pagination
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $users = $this->userService->getAllUsers($perPage);
        return response()->json($users);
    }

    // Ambil detail satu user
    public function show($id)
    {
        $user = $this->userService->getUserById($id);
        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }
        return response()->json($user);
    }

    // Buat user baru
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|in:admin,satpam',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'photo' => 'nullable|string',
        ]);
        $user = $this->userService->createUser($data);

        return response()->json([
            'message' => 'User berhasil dibuat.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'username' => $user->username,
            ],
        ], 201);
    }

    // Update user
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'role' => 'sometimes|required|in:admin,satpam',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'sometimes|nullable|string|min:8|confirmed',
            'username' => 'sometimes|required|string|max:255|unique:users,username,' . $id,
            'photo' => 'nullable|string',
        ]);
        $user = $this->userService->updateUser($id, (array) $data);
        return response()->json($user);
    }

    // Hapus user
    public function destroy($id)
    {
        $deleted = $this->userService->deleteUser($id);
        if ($deleted) {
            return response()->json(['message' => 'User berhasil dihapus']);
        }
        return response()->json(['message' => 'User gagal dihapus'], 500);
    }
}
