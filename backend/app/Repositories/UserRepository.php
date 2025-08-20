<?php
namespace App\Repositories;

use App\Models\User;

class UserRepository{

    // Ambil data user menggunakan pagination
    public function getAllUsers($perPage = 10)
    {
        return User::paginate($perPage);
    }

    // Ambil data satu user berdasarkan ID
    public function getUserById($id)
    {
        return User::find($id);
    }

    // Membuat user baru
    public function create(array $data)
    {
        return User::create($data);
    }

    // Update data user by id
    public function update($id, array $data)
    {
        $user = User::findOrFail($id);
        $user->update($data);
        return $user;
    }

    // Hapus user by id
    public function delete($id)
    {
        $user = User::findOrFail($id);
        return $user->delete();
    }

}
