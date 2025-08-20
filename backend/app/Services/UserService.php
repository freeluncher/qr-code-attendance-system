<?php
namespace App\Services;

use App\Models\User;

class UserService
{
    public function createUser(array $data): User
    {
        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = bcrypt($data['password']);
        $user->save();

        return $user;
    }

    public function findUserById(int $id): ?User
    {
        return User::find($id);
    }

    public function updateUser(int $id, array $data): ?User
    {
        $user = User::find($id);
        if ($user) {
            $user->update($data);
            return $user;
        }
        return null;
    }
}
