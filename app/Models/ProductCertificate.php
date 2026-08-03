<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductCertificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'certificate_number',
        'production_run_id',
        'issued_at',
        'issued_by_user_id',
        'data_snapshot',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'data_snapshot' => 'array',
    ];

    public function productionRun(): BelongsTo
    {
        return $this->belongsTo(ProductionRun::class);
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by_user_id');
    }
}
