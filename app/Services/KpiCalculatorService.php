<?php

namespace App\Services;

use App\Models\SystemSetting;
use InvalidArgumentException;
use Throwable;

class KpiCalculatorService
{
    public const DEFAULT_SHIFT_MINUTES = 720; // 12 hours * 60 mins
    public const DEFAULT_TARGET_CAPACITY = 1000.0; // 1000 kg/hr

    /**
     * Calculate all KPIs for a production run.
     */
    public function calculate(
        float $netWeight,
        float $productWeight,
        float $bitsStemWeight,
        float $dustWeight,
        int $totalDowntimeMinutes,
        ?float $targetCapacity = null
    ): array {
        if ($netWeight <= 0) {
            return [
                'waste_weight' => 0.0,
                'product_yield_pct' => 0.0,
                'bits_stem_pct' => 0.0,
                'dust_pct' => 0.0,
                'waste_pct' => 0.0,
                'uptime_hours' => 0.0,
                'capacity_kg_hr' => 0.0,
                'uptime_pct' => 0.0,
                'performance_pct' => 0.0,
            ];
        }

        $totalOutputWeights = $productWeight + $bitsStemWeight + $dustWeight;

        // Validation rule: Reject if total outputs exceed Net Weight
        if (round($totalOutputWeights, 4) > round($netWeight, 4)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Total bobot keluaran (Produk %.2fkg + Gagang %.2fkg + Debu %.2fkg = %.2fkg) melebihi Net Weight (%.2fkg).',
                    $productWeight,
                    $bitsStemWeight,
                    $dustWeight,
                    $totalOutputWeights,
                    $netWeight
                )
            );
        }

        $wasteWeight = max(0.0, $netWeight - $totalOutputWeights);

        $productYieldPct = ($productWeight / $netWeight) * 100.0;
        $bitsStemPct = ($bitsStemWeight / $netWeight) * 100.0;
        $dustPct = ($dustWeight / $netWeight) * 100.0;
        $wastePct = ($wasteWeight / $netWeight) * 100.0;

        $shiftMinutes = self::DEFAULT_SHIFT_MINUTES;
        try {
            $shiftMinutes = (int) SystemSetting::get('shift_duration_minutes', self::DEFAULT_SHIFT_MINUTES);
        } catch (Throwable $e) {
            $shiftMinutes = self::DEFAULT_SHIFT_MINUTES;
        }

        $uptimeMinutes = max(0, $shiftMinutes - $totalDowntimeMinutes);
        $uptimeHours = $uptimeMinutes / 60.0;
        $uptimePct = ($uptimeMinutes / $shiftMinutes) * 100.0;

        $capacityKgHr = $uptimeHours > 0 ? ($productWeight / $uptimeHours) : 0.0;

        $targetCap = $targetCapacity;
        if (! $targetCap) {
            try {
                $targetCap = (float) SystemSetting::get('target_capacity_kg_hr', self::DEFAULT_TARGET_CAPACITY);
            } catch (Throwable $e) {
                $targetCap = self::DEFAULT_TARGET_CAPACITY;
            }
        }

        $performancePct = $targetCap > 0 ? ($capacityKgHr / $targetCap) * 100.0 : 0.0;

        return [
            'waste_weight' => round($wasteWeight, 2),
            'product_yield_pct' => round($productYieldPct, 2),
            'bits_stem_pct' => round($bitsStemPct, 2),
            'dust_pct' => round($dustPct, 2),
            'waste_pct' => round($wastePct, 2),
            'uptime_hours' => round($uptimeHours, 2),
            'capacity_kg_hr' => round($capacityKgHr, 2),
            'uptime_pct' => round($uptimePct, 2),
            'performance_pct' => round($performancePct, 2),
        ];
    }
}
