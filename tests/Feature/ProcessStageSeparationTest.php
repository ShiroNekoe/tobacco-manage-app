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
}
