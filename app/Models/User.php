<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_KARYAWAN = 'karyawan';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_SUPERVISOR = 'supervisor';
    public const ROLE_CUSTOMER = 'customer';

    protected $fillable = [
        'name',
        'email',
        'role',
        'password',
        'shift',
        'group',
        'customer_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function isKaryawan(): bool
    {
        return in_array(strtolower($this->role ?? ''), [self::ROLE_KARYAWAN, 'worker', 'operator', 'karyawan']);
    }

    public function isWorker(): bool
    {
        return $this->isKaryawan();
    }

    public function isAdmin(): bool
    {
        return in_array(strtolower($this->role ?? ''), [self::ROLE_ADMIN, 'administrator', 'admin']);
    }

    public function isSupervisor(): bool
    {
        return in_array(strtolower($this->role ?? ''), [self::ROLE_SUPERVISOR, 'supervisor']);
    }

    public function isCustomer(): bool
    {
        return strtolower($this->role ?? '') === self::ROLE_CUSTOMER;
    }
}
