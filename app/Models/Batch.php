<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class Batch extends Model
{
    use HasFactory;

    public const STATUS_OPEN = 'OPEN';
    public const STATUS_ACTIVE = 'ACTIVE';
    public const STATUS_WAITING = 'WAITING';
    public const STATUS_CLOSED = 'CLOSED';

    public const APPROVAL_PENDING = 'PENDING';
    public const APPROVAL_APPROVED = 'APPROVED';
    public const APPROVAL_REJECTED = 'REJECTED';

    protected $fillable = [
        'batch_code',
        'customer_id',
        'delivery_note_id',
        'product_type_id',
        'origin_id',
        'material_code',
        'dn_header_details',
        'mrl_header_details',
        'sections_data',
        'pack_type',
        'product_kg_per_sack',
        'product_tare_per_sack',
        'date_of_receipt',
        'dn_total_pack',
        'dn_gross_weight',
        'dn_tare_weight',
        'dn_netto_weight',
        'mrl_total_pack',
        'mrl_gross_weight',
        'mrl_tare_weight',
        'mrl_netto_weight',
        'discrepancy_dn_vs_mrl_kg',
        'discrepancy_remark',
        'force_close_reason',
        'mrl_discrepancy_flag',
        'mrl_approved_at',
        'mrl_approved_by_user_id',
        'supervisor_approval_status',
        'supervisor_approved_at',
        'supervisor_approved_by_user_id',
        'custom_dn_remark',
        'custom_mrl_remark',
        'custom_separation_remark',
        'start_time',
        'last_saved_at',
        'last_saved_by_user_id',
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
        'yield_product_pct',
        'yield_bits_stem_pct',
        'yield_dust_pct',
        'yield_waste_pct',
        'status',
        'created_by_user_id',
        'locked_at',
        'unlocked_at',
        'unlocked_by_user_id',
    ];

    protected $casts = [
        'dn_header_details' => 'array',
        'mrl_header_details' => 'array',
        'sections_data' => 'array',
        'date_of_receipt' => 'date',
        'locked_at' => 'datetime',
        'unlocked_at' => 'datetime',
        'mrl_approved_at' => 'datetime',
        'supervisor_approved_at' => 'datetime',
        'start_time' => 'datetime',
        'last_saved_at' => 'datetime',
        'mrl_discrepancy_flag' => 'boolean',
        'product_kg_per_sack' => 'decimal:2',
        'product_tare_per_sack' => 'decimal:2',
        'dn_gross_weight' => 'decimal:2',
        'dn_tare_weight' => 'decimal:2',
        'dn_netto_weight' => 'decimal:2',
        'mrl_gross_weight' => 'decimal:2',
        'mrl_tare_weight' => 'decimal:2',
        'mrl_netto_weight' => 'decimal:2',
        'discrepancy_dn_vs_mrl_kg' => 'decimal:2',
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
        'yield_product_pct' => 'decimal:2',
        'yield_bits_stem_pct' => 'decimal:2',
        'yield_dust_pct' => 'decimal:2',
        'yield_waste_pct' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function deliveryNote(): BelongsTo
    {
        return $this->belongsTo(DeliveryNote::class);
    }

    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class);
    }

    public function origin(): BelongsTo
    {
        return $this->belongsTo(Origin::class);
    }

    public function batchOrigins(): HasMany
    {
        return $this->hasMany(BatchOrigin::class);
    }

    public function weighingItems(): HasMany
    {
        return $this->hasMany(WeighingItem::class);
    }

    public function interimSeparations(): HasMany
    {
        return $this->hasMany(BatchInterimSeparation::class);
    }

    public function dnShipmentItems(): HasMany
    {
        return $this->hasMany(DnShipmentItem::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function lastSavedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_saved_by_user_id');
    }

    public function supervisorApprovedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_approved_by_user_id');
    }

    public function isLocked(): bool
    {
        return in_array($this->status, [self::STATUS_CLOSED, 'locked']);
    }

    public function isClosed(): bool
    {
        return $this->isLocked();
    }

    public function isApprovedBySupervisor(): bool
    {
        return $this->supervisor_approval_status === self::APPROVAL_APPROVED;
    }

    public function approveBySupervisor(User $user): void
    {
        $this->update([
            'supervisor_approval_status' => self::APPROVAL_APPROVED,
            'supervisor_approved_at' => now(),
            'supervisor_approved_by_user_id' => $user->id,
        ]);
    }

    /**
     * Dynamic Weight Discrepancy Explanation (DN vs MRL)
     */
    public function getDiscrepancyExplanationAttribute(): string
    {
        if ((float)$this->discrepancy_dn_vs_mrl_kg != 0.0) {
            return 'Selisih berat terdeteksi antara Surat Jalan (DN) dan penerimaan fisik gudang (MRL).';
        }
        return 'Berat fisik gudang (MRL) sesuai dengan Surat Jalan (DN).';
    }

    public function calculateDiscrepancy(): float
    {
        return (float) ($this->mrl_gross_weight - $this->dn_gross_weight);
    }

    public function hasDiscrepancy(): bool
    {
        return $this->mrl_discrepancy_flag || $this->discrepancy_dn_vs_mrl_kg != 0 || $this->calculateDiscrepancy() > 0;
    }

    public function validateClosureGates(?string $forceCloseReason = null): array
    {
        $errors = [];

        $normalizedStatus = strtoupper($this->status);
        if (! in_array($normalizedStatus, [self::STATUS_ACTIVE, self::STATUS_WAITING, 'DRAFT', 'ACTIVE', 'WAITING'])) {
            $errors[] = 'Gate 1: Status batch harus ACTIVE atau WAITING.';
        }

        if (Schema::hasTable('production_runs')) {
            $activeRunsCount = ProductionRun::where('mrl_id', $this->delivery_note_id)
                ->whereNull('finish_time')
                ->count();
            if ($activeRunsCount > 0) {
                $errors[] = 'Gate 2: Masih ada sesi produksi yang belum mencatat waktu selesai (End_Time).';
            }
        }

        if (Schema::hasTable('batch_origins')) {
            $hasRemaining = $this->batchOrigins()->where('remaining_kg', '>', 0)->exists();
            $providedReason = $forceCloseReason ?? $this->force_close_reason;
            if ($hasRemaining && empty($providedReason)) {
                $errors[] = 'Gate 3: Sisa kuantitas (Remaining_Qty) untuk asal tembakau belum 0 dan belum ada alasan Force Close dari Supervisor.';
            }
        }

        $sumYield = (float)$this->yield_product_pct + (float)$this->yield_bits_stem_pct + (float)$this->yield_dust_pct + (float)$this->yield_waste_pct;
        if (abs($sumYield - 100.00) > 0.01) {
            $errors[] = sprintf('Gate 4: Total KPI Balance Yield (%.2f%%) tidak seimbang 100.00%% (±0.01%%).', $sumYield);
        }

        return $errors;
    }

    public function canBeClosed(?string $forceCloseReason = null): bool
    {
        return count($this->validateClosureGates($forceCloseReason)) === 0;
    }
}
