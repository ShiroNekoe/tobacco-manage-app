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
}
