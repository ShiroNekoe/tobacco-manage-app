<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'default_sack_weight',
        'default_tare_weight',
        'is_active',
    ];

    protected $casts = [
        'default_sack_weight' => 'decimal:2',
        'default_tare_weight' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}
