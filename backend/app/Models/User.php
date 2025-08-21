<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'username', // Sebagai identifier unik pengguna
        'password',
        'role',
        'photo',
        'telegram_chat_id',
        'telegram_username',
        'telegram_notifications_enabled',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'telegram_notifications_enabled' => 'boolean',
        ];
    }

    // Relation
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    // Telegram helpers
    public function hasTelegramEnabled()
    {
        return !empty($this->telegram_chat_id) && $this->telegram_notifications_enabled;
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function scopeWithTelegram($query)
    {
        return $query->whereNotNull('telegram_chat_id')
                    ->where('telegram_notifications_enabled', true);
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }
}
