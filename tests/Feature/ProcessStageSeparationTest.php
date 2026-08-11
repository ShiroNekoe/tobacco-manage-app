<?php

namespace Tests\Feature;

use App\Livewire\Karyawan\WeighingSheet;
use App\Models\Batch;
use App\Models\Customer;
use App\Models\DeliveryNote;
use App\Models\Origin;
use App\Models\ProductType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProcessStageSeparationTest extends TestCase
{
    use RefreshDatabase;

    public function test_process_1_and_process_2_separation_isolation_and_totals(): void
    {
        $worker = User::factory()->create(['role' => 'karyawan', 'shift' => 'Shift 1']);

        $customer = Customer::create(['name' => 'PT Stage Test', 'code' => 'CUST-STG']);
        $prodType = ProductType::create(['code' => 'P-STG', 'name' => 'PAITON STG']);
        $origin = Origin::create(['region_name' => 'PAITON STG']);
        $dn = DeliveryNote::create(['dn_number' => 'DN-STG-01', 'customer_id' => $customer->id, 'delivery_date' => Carbon::now()]);

        $batch = Batch::create([
            'batch_code' => 'BCH-STG-001',
            'customer_id' => $customer->id,
            'delivery_note_id' => $dn->id,
            'product_type_id' => $prodType->id,
            'origin_id' => $origin->id,
            'pack_type' => 'Bale',
            'product_kg_per_sack' => 25.20,
            'product_tare_per_sack' => 0.20,
            'date_of_receipt' => Carbon::now(),
            'status' => 'OPEN',
            'mrl_netto_weight' => 477.00,
        ]);

        // 1. Process 1: Worker fills P1 Product (6 sacks = 150kg) & P1 Dust (8.80kg)
        Livewire::actingAs($worker)
            ->test(WeighingSheet::class, ['batch_id' => $batch->id])
            ->set('p1_product_sack', 6)
            ->set('p1_dust_items.0.gross_kg', 9.5)
            ->set('p1_dust_items.0.tare_kg', 0.7)
            ->call('saveDraft')
            ->assertSet('p1_product_kg', 150.00)
            ->assertSet('p1_dust_netto_kg', 8.80);

        // 2. Process 2: Worker switches to Process 2 and fills P2 Product (5 sacks, remnant gross 6, tare 0.6) & Bit Stem (3.90kg)
        Livewire::actingAs($worker)
            ->test(WeighingSheet::class, ['batch_id' => $batch->id])
            ->call('setProcessStage', 2)
            ->set('p2_product_sack', 5)
            ->set('p2_remnant_gross_kg', 6.0)
            ->set('p2_remnant_tare_kg', 0.6)
            ->set('bit_stem_items.0.gross_kg', 4.5)
            ->set('bit_stem_items.0.tare_kg', 0.6)
            ->call('saveDraft')
            ->assertSet('p2_product_kg', 130.40)
            ->assertSet('separation_bits_stem_netto_kg', 3.90)
            // Combined totals (P1 150kg + P2 130.40kg = 280.40kg)
            ->assertSet('separation_product_kg', 280.40)
            ->assertSet('separation_dust_netto_kg', 8.80);

        // Assert database stores separation_p1_data & separation_p2_data JSON
        $freshBatch = Batch::find($batch->id);
        $this->assertEquals(6, $freshBatch->separation_p1_data['product_sack']);
        $this->assertEquals(5, $freshBatch->separation_p2_data['product_sack']);
        $this->assertEquals(280.40, (float) $freshBatch->separation_product_kg);
    }

    public function test_gross_standard_follows_custom_product_kg_per_sack(): void
    {
        $worker = User::factory()->create(['role' => 'karyawan', 'shift' => 'Shift 1']);
        $customer = Customer::create(['name' => 'PT Custom Gross Test', 'code' => 'CUST-GROSS']);
        $prodType = ProductType::create(['code' => 'P-GROSS', 'name' => 'PAITON GROSS']);
        $origin = Origin::create(['region_name' => 'PAITON GROSS']);
        $dn = DeliveryNote::create(['dn_number' => 'DN-GROSS-01', 'customer_id' => $customer->id, 'delivery_date' => Carbon::now()]);

        // Batch created with custom gross per sack = 20.50 kg/sak
        $batch = Batch::create([
            'batch_code' => 'BCH-GROSS-001',
            'customer_id' => $customer->id,
            'delivery_note_id' => $dn->id,
            'product_type_id' => $prodType->id,
            'origin_id' => $origin->id,
            'pack_type' => 'Bale',
            'product_kg_per_sack' => 20.50,
            'product_tare_per_sack' => 0.50,
            'date_of_receipt' => Carbon::now(),
            'status' => 'OPEN',
            'mrl_netto_weight' => 500.00,
        ]);

        Livewire::actingAs($worker)
            ->test(WeighingSheet::class, ['batch_id' => $batch->id])
            ->assertSet('product_kg_per_sack', 20.50)
            ->assertSet('product_tare_per_sack', 0.50)
            ->assertSee('Gross Standard: 20.50 kg/sak')
            ->assertSee('Tare Standard: 0.50 kg/sak')
            ->set('p1_product_sack', 10)
            // 10 sacks * (20.50 gross - 0.50 tare = 20.00 netto) = 200.00 kg
            ->assertSet('p1_product_kg', 200.00);
    }

    public function test_adding_multiple_p2_dust_slots_preserves_previous_rows(): void
    {
        $worker = User::factory()->create(['role' => 'karyawan', 'shift' => 'Shift 1']);
        $customer = Customer::create(['name' => 'PT P2 Dust Test', 'code' => 'CUST-DUST']);
        $prodType = ProductType::create(['code' => 'P-DUST', 'name' => 'PAITON DUST']);
        $origin = Origin::create(['region_name' => 'PAITON DUST']);
        $dn = DeliveryNote::create(['dn_number' => 'DN-DUST-01', 'customer_id' => $customer->id, 'delivery_date' => Carbon::now()]);

        $batch = Batch::create([
            'batch_code' => 'BCH-DUST-001',
            'customer_id' => $customer->id,
            'delivery_note_id' => $dn->id,
            'product_type_id' => $prodType->id,
            'origin_id' => $origin->id,
            'pack_type' => 'Bale',
            'product_kg_per_sack' => 25.20,
            'product_tare_per_sack' => 0.20,
            'date_of_receipt' => Carbon::now(),
            'status' => 'OPEN',
            'mrl_netto_weight' => 500.00,
        ]);

        Livewire::actingAs($worker)
            ->test(WeighingSheet::class, ['batch_id' => $batch->id])
            ->call('setProcessStage', 2)
            // Add initial slot 1
            ->call('addP2DustRow')
            ->set('p2_dust_items.0.gross_kg', '7')
            ->set('p2_dust_items.0.tare_kg', '0.7')
            ->assertSet('p2_dust_items.0.netto_kg', 6.30)
            ->assertSet('p2_dust_netto_kg', 6.30)
            // Add slot 2
            ->call('addP2DustRow')
            // Row 1 MUST NOT be cleared / lost!
            ->assertSet('p2_dust_items.0.gross_kg', '7')
            ->assertSet('p2_dust_items.0.tare_kg', '0.7')
            ->assertSet('p2_dust_items.0.netto_kg', 6.30)
            // Row 2 is new
            ->assertSet('p2_dust_items.1.gross_kg', 0)
            ->assertSet('p2_dust_items.1.tare_kg', 0)
            ->assertSet('p2_dust_items.1.netto_kg', 0)
            // Set Row 2 with comma decimal
            ->set('p2_dust_items.1.gross_kg', '5,5')
            ->set('p2_dust_items.1.tare_kg', '0,5')
            ->assertSet('p2_dust_items.1.netto_kg', 5.00)
            ->assertSet('p2_dust_netto_kg', 11.30)
            // Row 1 is still intact
            ->assertSet('p2_dust_items.0.gross_kg', '7')
            ->assertSet('p2_dust_items.0.netto_kg', 6.30);
    }
}
