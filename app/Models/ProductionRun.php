<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProductionRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_code',
        'mrl_id',
        'shift',
        'group_name',
        'group_leader_name',
        'operator_1_name',
        'operator_2_name',
        'start_time',
        'finish_time',
        'product_weight',
        'bits_stem_weight',
        'dust_weight',
        'waste_weight',
        'total_downtime_minutes',
        'product_yield_pct',
        'bits_stem_pct',
        'dust_pct',
        'waste_pct',
        'uptime_hours',
        'capacity_kg_hr',
        'uptime_pct',
        'performance_pct',
        'machine_status',
        'status',
        'remarks',
        'created_by_user_id',
        'locked_at',
        'unlocked_at',
        'unlocked_by_user_id',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'finish_time' => 'datetime',
        'locked_at' => 'datetime',
        'unlocked_at' => 'datetime',
        'product_weight' => 'decimal:2',
        'bits_stem_weight' => 'decimal:2',
        'dust_weight' => 'decimal:2',
        'waste_weight' => 'decimal:2',
        'total_downtime_minutes' => 'integer',
        'product_yield_pct' => 'decimal:2',
        'bits_stem_pct' => 'decimal:2',
        'dust_pct' => 'decimal:2',
        'waste_pct' => 'decimal:2',
        'uptime_hours' => 'decimal:2',
        'capacity_kg_hr' => 'decimal:2',
        'uptime_pct' => 'decimal:2',
        'performance_pct' => 'decimal:2',
    ];

    public function mrl(): BelongsTo
    {
        return $this->belongsTo(MaterialReceiptList::class, 'mrl_id');
    }

    public function downtimeEvents(): HasMany
    {
        return $table = $this->hasMany(DowntimeEvent::class);
    }

    public function certificate(): HasOne
    {
        return $this->hasOne(ProductCertificate::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function unlockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'unlocked_by_user_id');
    }

    public function isLocked(): bool
    {
        return $this->status === 'completed' || $this->status === 'locked';
    }
}
