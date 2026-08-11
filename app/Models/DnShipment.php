<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DnShipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'dn_number',
        'shipment_date',
        'customer_id',
        'product_type_id',
        'vehicle_number',
        'driver_name',
        'destination',
        'notes',
        'total_sacks',
        'total_gross_kg',
        'total_tare_kg',
        'total_netto_kg',
        'status',
        'customer_approved_at',
        'customer_approved_by_user_id',
        'customer_approval_note',
        'created_by',
    ];

    protected $casts = [
        'shipment_date' => 'date',
        'customer_approved_at' => 'datetime',
        'total_sacks' => 'integer',
        'total_gross_kg' => 'decimal:2',
        'total_tare_kg' => 'decimal:2',
        'total_netto_kg' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function customerApprovedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_approved_by_user_id');
    }

    public function isApprovedByCustomer(): bool
    {
        return $this->status === 'Approved' || ! empty($this->customer_approved_at);
    }

    public function approveByCustomer(?int $userId = null, ?string $note = null): void
    {
        $this->update([
            'status' => 'Approved',
            'customer_approved_at' => now(),
            'customer_approved_by_user_id' => $userId ?: auth()->id(),
            'customer_approval_note' => $note,
        ]);
    }

    public function items(): HasMany
    {
        return $this->hasMany(DnShipmentItem::class)->orderBy('item_no', 'asc');
    }

    public function recalculateTotals(): void
    {
        $this->load('items');
        $this->total_sacks = $this->items->sum('total_sacks');
        $this->total_gross_kg = $this->items->sum('total_gross_kg');
        $this->total_tare_kg = $this->items->sum('total_tare_kg');
        $this->total_netto_kg = $this->items->sum('total_netto_kg');
        $this->save();
    }
}
