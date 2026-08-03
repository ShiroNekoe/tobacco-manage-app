<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoricalYieldReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_type',
        'row_number',
        'product',
        'origin',
        'metric_category',
        'batch_data',
        'total_qty',
        'avg_pct',
    ];

    protected $casts = [
        'batch_data' => 'array',
        'total_qty' => 'decimal:2',
        'avg_pct' => 'decimal:2',
    ];
}
