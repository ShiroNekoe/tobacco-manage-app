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
        'product_tare_per_sack',
        'separation_product_kg',
        'separation_product_sack',
        'separation_product_gross_kg',
        'separation_product_tare_kg',
        'separation_product_remnant_gross_kg',
        'separation_product_remnant_tare_kg',
        'separation_product_remnant_kg',
        'separation_bits_stem_kg',
        'separation_bits_stem_gross_kg',
        'separation_bits_stem_tare_kg',
        'separation_bits_stem_netto_kg',
        'bit_stem_items',
        'separation_dust_kg',
        'separation_dust_gross_kg',
        'separation_dust_tare_kg',
        'separation_dust_netto_kg',
        'dust_items',
        'separation_p1_data',
        'separation_p2_data',
        'separation_waste_kg',
        'sacks_processed_count',
        'notes',
    ];

    protected $casts = [
        'product_tare_per_sack' => 'decimal:2',
        'separation_product_kg' => 'decimal:2',
        'separation_product_sack' => 'integer',
        'separation_product_gross_kg' => 'decimal:2',
        'separation_product_tare_kg' => 'decimal:2',
        'separation_bits_stem_kg' => 'decimal:2',
        'separation_bits_stem_gross_kg' => 'decimal:2',
        'separation_bits_stem_tare_kg' => 'decimal:2',
        'separation_bits_stem_netto_kg' => 'decimal:2',
        'bit_stem_items' => 'array',
        'separation_dust_kg' => 'decimal:2',
        'separation_dust_gross_kg' => 'decimal:2',
        'separation_dust_tare_kg' => 'decimal:2',
        'separation_dust_netto_kg' => 'decimal:2',
        'dust_items' => 'array',
        'separation_p1_data' => 'array',
        'separation_p2_data' => 'array',
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
