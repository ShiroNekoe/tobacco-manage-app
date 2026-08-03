<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class WasteValidationTest extends TestCase
{
    public function test_uncountable_waste_calculated_theoretically(): void
    {
        $netto = 720.00;
        $prodKg = 600.00;
        $stemKg = 70.00;
        $dustKg = 30.00;

        $uncountableWasteKg = round($netto - ($prodKg + $stemKg + $dustKg), 2);

        $this->assertEquals(20.00, $uncountableWasteKg);
    }
}
