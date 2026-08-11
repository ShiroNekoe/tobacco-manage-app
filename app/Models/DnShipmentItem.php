<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DnShipmentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'dn_shipment_id',
        'batch_id',
        'batch_code',
        'item_no',
        'origin',
        'origin_code',
        'material_type',
        'standard_sack_count',
        'standard_gross_per_sack',
        'standard_tare_per_sack',
        'standard_netto_per_sack',
        'has_remnant',
        'remnant_gross_kg',
        'remnant_tare_kg',
        'remnant_netto_kg',
        'total_sacks',
        'total_gross_kg',
        'total_tare_kg',
        'total_netto_kg',
    ];

    protected $casts = [
        'batch_id' => 'integer',
        'item_no' => 'integer',
        'standard_sack_count' => 'integer',
        'standard_gross_per_sack' => 'decimal:2',
        'standard_tare_per_sack' => 'decimal:2',
        'standard_netto_per_sack' => 'decimal:2',
        'has_remnant' => 'boolean',
        'remnant_gross_kg' => 'decimal:2',
        'remnant_tare_kg' => 'decimal:2',
        'remnant_netto_kg' => 'decimal:2',
        'total_sacks' => 'integer',
        'total_gross_kg' => 'decimal:2',
        'total_tare_kg' => 'decimal:2',
        'total_netto_kg' => 'decimal:2',
    ];

    public function dnShipment(): BelongsTo
    {
        return $this->belongsTo(DnShipment::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * Compute and update calculated totals for this item
     */
    public function computeTotals(): void
    {
        $stdCount = max(0, (int) $this->standard_sack_count);
        $stdGross = (float) ($this->standard_gross_per_sack ?: 50.70);
        $stdTare = (float) ($this->standard_tare_per_sack ?: 0.70);
        $stdNetto = (float) ($this->standard_netto_per_sack ?: max(0, $stdGross - $stdTare));

        $remGross = $this->has_remnant ? (float) $this->remnant_gross_kg : 0.0;
        $remTare = $this->has_remnant ? (float) $this->remnant_tare_kg : 0.0;
        $remNetto = $this->has_remnant ? (float) $this->remnant_netto_kg : 0.0;

        $this->total_sacks = $stdCount + ($this->has_remnant ? 1 : 0);
        $this->total_gross_kg = ($stdCount * $stdGross) + $remGross;
        $this->total_tare_kg = ($stdCount * $stdTare) + $remTare;
        $this->total_netto_kg = ($stdCount * $stdNetto) + $remNetto;
    }

    /**
     * Generate structured list of individual sacks for PDF breakdown
     * e.g. Sack 1..N + Sack Remnant
     */
    public function generateSackList(): array
    {
        $list = [];
        $stdCount = max(0, (int) $this->standard_sack_count);
        $stdGross = (float) ($this->standard_gross_per_sack ?: 50.70);
        $stdTare = (float) ($this->standard_tare_per_sack ?: 0.70);
        $stdNetto = (float) ($this->standard_netto_per_sack ?: max(0, $stdGross - $stdTare));

        for ($i = 1; $i <= $stdCount; $i++) {
            $list[] = [
                'sack_no' => $i,
                'type' => 'Standard',
                'gross_kg' => $stdGross,
                'tare_kg' => $stdTare,
                'netto_kg' => $stdNetto,
            ];
        }

        if ($this->has_remnant && ($this->remnant_gross_kg > 0 || $this->remnant_netto_kg > 0)) {
            $list[] = [
                'sack_no' => $stdCount + 1,
                'type' => 'Remnant',
                'gross_kg' => (float) $this->remnant_gross_kg,
                'tare_kg' => (float) $this->remnant_tare_kg,
                'netto_kg' => (float) $this->remnant_netto_kg,
            ];
        }

        return $list;
    }
}
