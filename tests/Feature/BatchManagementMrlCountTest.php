<?php

namespace Tests\Feature;

use App\Livewire\Admin\BatchManagement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BatchManagementMrlCountTest extends TestCase
{
    use RefreshDatabase;

    public function test_typing_numeric_sack_count_generates_exact_mrl_rows(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(BatchManagement::class)
            ->set('target_sack_count', 32)
            ->assertCount('mrl_items', 32)
            ->set('target_sack_count', '')
            ->assertCount('mrl_items', 32);
    }

    public function test_clearing_dn_gross_weight_input_does_not_throw_exception(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(BatchManagement::class)
            ->set('dn_gross_weight_input', 12)
            ->set('dn_gross_weight_input', '')
            ->assertSet('dn_gross_weight_input', '');
    }

    public function test_batch_can_be_created_with_optional_dn_number(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = \App\Models\Customer::create(['name' => 'PT Test Customer', 'code' => 'CUST-OPT']);
        $productType = \App\Models\ProductType::create(['code' => 'PT-OPT', 'name' => 'Test Product Type']);
        $origin = \App\Models\Origin::create(['region_name' => 'PAITON TEST']);

        Livewire::actingAs($admin)
            ->test(BatchManagement::class)
            ->set('batch_code', 'BCH-TEST-OPTIONAL')
            ->set('customer_id', $customer->id)
            ->set('dn_number', '')
            ->set('product_type_id', $productType->id)
            ->set('origin_id', $origin->id)
            ->set('mrl_items', [
                ['sack_number' => 1, 'mrl_gross_weight' => 25.0],
            ])
            ->call('createBatch')
            ->assertHasNoErrors();

        $batch = \App\Models\Batch::where('batch_code', 'BCH-TEST-OPTIONAL')->first();
        $this->assertNotNull($batch);
        $this->assertDatabaseHas('delivery_notes', [
            'id' => $batch->delivery_note_id,
            'dn_number' => '',
        ]);
        $this->assertEquals('-', $batch->deliveryNote->formatted_dn_number);
    }

    public function test_product_tare_per_sack_calculates_dn_tare_weight_correctly(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(BatchManagement::class)
            ->set('product_tare_per_sack', 0.20)
            ->set('target_sack_count', 5)
            ->assertSet('dn_tare_weight', 1.00); // 5 * 0.20 = 1.00 kg
    }

    public function test_admin_or_supervisor_can_delete_batch(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = \App\Models\Customer::create(['name' => 'PT Delete Test', 'code' => 'DEL']);
        $productType = \App\Models\ProductType::create(['code' => 'DEL-P', 'name' => 'Delete Type']);
        $origin = \App\Models\Origin::create(['region_name' => 'DEL ORIGIN']);
        $dn = \App\Models\DeliveryNote::create(['dn_number' => 'DN-DEL-01', 'customer_id' => $customer->id, 'delivery_date' => now()]);

        $batch = \App\Models\Batch::create([
            'batch_code' => 'BCH-DEL-001',
            'customer_id' => $customer->id,
            'delivery_note_id' => $dn->id,
            'product_type_id' => $productType->id,
            'origin_id' => $origin->id,
            'date_of_receipt' => now(),
            'status' => 'OPEN',
            'created_by_user_id' => $admin->id,
        ]);

        Livewire::actingAs($admin)
            ->test(BatchManagement::class)
            ->call('deleteBatch', $batch->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('batches', [
            'id' => $batch->id,
        ]);
    }

    public function test_duplicate_batch_code_can_be_created_without_unique_constraint_error(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = \App\Models\Customer::create(['name' => 'PT Duplicate Batch Test', 'code' => 'DUP']);
        $productType = \App\Models\ProductType::create(['code' => 'DUP-P', 'name' => 'Duplicate Type']);
        $origin = \App\Models\Origin::create(['region_name' => 'DUP ORIGIN']);

        // Create 1st batch with BCH-20260806-001
        Livewire::actingAs($admin)
            ->test(BatchManagement::class)
            ->set('batch_code', 'BCH-20260806-001')
            ->set('customer_id', $customer->id)
            ->set('dn_number', 'DN-DUP-001')
            ->set('product_type_id', $productType->id)
            ->set('origin_id', $origin->id)
            ->set('mrl_items', [
                ['sack_number' => 1, 'mrl_gross_weight' => 25.0],
            ])
            ->call('createBatch')
            ->assertHasNoErrors();

        // Create 2nd batch with SAME batch_code BCH-20260806-001
        Livewire::actingAs($admin)
            ->test(BatchManagement::class)
            ->set('batch_code', 'BCH-20260806-001')
            ->set('customer_id', $customer->id)
            ->set('dn_number', 'DN-DUP-002')
            ->set('product_type_id', $productType->id)
            ->set('origin_id', $origin->id)
            ->set('mrl_items', [
                ['sack_number' => 1, 'mrl_gross_weight' => 25.0],
            ])
            ->call('createBatch')
            ->assertHasNoErrors();

        $this->assertEquals(2, \App\Models\Batch::where('batch_code', 'BCH-20260806-001')->count());
    }

    public function test_box_pack_type_auto_distributes_dn_gross_weight_across_boxes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = \App\Models\Customer::create(['name' => 'PT Box Customer', 'code' => 'BOX']);
        $productType = \App\Models\ProductType::create(['code' => 'BOX-P', 'name' => 'Box Type']);
        $origin = \App\Models\Origin::create(['region_name' => 'BOX ORIGIN']);

        Livewire::actingAs($admin)
            ->test(BatchManagement::class)
            ->set('pack_type', 'Box')
            ->set('target_sack_count', 5)
            ->set('dn_gross_weight_input', 1000.00)
            ->assertSet('mrl_items.0.mrl_gross_weight', 200.00)
            ->assertSet('mrl_items.1.mrl_gross_weight', 200.00)
            ->assertSet('mrl_items.2.mrl_gross_weight', 200.00)
            ->assertSet('mrl_items.3.mrl_gross_weight', 200.00)
            ->assertSet('mrl_items.4.mrl_gross_weight', 200.00)
            ->assertSet('mrl_gross_weight', 1000.00)
            ->set('target_sack_count', 4)
            ->assertSet('mrl_items.0.mrl_gross_weight', 250.00)
            ->assertSet('mrl_items.1.mrl_gross_weight', 250.00)
            ->assertSet('mrl_items.2.mrl_gross_weight', 250.00)
            ->assertSet('mrl_items.3.mrl_gross_weight', 250.00)
            ->assertSet('mrl_gross_weight', 1000.00)
            ->set('batch_code', 'BCH-BOX-001')
            ->set('customer_id', $customer->id)
            ->set('product_type_id', $productType->id)
            ->set('origin_id', $origin->id)
            ->call('createBatch')
            ->assertHasNoErrors();

        $batch = \App\Models\Batch::where('batch_code', 'BCH-BOX-001')->first();
        $this->assertNotNull($batch);
        $this->assertEquals('Box', $batch->pack_type);
        $this->assertEquals(4, $batch->weighingItems()->count());
    }
}

