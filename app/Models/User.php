<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_SISWA = 'siswa';

    public const ROLE_GURU = 'guru';

    public const ROLE_BK = 'bk';

    public const ROLE_ADMIN = 'admin';

    public const ROLES = [
        self::ROLE_SISWA => 'Siswa',
        self::ROLE_GURU => 'Guru',
        self::ROLE_BK => 'Guru BK',
        self::ROLE_ADMIN => 'Admin',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'telepon',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Pengguna yang login dalam N hari terakhir dianggap aktif.
     */
    public function scopeAktif($query, int $hari = 7)
    {
        return $query->where('last_login_at', '>=', now()->subDays($hari));
    }

    public function profilSiswa(): HasOne
    {
        return $this->hasOne(Siswa::class);
    }

    public function isSiswa(): bool
    {
        return $this->role === self::ROLE_SISWA;
    }

    public function isGuru(): bool
    {
        return $this->role === self::ROLE_GURU;
    }

    public function isBk(): bool
    {
        return $this->role === self::ROLE_BK;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function roleLabel(): string
    {
        return self::ROLES[$this->role] ?? $this->role;
    }
}
