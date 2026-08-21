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
    public const ROLE_IT_SUPPORT = 'it_support';

    protected $fillable = [
        'name',
        'email',
        'role',
        'password',
        'shift',
        'group',
        'customer_id',
        'must_change_password',
        'password_changed_at',
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
            'must_change_password' => 'boolean',
            'password_changed_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function isItSupport(): bool
    {
        return in_array(strtolower($this->role ?? ''), [self::ROLE_IT_SUPPORT, 'it_support', 'it support', 'itsupport']);
    }

    public function isKaryawan(): bool
    {
        return $this->isItSupport() || in_array(strtolower($this->role ?? ''), [self::ROLE_KARYAWAN, 'worker', 'operator', 'karyawan']);
    }

    public function isWorker(): bool
    {
        return $this->isKaryawan();
    }

    public function isAdmin(): bool
    {
        return $this->isItSupport() || in_array(strtolower($this->role ?? ''), [self::ROLE_ADMIN, 'administrator', 'admin']);
    }

    public function isSupervisor(): bool
    {
        return $this->isItSupport() || in_array(strtolower($this->role ?? ''), [self::ROLE_SUPERVISOR, 'supervisor']);
    }

    public function isCustomer(): bool
    {
        return strtolower($this->role ?? '') === self::ROLE_CUSTOMER;
    }
}
