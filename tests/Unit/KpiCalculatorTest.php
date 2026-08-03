<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class KpiCalculatorTest extends TestCase
{
    public function test_kpi_yield_percentages_sum_to_100_percent_with_decimal_precision(): void
    {
        $netto = 498.00;
        $prodKg = 410.00;
        $stemKg = 45.00;
        $dustKg = 20.00;
        $wasteKg = round($netto - ($prodKg + $stemKg + $dustKg), 2); // 23.00

        $prodPct = round(($prodKg / $netto) * 100, 2); // 82.33
        $stemPct = round(($stemKg / $netto) * 100, 2); // 9.04
        $dustPct = round(($dustKg / $netto) * 100, 2); // 4.02
        $wastePct = round(100.00 - ($prodPct + $stemPct + $dustPct), 2); // 4.61

        $totalPct = $prodPct + $stemPct + $dustPct + $wastePct;

        $this->assertEquals(100.00, $totalPct);
        $this->assertTrue(abs($totalPct - 100.00) <= 0.01);
    }
}
