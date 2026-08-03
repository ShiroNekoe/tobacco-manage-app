<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialReceiptList extends Model
{
    use HasFactory;

    protected $fillable = [
        'mrl_number',
        'supplier_id',
        'delivery_note_id',
        'origin_region',
        'tobacco_grade',
        'batch_number',
        'gross_weight',
        'tare_weight',
        'net_weight',
        'total_pack',
        'status',
        'received_by_user_id',
    ];

    protected $casts = [
        'gross_weight' => 'decimal:2',
        'tare_weight' => 'decimal:2',
        'net_weight' => 'decimal:2',
        'total_pack' => 'integer',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function deliveryNote(): BelongsTo
    {
        return $this->belongsTo(DeliveryNote::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by_user_id');
    }

    public function productionRuns(): HasMany
    {
        return $this->hasMany(ProductionRun::class, 'mrl_id');
    }
}
