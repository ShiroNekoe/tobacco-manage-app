<?php

namespace Tests\Feature;

use App\Livewire\Karyawan\WeighingSheet;
use App\Models\Batch;
use App\Models\BatchInterimSeparation;
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

class WorkerSacksProcessedCountTest extends TestCase
{
    use RefreshDatabase;

    public function test_interim_report_sacks_processed_count_is_specific_to_each_worker(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'name' => 'Admin']);
        $worker1 = User::factory()->create(['role' => 'karyawan', 'name' => 'karyawan1', 'shift' => 'Shift 1', 'group' => 'Group A']);
        $worker2 = User::factory()->create(['role' => 'karyawan', 'name' => 'karyawan2', 'shift' => 'Shift 2', 'group' => 'Group A']);

        $customer = Customer::create(['name' => 'PT Count Test', 'code' => 'CUST-CNT']);
        $prodType = ProductType::create(['code' => 'P-CNT', 'name' => 'PAITON CNT']);
        $origin = Origin::create(['region_name' => 'PAITON CNT']);
        $dn = DeliveryNote::create(['dn_number' => 'DN-CNT-01', 'customer_id' => $customer->id, 'delivery_date' => Carbon::now()]);

        $batch = Batch::create([
            'batch_code' => 'BCH-CNT-001',
            'customer_id' => $customer->id,
            'delivery_note_id' => $dn->id,
            'product_type_id' => $prodType->id,
            'origin_id' => $origin->id,
            'pack_type' => 'Bale',
            'date_of_receipt' => Carbon::now(),
            'status' => 'OPEN',
            'mrl_netto_weight' => 500.00,
            'created_by_user_id' => $admin->id,
        ]);

        // Pre-launch rows created during launch (Sacks 1..5)
        for ($i = 1; $i <= 5; $i++) {
            WeighingItem::create([
                'batch_id' => $batch->id,
                'sack_number' => $i,
                'gross_kg' => 90.0 + $i,
                'tare_kg' => 0.5,
                'netto_kg' => 89.5 + $i,
                'remark' => 'MRL Pre-Launch',
                'created_by_user_id' => $admin->id,
            ]);
        }

        // 1. Worker 1 (Shift 1) edits sacks #1, #2, #3, #4 (4 sacks total) and sets separation product sacks
        Livewire::actingAs($worker1)
            ->test(WeighingSheet::class, ['batch_id' => $batch->id])
            ->set('items.0.gross_kg', 95.0)
            ->set('items.1.gross_kg', 96.0)
            ->set('items.2.gross_kg', 97.0)
            ->set('items.3.gross_kg', 98.0)
            ->set('p1_product_sack', 4)
            ->call('submitPauseAndInterimReport')
            ->assertHasNoErrors();

        $reportWorker1 = BatchInterimSeparation::where('user_id', $worker1->id)->first();
        $this->assertNotNull($reportWorker1);
        $this->assertEquals(4, $reportWorker1->sacks_processed_count);

        // 2. Worker 2 (Shift 2) only edits sack #5 (1 sack total)
        Livewire::actingAs($worker2)
            ->test(WeighingSheet::class, ['batch_id' => $batch->id])
            ->set('items.4.gross_kg', 100.0)
            ->set('p1_product_sack', 5)
            ->call('submitPauseAndInterimReport')
            ->assertHasNoErrors();

        $reportWorker2 = BatchInterimSeparation::where('user_id', $worker2->id)->first();
        $this->assertNotNull($reportWorker2);
        $this->assertEquals(1, $reportWorker2->sacks_processed_count);
    }
}
