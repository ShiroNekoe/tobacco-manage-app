<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatchOrigin extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'origin_id',
        'allocated_kg',
        'processed_kg',
        'remaining_kg',
        'status',
    ];

    protected $casts = [
        'allocated_kg' => 'decimal:2',
        'processed_kg' => 'decimal:2',
        'remaining_kg' => 'decimal:2',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function origin(): BelongsTo
    {
        return $this->belongsTo(Origin::class);
    }
}
