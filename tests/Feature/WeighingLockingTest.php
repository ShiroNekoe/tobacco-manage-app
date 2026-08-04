<?php

namespace Tests\Feature;

use App\Livewire\Admin\BatchManagement;
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

class WeighingLockingTest extends TestCase
{
    use RefreshDatabase;

    public function test_karyawan_can_input_sack_weighing_and_lock_data(): void
    {
        $karyawan = User::factory()->create(['role' => 'karyawan']);

        $customer = Customer::create(['name' => 'PT Test', 'code' => 'TEST-01']);
        $prodType = ProductType::create(['code' => 'P-01', 'name' => 'PAITON P10T5']);
        $origin = Origin::create(['region_name' => 'PAITON']);
        $dn = DeliveryNote::create([
            'dn_number' => 'DN-001',
            'customer_id' => $customer->id,
            'delivery_date' => Carbon::now(),
        ]);

        $batch = Batch::create([
            'batch_code' => 'BATCH-TEST-001',
            'customer_id' => $customer->id,
            'delivery_note_id' => $dn->id,
            'product_type_id' => $prodType->id,
            'origin_id' => $origin->id,
            'pack_type' => 'Bale',
            'date_of_receipt' => Carbon::now(),
            'dn_gross_weight' => 520,
            'dn_tare_weight' => 20,
            'dn_netto_weight' => 500,
            'status' => 'draft',
        ]);

        Livewire::actingAs($karyawan)
            ->test(WeighingSheet::class, ['batch_id' => $batch->id])
            ->set('items.0.gross_kg', 52.00)
            ->set('items.0.tare_kg', 2.00)
            ->set('separation_product_kg', 40.00)
            ->set('separation_bits_stem_kg', 5.00)
            ->set('separation_dust_kg', 2.00)
            ->call('lockData')
            ->assertHasNoErrors();

        $batch->refresh();
        $this->assertEquals('locked', $batch->status);
        $this->assertEquals(1, $batch->mrl_total_pack);
        $this->assertEquals(50.00, $batch->mrl_netto_weight);

        // Karyawan editing locked batch throws 403
        Livewire::actingAs($karyawan)
            ->test(WeighingSheet::class, ['batch_id' => $batch->id])
            ->set('separation_product_kg', 42.00)
            ->call('saveDraft')
            ->assertStatus(403);
    }

    public function test_admin_can_unlock_locked_batch(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $customer = Customer::create(['name' => 'PT Test 2', 'code' => 'TEST-02']);
        $prodType = ProductType::create(['code' => 'P-02', 'name' => 'LOMBOK P9K5']);
        $origin = Origin::create(['region_name' => 'LOMBOK']);
        $dn = DeliveryNote::create(['dn_number' => 'DN-002', 'customer_id' => $customer->id, 'delivery_date' => Carbon::now()]);

        $batch = Batch::create([
            'batch_code' => 'BATCH-TEST-002',
            'customer_id' => $customer->id,
            'delivery_note_id' => $dn->id,
            'product_type_id' => $prodType->id,
            'origin_id' => $origin->id,
            'date_of_receipt' => Carbon::now(),
            'status' => 'locked',
        ]);

        Livewire::actingAs($admin)
            ->test(BatchManagement::class)
            ->call('unlockBatch', $batch->id)
            ->assertHasNoErrors();

        $batch->refresh();
        $this->assertEquals('draft', $batch->status);
    }

    public function test_selecting_empty_batch_id_does_not_throw_type_error(): void
    {
        $karyawan = User::factory()->create(['role' => 'karyawan']);

        Livewire::actingAs($karyawan)
            ->test(WeighingSheet::class)
            ->call('selectBatch', '')
            ->assertSet('batchId', null)
            ->call('selectBatch', null)
            ->assertSet('batchId', null);
    }

    public function test_remnant_comma_decimal_input_does_not_throw_type_error(): void
    {
        $karyawan = User::factory()->create(['role' => 'karyawan']);

        Livewire::actingAs($karyawan)
            ->test(WeighingSheet::class)
            ->set('separation_product_remnant_gross_kg', '51,5')
            ->set('separation_product_remnant_tare_kg', '0,4')
            ->assertSet('separation_product_remnant_kg', 51.1);
    }
}
