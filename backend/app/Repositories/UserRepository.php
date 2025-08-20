<?php
namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    // Cari berdasarkan email atau username
    public function findByLogin($login)
    {
        return User::where('email', $login)
            ->orWhere('username', $login)
            ->first();
    }
}
