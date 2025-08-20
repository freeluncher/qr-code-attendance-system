<?php
namespace App\Services;

use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;

class UserService
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

   // Proses Ambil data user dengan pagination
   public function getAllUsers($perPage = 10)
   {
       return $this->userRepository->getAllUsers($perPage);
   }

   // Proses Ambil satu data user by id
   public function getUserById($id)
   {
       return $this->userRepository->getUserById($id);
   }

   // Proses buat user baru
   public function createUser(array $data)
   {
        //pastikan password di-hash dan username unik
        $data['password'] = Hash::make($data['password']);
        if (empty($data['password'])){
            $data['password'] = Hash::make($data['password']);
        }else {
            unset($data['password']); //Jangan ubah password jika kosong
        }
       return $this->userRepository->create($data);
   }

   // Proses update user by id
   public function updateUser($id, array $data)
   {
        //Jika ada password baru, hash dulu
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']); //Jangan ubah password jika kosong
        }
       return $this->userRepository->update($id, $data);
   }

   // Proses hapus user by id
   public function deleteUser($id)
   {
       return $this->userRepository->delete($id);
   }
}
