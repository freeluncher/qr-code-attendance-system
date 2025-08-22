<?php
namespace App\Services;

use App\Repositories\UserRepository;
use App\Repositories\AuthRepository;
use Illuminate\Support\Facades\Hash;

class UserService
{
    protected $userRepository;
    protected $authRepository;

    public function __construct(UserRepository $userRepository, AuthRepository $authRepository)
    {
        $this->userRepository = $userRepository;
        $this->authRepository = $authRepository;
    }

   // Proses Ambil data user dengan pagination
   public function getAllUsers($perPage = 10, $role = null)
   {
       return $this->userRepository->getAllUsers($perPage, $role);
   }

   // Proses Ambil satu data user by id
   public function getUserById($id)
   {
       return $this->userRepository->getUserById($id);
   }

   // Proses buat user baru
   public function createUser(array $data)
   {
        // Generate username dari nama
        $username = $this->authRepository->generateUsername($data['name']);

        // Build user data
        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'] ?? 'satpam', // Default role satpam
            'username' => $username,
            'password' => Hash::make($data['password']),
            'photo' => $data['photo'] ?? null,
        ];

        // Hash password jika ada
        if (isset($data['password']) && !empty($data['password'])) {
            $userData['password'] = Hash::make($data['password']);
        }

        return $this->userRepository->create($userData);
   }

   // Proses update user by id
   public function updateUser($id, array $data)
   {
        // Debug log data yang diterima
        \Log::info('UserService updateUser data:', $data);
        // Jika password kosong, jangan ubah password
        if (empty($data['password'])) {
            unset($data['password']);
        }
        // Update
        return $this->userRepository->update($id, $data);
   }

   // Proses hapus user by id
   public function deleteUser($id)
   {
       return $this->userRepository->delete($id);
   }
}
