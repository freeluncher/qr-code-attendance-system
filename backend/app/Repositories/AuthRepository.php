<?php
namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Str;

class AuthRepository
{
    // Cari berdasarkan email atau username
    public function findByLogin($login)
    {
        return User::where('email', $login)
            ->orWhere('username', $login)
            ->first();
    }

    // Generate username unik dari nama
    public function generateUsername($name)
    {
        // Ambil nama tanpa spasi, lowercase
        $base = Str::slug($name, '.');
        $username = $base;
        $counter = 1;

        //cek apakah username sudah ada
        while (User::where('username', $username)->exists()) {
            $username = $base . $counter;
            $counter++;
        }

        return $username;
    }

    // Simpan user baru
    public function create(array $data)
    {
        return User::create($data);
    }
}
