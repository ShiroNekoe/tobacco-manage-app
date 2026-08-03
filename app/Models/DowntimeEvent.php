<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DowntimeEvent extends Model
{
    use HasFactory;

    public const REASON_MACHINE_BREAKDOWN = 'Machine Breakdown';
    public const REASON_MATERIAL_SHORTAGE = 'Material Shortage';
    public const REASON_SCHEDULED_MAINTENANCE = 'Scheduled Maintenance';
    public const REASON_QUALITY_HOLD = 'Quality Hold';
    public const REASON_OPERATOR_BREAK = 'Operator Break';
    public const REASON_OTHER = 'Other';

    public static array $allowedReasons = [
        self::REASON_MACHINE_BREAKDOWN,
        self::REASON_MATERIAL_SHORTAGE,
        self::REASON_SCHEDULED_MAINTENANCE,
        self::REASON_QUALITY_HOLD,
        self::REASON_OPERATOR_BREAK,
        self::REASON_OTHER,
    ];

    protected $fillable = [
        'production_run_id',
        'batch_id',
        'downtime_minutes',
        'reason',
        'remarks',
    ];

    protected $casts = [
        'downtime_minutes' => 'integer',
    ];

    public function productionRun(): BelongsTo
    {
        return $this->belongsTo(ProductionRun::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }
}
