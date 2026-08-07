<?php

namespace Tests\Feature;

use App\Livewire\Karyawan\WeighingSheet;
use App\Models\Batch;
use App\Models\Customer;
use App\Models\DeliveryNote;
use App\Models\Origin;
use App\Models\ProductType;
use App\Models\User;
use App\Models\WeighingItem;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PredecessorLockTest extends TestCase
{
    use RefreshDatabase;

    public function test_worker_cannot_modify_predecessor_shift_rows(): void
    {
        $customer = Customer::create(['name' => 'PT Test 1', 'code' => 'CUST-01']);
        $prodType = ProductType::create(['code' => 'P-01', 'name' => 'PAITON P10T5']);
        $origin = Origin::create(['region_name' => 'PAITON']);
        $dn = DeliveryNote::create(['dn_number' => 'DN-001', 'customer_id' => $customer->id, 'delivery_date' => Carbon::now()]);

        $workerShift1 = User::create([
            'name' => 'Worker 1',
            'email' => 'w1@tobacco.com',
            'password' => bcrypt('password'),
            'role' => 'karyawan',
            'shift' => 'Shift 1',
            'group' => 'Group A',
        ]);

        $workerShift2 = User::create([
            'name' => 'Worker 2',
            'email' => 'w2@tobacco.com',
            'password' => bcrypt('password'),
            'role' => 'karyawan',
            'shift' => 'Shift 2',
            'group' => 'Group B',
        ]);

        $batch = Batch::create([
            'batch_code' => 'BCH-TEST-001',
            'customer_id' => $customer->id,
            'delivery_note_id' => $dn->id,
            'product_type_id' => $prodType->id,
            'origin_id' => $origin->id,
            'pack_type' => 'Bale',
            'date_of_receipt' => Carbon::now(),
            'dn_total_pack' => 1,
            'dn_gross_weight' => 50.00,
            'dn_tare_weight' => 2.00,
            'dn_netto_weight' => 48.00,
            'mrl_gross_weight' => 50.00,
            'status' => 'ACTIVE',
        ]);

        // Worker Shift 1 creates row 1
        $row1 = WeighingItem::create([
            'batch_id' => $batch->id,
            'sack_number' => 1,
            'gross_kg' => 50.00,
            'tare_kg' => 2.00,
            'netto_kg' => 48.00,
            'remark' => 'Normal',
            'created_by_user_id' => $workerShift1->id,
            'shift' => 'Shift 1',
            'group' => 'Group A',
        ]);

        // Worker Shift 2 opens weighing sheet and attempts to overwrite row 1
        Livewire::actingAs($workerShift2)
            ->test(WeighingSheet::class, ['batch_id' => $batch->id])
            ->set('items.0.gross_kg', 999.00)
            ->call('saveDraft');

        // Assert row 1 gross weight remains 50.00 (predecessor row locked)
        $this->assertDatabaseHas('weighing_items', [
            'id' => $row1->id,
            'gross_kg' => 50.00,
            'created_by_user_id' => $workerShift1->id,
        ]);
    }

    public function test_worker_cannot_fill_gross_weight_only_admin_can(): void
    {
        $admin = User::create(['name' => 'Admin Test', 'email' => 'adm@tobacco.com', 'password' => bcrypt('password'), 'role' => 'admin']);
        $worker = User::create(['name' => 'Worker 1', 'email' => 'w1_test@tobacco.com', 'password' => bcrypt('password'), 'role' => 'karyawan', 'shift' => 'Shift 1', 'group' => 'Group A']);

        $customer = Customer::create(['name' => 'PT Admin Row Test', 'code' => 'CUST-ADM']);
        $prodType = ProductType::create(['code' => 'P-ADM', 'name' => 'PAITON ADM']);
        $origin = Origin::create(['region_name' => 'PAITON ADM']);
        $dn = DeliveryNote::create(['dn_number' => 'DN-ADM-01', 'customer_id' => $customer->id, 'delivery_date' => Carbon::now()]);

        $batch = Batch::create([
            'batch_code' => 'BCH-ADM-001',
            'customer_id' => $customer->id,
            'delivery_note_id' => $dn->id,
            'product_type_id' => $prodType->id,
            'origin_id' => $origin->id,
            'pack_type' => 'Bale',
            'date_of_receipt' => Carbon::now(),
            'status' => 'OPEN',
            'created_by_user_id' => $admin->id,
        ]);

        // Pre-launch row created by Admin (e.g. tare 0.20, gross 98.60)
        $adminRow = WeighingItem::create([
            'batch_id' => $batch->id,
            'sack_number' => 1,
            'gross_kg' => 98.60,
            'tare_kg' => 0.20,
            'netto_kg' => 98.40,
            'remark' => 'Normal',
            'created_by_user_id' => $admin->id,
        ]);

        // Worker attempts to alter gross weight to 999.00 -> should be ignored (gross remains 98.60)
        Livewire::actingAs($worker)
            ->test(WeighingSheet::class, ['batch_id' => $batch->id])
            ->set('items.0.gross_kg', 999.00)
            ->set('items.0.tare_kg', 0.50)
            ->call('saveDraft');

        $this->assertDatabaseHas('weighing_items', [
            'id' => $adminRow->id,
            'gross_kg' => 98.60,
            'tare_kg' => 0.50,
            'netto_kg' => 98.10,
        ]);

        // Admin opens sheet and updates gross weight to 100.00 -> gross weight is updated
        Livewire::actingAs($admin)
            ->test(WeighingSheet::class, ['batch_id' => $batch->id])
            ->set('items.0.gross_kg', 100.00)
            ->call('saveDraft');

        $this->assertDatabaseHas('weighing_items', [
            'id' => $adminRow->id,
            'gross_kg' => 100.00,
        ]);
    }

    public function test_worker_cannot_modify_batch_launched_pre_fill_gross_weight(): void
    {
        $admin = User::create(['name' => 'Admin Launch', 'email' => 'adm_launch@tobacco.com', 'password' => bcrypt('password'), 'role' => 'admin']);
        $worker = User::create(['name' => 'Worker 1', 'email' => 'w1_launch@tobacco.com', 'password' => bcrypt('password'), 'role' => 'karyawan', 'shift' => 'Shift 1', 'group' => 'Group A']);

        $customer = Customer::create(['name' => 'PT Launch Test', 'code' => 'CUST-LCH']);
        $prodType = ProductType::create(['code' => 'P-LCH', 'name' => 'PAITON LCH']);
        $origin = Origin::create(['region_name' => 'PAITON LCH']);
        $dn = DeliveryNote::create(['dn_number' => 'DN-LCH-01', 'customer_id' => $customer->id, 'delivery_date' => Carbon::now()]);

        $batch = Batch::create([
            'batch_code' => 'BCH-LCH-001',
            'customer_id' => $customer->id,
            'delivery_note_id' => $dn->id,
            'product_type_id' => $prodType->id,
            'origin_id' => $origin->id,
            'pack_type' => 'Bale',
            'date_of_receipt' => Carbon::now(),
            'status' => 'OPEN',
            'created_by_user_id' => $admin->id,
        ]);

        // Pre-launch row created during Batch Launch by Admin with gross 76.7
        $preLaunchRow = WeighingItem::create([
            'batch_id' => $batch->id,
            'sack_number' => 1,
            'gross_kg' => 76.70,
            'tare_kg' => 0.20,
            'netto_kg' => 76.50,
            'remark' => 'MRL Pre-Launch',
            'created_by_user_id' => $admin->id,
        ]);

        // Worker opens sheet and attempts to change gross weight
        Livewire::actingAs($worker)
            ->test(WeighingSheet::class, ['batch_id' => $batch->id])
            ->set('items.0.gross_kg', 77.00)
            ->set('items.0.tare_kg', 0.30)
            ->call('saveDraft');

        // Gross weight remains 76.70, tare updated to 0.30
        $this->assertDatabaseHas('weighing_items', [
            'id' => $preLaunchRow->id,
            'gross_kg' => 76.70,
            'tare_kg' => 0.30,
        ]);
    }

    public function test_admin_can_view_and_edit_rows_created_by_karyawan(): void
    {
        $admin = User::create(['name' => 'Admin Override', 'email' => 'adm_ovr@tobacco.com', 'password' => bcrypt('password'), 'role' => 'admin']);
        $worker = User::create(['name' => 'Worker 1', 'email' => 'w1_ovr@tobacco.com', 'password' => bcrypt('password'), 'role' => 'karyawan', 'shift' => 'Shift 1', 'group' => 'Group A']);

        $customer = Customer::create(['name' => 'PT Admin Ovr Test', 'code' => 'CUST-OVR']);
        $prodType = ProductType::create(['code' => 'P-OVR', 'name' => 'PAITON OVR']);
        $origin = Origin::create(['region_name' => 'PAITON OVR']);
        $dn = DeliveryNote::create(['dn_number' => 'DN-OVR-01', 'customer_id' => $customer->id, 'delivery_date' => Carbon::now()]);

        $batch = Batch::create([
            'batch_code' => 'BCH-OVR-001',
            'customer_id' => $customer->id,
            'delivery_note_id' => $dn->id,
            'product_type_id' => $prodType->id,
            'origin_id' => $origin->id,
            'pack_type' => 'Bale',
            'date_of_receipt' => Carbon::now(),
            'status' => 'OPEN',
        ]);

        $workerRow = WeighingItem::create([
            'batch_id' => $batch->id,
            'sack_number' => 1,
            'gross_kg' => 89.20,
            'tare_kg' => 0.20,
            'netto_kg' => 89.00,
            'remark' => 'Normal',
            'created_by_user_id' => $worker->id,
            'shift' => 'Shift 1',
            'group' => 'Group A',
        ]);

        // Admin opens sheet -> row should NOT be locked for Admin
        Livewire::actingAs($admin)
            ->test(WeighingSheet::class, ['batch_id' => $batch->id])
            ->assertSet('items.0.is_locked_for_user', false)
            ->set('items.0.gross_kg', 90.00)
            ->call('saveDraft');

        $this->assertDatabaseHas('weighing_items', [
            'id' => $workerRow->id,
            'gross_kg' => 90.00,
        ]);
    }

    public function test_shift_2_worker_can_edit_untouched_pre_launch_sack_5(): void
    {
        $admin = User::create(['name' => 'Admin Test', 'email' => 'adm_multi@tobacco.com', 'password' => bcrypt('password'), 'role' => 'admin']);
        $workerShift1 = User::create(['name' => 'Worker 1', 'email' => 'w1_multi@tobacco.com', 'password' => bcrypt('password'), 'role' => 'karyawan', 'shift' => 'Shift 1', 'group' => 'Group A']);
        $workerShift2 = User::create(['name' => 'Worker 2', 'email' => 'w2_multi@tobacco.com', 'password' => bcrypt('password'), 'role' => 'karyawan', 'shift' => 'Shift 2', 'group' => 'Group B']);

        $customer = Customer::create(['name' => 'PT Multi Shift Test', 'code' => 'CUST-MULTI']);
        $prodType = ProductType::create(['code' => 'P-MULTI', 'name' => 'PAITON MULTI']);
        $origin = Origin::create(['region_name' => 'PAITON MULTI']);
        $dn = DeliveryNote::create(['dn_number' => 'DN-MULTI-01', 'customer_id' => $customer->id, 'delivery_date' => Carbon::now()]);

        $batch = Batch::create([
            'batch_code' => 'BCH-MULTI-001',
            'customer_id' => $customer->id,
            'delivery_note_id' => $dn->id,
            'product_type_id' => $prodType->id,
            'origin_id' => $origin->id,
            'pack_type' => 'Bale',
            'date_of_receipt' => Carbon::now(),
            'status' => 'OPEN',
            'created_by_user_id' => $admin->id,
        ]);

        // Pre-launch rows created during launch (Sacks 1..5)
        $row1 = WeighingItem::create(['batch_id' => $batch->id, 'sack_number' => 1, 'gross_kg' => 99.75, 'tare_kg' => 0.50, 'netto_kg' => 99.25, 'remark' => 'MRL Pre-Launch', 'created_by_user_id' => $admin->id]);
        $row5 = WeighingItem::create(['batch_id' => $batch->id, 'sack_number' => 5, 'gross_kg' => 99.96, 'tare_kg' => 0.20, 'netto_kg' => 99.76, 'remark' => 'MRL Pre-Launch', 'created_by_user_id' => $admin->id]);

        // Worker Shift 1 opens sheet and edits Row 1 (tare -> 0.60), but DOES NOT touch Row 5
        Livewire::actingAs($workerShift1)
            ->test(WeighingSheet::class, ['batch_id' => $batch->id])
            ->set('items.0.tare_kg', 0.60)
            ->call('saveDraft');

        // Worker Shift 2 opens sheet
        // Row 1 should be locked for Shift 2, but Row 5 MUST be unlocked for Shift 2!
        Livewire::actingAs($workerShift2)
            ->test(WeighingSheet::class, ['batch_id' => $batch->id])
            ->assertSet('items.0.is_locked_for_user', true)
            ->assertSet('items.1.is_locked_for_user', false)
            ->set('items.1.tare_kg', 0.40)
            ->call('saveDraft');

        $this->assertDatabaseHas('weighing_items', [
            'id' => $row5->id,
            'tare_kg' => 0.40,
            'created_by_user_id' => $workerShift2->id,
        ]);
    }
}

