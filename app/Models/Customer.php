<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'contact_person',
        'phone',
        'address',
        'email',
    ];

    public function user()
    {
        return $this->hasOne(User::class)->where('role', User::ROLE_CUSTOMER);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function deliveryNotes(): HasMany
    {
        return $this->hasMany(DeliveryNote::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }
}
