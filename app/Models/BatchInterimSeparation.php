<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatchInterimSeparation extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'user_id',
        'shift',
        'group',
        'separation_product_kg',
        'separation_bits_stem_kg',
        'separation_dust_kg',
        'separation_waste_kg',
        'sacks_processed_count',
        'notes',
    ];

    protected $casts = [
        'separation_product_kg' => 'decimal:2',
        'separation_bits_stem_kg' => 'decimal:2',
        'separation_dust_kg' => 'decimal:2',
        'separation_waste_kg' => 'decimal:2',
        'sacks_processed_count' => 'integer',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
