<?php

namespace Tests\Feature;

use App\Livewire\Customer\CustomerDashboard;
use App\Models\Batch;
use App\Models\BatchOrigin;
use App\Models\Customer;
use App\Models\DeliveryNote;
use App\Models\Origin;
use App\Models\ProductType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerPortalRedesignTest extends TestCase
{
    use RefreshDatabase;

    protected Customer $customer1;
    protected Customer $customer2;
    protected User $customerUser1;
    protected User $customerUser2;
    protected ProductType $productType;
    protected Origin $originPaiton;
    protected Origin $originLombok;
    protected Batch $batch1;
    protected Batch $batch2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer1 = Customer::create(['name' => 'PT Falih Nur Gemilang', 'code' => 'FNG']);
        $this->customer2 = Customer::create(['name' => 'PT Other Customer', 'code' => 'OTH']);

        $this->customerUser1 = User::create([
            'name' => 'Customer User 1',
            'email' => 'customer1@tobacco.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'customer_id' => $this->customer1->id,
        ]);

        $this->customerUser2 = User::create([
            'name' => 'Customer User 2',
            'email' => 'customer2@tobacco.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'customer_id' => $this->customer2->id,
        ]);

        $this->productType = ProductType::create(['code' => 'RAJANGAN', 'name' => 'RAJANGAN']);
        $this->originPaiton = Origin::create(['region_name' => 'PAITON P10T5']);
        $this->originLombok = Origin::create(['region_name' => 'LOMBOK P9K5']);

        $dn1 = DeliveryNote::create([
            'dn_number' => 'SJ/FNG/VT/23/07/26',
            'customer_id' => $this->customer1->id,
            'delivery_date' => Carbon::parse('2026-07-23'),
        ]);

        $this->batch1 = Batch::create([
            'batch_code' => 'BCH-2026-0025',
            'customer_id' => $this->customer1->id,
            'delivery_note_id' => $dn1->id,
            'product_type_id' => $this->productType->id,
            'origin_id' => $this->originPaiton->id,
            'pack_type' => 'Bale',
            'date_of_receipt' => Carbon::parse('2026-07-23'),
            'dn_total_pack' => 65,
            'dn_gross_weight' => 3247.60,
            'dn_tare_weight' => 73.80,
            'dn_netto_weight' => 3173.80,
            'mrl_total_pack' => 65,
            'mrl_gross_weight' => 3251.90,
            'mrl_tare_weight' => 78.10,
            'mrl_netto_weight' => 3173.80,
            'discrepancy_dn_vs_mrl_kg' => 4.30,
            'separation_product_kg' => 2442.50,
            'separation_bits_stem_kg' => 589.22,
            'separation_dust_kg' => 58.70,
            'separation_waste_kg' => 20.28,
            'yield_product_pct' => 76.96,
            'yield_bits_stem_pct' => 18.56,
            'yield_dust_pct' => 1.85,
            'yield_waste_pct' => 0.63,
            'status' => 'CLOSED',
            'supervisor_approval_status' => 'APPROVED',
            'supervisor_approved_at' => now(),
        ]);

        BatchOrigin::create([
            'batch_id' => $this->batch1->id,
            'origin_id' => $this->originPaiton->id,
            'allocated_kg' => 1818.00,
            'processed_kg' => 1818.00,
            'remaining_kg' => 0,
        ]);

        BatchOrigin::create([
            'batch_id' => $this->batch1->id,
            'origin_id' => $this->originLombok->id,
            'allocated_kg' => 1433.90,
            'processed_kg' => 1433.90,
            'remaining_kg' => 0,
        ]);

        $dn2 = DeliveryNote::create([
            'dn_number' => 'DN-OTH-001',
            'customer_id' => $this->customer2->id,
            'delivery_date' => Carbon::parse('2026-07-24'),
        ]);

        $this->batch2 = Batch::create([
            'batch_code' => 'BCH-2026-0099',
            'customer_id' => $this->customer2->id,
            'delivery_note_id' => $dn2->id,
            'product_type_id' => $this->productType->id,
            'origin_id' => $this->originPaiton->id,
            'pack_type' => 'Bale',
            'date_of_receipt' => Carbon::parse('2026-07-24'),
            'dn_gross_weight' => 1000.00,
            'mrl_gross_weight' => 1000.00,
            'mrl_netto_weight' => 950.00,
            'separation_product_kg' => 700.00,
            'yield_product_pct' => 73.68,
            'status' => 'CLOSED',
            'supervisor_approval_status' => 'APPROVED',
            'supervisor_approved_at' => now(),
        ]);
    }

    public function test_customer_can_render_portal_with_all_tabs_and_navigation(): void
    {
        Livewire::actingAs($this->customerUser1)
            ->test(CustomerDashboard::class)
            ->assertSee('TOBACCO SEPARATION')
            ->assertSee('CUSTOMER PORTAL')
            ->assertSee('Batch Overview')
            ->assertSee('Historical Analytics')
            ->assertSee('Yield Cost Calculator')
            ->assertSee('BCH-2026-0025')
            ->call('setTab', 'historical_analytics')
            ->assertSet('activeTab', 'historical_analytics')
            ->call('setTab', 'yield_calculator')
            ->assertSet('activeTab', 'yield_calculator');
    }

    public function test_batch_overview_displays_correct_kpis_and_reconciliation(): void
    {
        Livewire::actingAs($this->customerUser1)
            ->test(CustomerDashboard::class)
            ->assertSee('3,247.60') // DN Gross
            ->assertSee('3,251.90') // MRL Gross
            ->assertSee('3,173.80') // MRL Netto
            ->assertSee('2,442.50') // Product Output
            ->assertSee('76.96%')   // Product Yield
            ->assertSee('DN Received')
            ->assertSee('DN Shipped')
            ->assertSee('Delivery Note Reconciliation Pipeline')
            ->assertSee('Material Receiving Reconciliation')
            ->assertSee('Receiving Confirmation Status')
            ->assertSee('Separation Result by Origin')
            ->assertSee('Process Material Balance');
    }

    public function test_customer_tenant_isolation_in_portal(): void
    {
        // Customer 1 should NOT see Customer 2's batch
        Livewire::actingAs($this->customerUser1)
            ->test(CustomerDashboard::class)
            ->assertSee('BCH-2026-0025')
            ->assertDontSee('BCH-2026-0099');

        // Customer 2 should see only their batch
        Livewire::actingAs($this->customerUser2)
            ->test(CustomerDashboard::class)
            ->assertSee('BCH-2026-0099')
            ->assertDontSee('BCH-2026-0025');
    }

    public function test_rest_api_endpoints_work_with_tenant_isolation(): void
    {
        // 1. Index Batches API
        $response = $this->actingAs($this->customerUser1)->getJson('/api/customer/batches');
        $response->assertStatus(200);
        $response->assertJsonFragment(['batchCode' => 'BCH-2026-0025']);
        $response->assertJsonMissing(['batchCode' => 'BCH-2026-0099']);

        // 2. Show Batch API
        $responseDetail = $this->actingAs($this->customerUser1)->getJson("/api/customer/batches/{$this->batch1->id}");
        $responseDetail->assertStatus(200);
        $responseDetail->assertJsonPath('data.batchCode', 'BCH-2026-0025');
        $responseDetail->assertJsonPath('data.receivingKpi.productOutputKg', 2442.50);

        // 3. Receiving Reconciliation API
        $responseRec = $this->actingAs($this->customerUser1)->getJson("/api/customer/batches/{$this->batch1->id}/receiving-reconciliation");
        $responseRec->assertStatus(200);
        $responseRec->assertJsonPath('data.differenceKg', 4.30);

        // 4. Process Balance API
        $responseBal = $this->actingAs($this->customerUser1)->getJson("/api/customer/batches/{$this->batch1->id}/process-balance");
        $responseBal->assertStatus(200);
        $responseBal->assertJsonPath('data.productQtyKg', 2442.50);

        // 5. Performance Summary API
        $responseSum = $this->actingAs($this->customerUser1)->getJson('/api/customer/performance/summary');
        $responseSum->assertStatus(200);
        $responseSum->assertJsonPath('data.totalBatches', 1);

        // 6. Performance Trend API
        $responseTrend = $this->actingAs($this->customerUser1)->getJson('/api/customer/performance/trend');
        $responseTrend->assertStatus(200);
        $responseTrend->assertJsonStructure(['status', 'data' => ['weightedAveragePct', 'series']]);
    }

    public function test_historical_separation_performance_distinguishes_origin_and_origin_code_and_provides_chart_metadata(): void
    {
        // Test helper resolution
        $resolvedLombok24 = CustomerDashboard::resolveOriginAndCode("Lombok'24");
        $this->assertEquals('Lombok', $resolvedLombok24['origin']);
        $this->assertEquals("Lombok'24", $resolvedLombok24['originCode']);

        $resolvedPaiton = CustomerDashboard::resolveOriginAndCode('PAITON P10T5');
        $this->assertEquals('Paiton', $resolvedPaiton['origin']);
        $this->assertEquals('P10T5', $resolvedPaiton['originCode']);

        $resolvedExplicit = CustomerDashboard::resolveOriginAndCode('Lombok', 'FN602');
        $this->assertEquals('Lombok', $resolvedExplicit['origin']);
        $this->assertEquals('FN602', $resolvedExplicit['originCode']);

        // Test Livewire component renders Historical tab with origin & origin code
        Livewire::actingAs($this->customerUser1)
            ->test(CustomerDashboard::class)
            ->call('setTab', 'historical_analytics')
            ->assertSet('activeTab', 'historical_analytics')
            ->assertSee('Historical Product Yield Trend')
            ->assertSee('Weighted Yield by Origin')
            ->assertSee('Origin Code Performance')
            ->assertSee('Paiton')
            ->assertSee('P10T5')
            ->set('histBaseOrigin', 'Paiton')
            ->assertSet('histBaseOrigin', 'Paiton')
            ->call('resetHistoricalFilters')
            ->assertSet('histBaseOrigin', '')
            ->assertSet('histOriginCode', '');
    }
}
