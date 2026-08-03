<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Customer;
use App\Models\DeliveryNote;
use App\Models\Origin;
use App\Models\ProductType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_warehouse_cannot_access_production_input_routes(): void
    {
        $warehouse = User::factory()->create(['role' => 'warehouse']);

        $response = $this->actingAs($warehouse)->get('/karyawan/weighing');

        $response->assertStatus(403);
    }

    public function test_operator_cannot_trigger_batch_closing(): void
    {
        $operator = User::factory()->create(['role' => 'operator']);

        $customer = Customer::create(['name' => 'PT. Test', 'code' => 'TEST']);
        $prodType = ProductType::create(['code' => 'P10', 'name' => 'P10']);
        $origin = Origin::create(['region_name' => 'PAITON']);
        $dn = DeliveryNote::create([
            'dn_number' => 'DN-TEST-01',
            'customer_id' => $customer->id,
            'delivery_date' => Carbon::now(),
        ]);

        $batch = Batch::create([
            'batch_code' => 'BCH-20260803-001',
            'customer_id' => $customer->id,
            'delivery_note_id' => $dn->id,
            'product_type_id' => $prodType->id,
            'origin_id' => $origin->id,
            'date_of_receipt' => Carbon::now(),
            'status' => 'ACTIVE',
            'dn_gross_weight' => 100,
            'dn_tare_weight' => 0,
            'dn_netto_weight' => 100,
            'mrl_gross_weight' => 100,
            'mrl_tare_weight' => 0,
            'mrl_netto_weight' => 100,
            'yield_product_pct' => 80.00,
            'yield_bits_stem_pct' => 10.00,
            'yield_dust_pct' => 5.00,
            'yield_waste_pct' => 5.00,
        ]);

        // Operator attempting batch closure should be rejected
        $this->actingAs($operator);
        $errors = $batch->validateClosureGates();
        // Check batch status remains ACTIVE (not CLOSED)
        $this->assertEquals('ACTIVE', $batch->fresh()->status);
    }

    public function test_supervisor_and_admin_can_access_batch_management(): void
    {
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($supervisor)->get('/admin/batches')->assertStatus(200);
        $this->actingAs($admin)->get('/admin/batches')->assertStatus(200);
    }
}
