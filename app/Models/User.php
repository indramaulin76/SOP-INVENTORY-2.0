<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Role constants.
     */
    const ROLE_PIMPINAN = 'Pimpinan';
    const ROLE_ADMIN = 'Admin';
    const ROLE_KARYAWAN = 'Karyawan';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'password',
        'active',
        'last_login',
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
            'active' => 'boolean',
            'last_login' => 'datetime',
        ];
    }

    /**
     * Check if user is Pimpinan.
     */
    public function isPimpinan(): bool
    {
        return $this->role === self::ROLE_PIMPINAN;
    }

    /**
     * Check if user is Admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Check if user is Karyawan.
     */
    public function isKaryawan(): bool
    {
        return $this->role === self::ROLE_KARYAWAN;
    }

    /**
     * Check if user has management access (Pimpinan or Admin).
     */
    public function hasManagementAccess(): bool
    {
        return in_array($this->role, [self::ROLE_PIMPINAN, self::ROLE_ADMIN]);
    }

    /**
     * Update last login timestamp.
     */
    public function updateLastLogin(): void
    {
        $this->last_login = now();
        $this->save();
    }

    /**
     * Get list of all roles.
     */
    public static function getRoles(): array
    {
        return [
            self::ROLE_PIMPINAN,
            self::ROLE_ADMIN,
            self::ROLE_KARYAWAN,
        ];
    }
}
