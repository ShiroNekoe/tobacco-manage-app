<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeighingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'sack_number',
        'gross_kg',
        'tare_kg',
        'netto_kg',
        'remark',
        'created_by_user_id',
        'shift',
        'group',
    ];

    protected $casts = [
        'sack_number' => 'integer',
        'gross_kg' => 'decimal:2',
        'tare_kg' => 'decimal:2',
        'netto_kg' => 'decimal:2',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
