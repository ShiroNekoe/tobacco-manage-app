<?php

namespace Tests\Unit;

use App\Models\Batch;
use App\Models\Customer;
use App\Models\DeliveryNote;
use App\Models\Origin;
use App\Models\ProductType;
use App\Models\WeighingItem;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PdfCertificateRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_pdf_certificate_renders_exact_sections_and_yield_percentages(): void
    {
        $customer = Customer::create(['name' => 'PT. Falih Nur Gemilang', 'code' => 'FNG']);
        $prodType = ProductType::create(['code' => 'PAITON-P10T5', 'name' => 'PAITON P10T5']);
        $origin = Origin::create(['region_name' => 'PAITON']);
        $dn = DeliveryNote::create([
            'dn_number' => 'DN-2026-0801',
            'customer_id' => $customer->id,
            'delivery_date' => Carbon::now(),
        ]);

        $batch = Batch::create([
            'batch_code' => 'BATCH-TEST-PDF',
            'customer_id' => $customer->id,
            'delivery_note_id' => $dn->id,
            'product_type_id' => $prodType->id,
            'origin_id' => $origin->id,
            'pack_type' => 'Bale',
            'date_of_receipt' => Carbon::now(),
            'dn_total_pack' => 10,
            'dn_gross_weight' => 520.00,
            'dn_tare_weight' => 20.00,
            'dn_netto_weight' => 500.00,
            'mrl_total_pack' => 10,
            'mrl_gross_weight' => 518.00,
            'mrl_tare_weight' => 20.00,
            'mrl_netto_weight' => 498.00,
            'discrepancy_dn_vs_mrl_kg' => 2.00,
            'separation_product_kg' => 410.00,
            'separation_bits_stem_kg' => 45.00,
            'separation_dust_kg' => 20.00,
            'separation_waste_kg' => 23.00,
            'yield_product_pct' => 82.33,
            'yield_bits_stem_pct' => 9.04,
            'yield_dust_pct' => 4.02,
            'yield_waste_pct' => 4.61,
            'status' => 'locked',
        ]);

        WeighingItem::create([
            'batch_id' => $batch->id,
            'sack_number' => 1,
            'gross_kg' => 518.00,
            'tare_kg' => 20.00,
            'netto_kg' => 498.00,
            'remark' => 'Normal',
        ]);

        $html = view('certificates.process-certificate-pdf', compact('batch'))->render();

        $this->assertStringContainsString('PROCESS CERTIFICATE', $html);
        $this->assertStringContainsString('PT. Falih Nur Gemilang', $html);
        $this->assertStringContainsString('1. DELIVERY NOTE (DN)', $html);
        $this->assertStringContainsString('2. MATERIAL RECEIPT LIST (MRL)', $html);
        $this->assertStringContainsString('3. SEPARATION RESULTS REPORT', $html);
        $this->assertStringContainsString('Gross qty. Based on Delivery Note.', $html);
        $this->assertStringContainsString('Uncountable waste qty. based on teoritical calculation.', $html);
        $this->assertStringContainsString('PERCENTAGE (YIELD)', $html);
        $this->assertStringContainsString('100,00%', $html);
    }
}
