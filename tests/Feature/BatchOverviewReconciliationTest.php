<?php

namespace Tests\Feature;

use App\Livewire\Customer\CustomerDashboard;
use App\Models\Batch;
use App\Models\BatchOrigin;
use App\Models\Customer;
use App\Models\DeliveryNote;
use App\Models\DnShipment;
use App\Models\DnShipmentItem;
use App\Models\Origin;
use App\Models\ProductType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BatchOverviewReconciliationTest extends TestCase
{
    use RefreshDatabase;

    protected Customer $customer;
    protected User $customerUser;
    protected Origin $originPaiton;
    protected Origin $originLombok;
    protected ProductType $productType;
    protected Batch $batch1;
    protected Batch $batch2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = Customer::create([
            'code' => 'CUST-FNG',
            'name' => 'PT Falih Nur Gemilang',
        ]);

        $this->customerUser = User::factory()->create([
            'role' => 'customer',
            'customer_id' => $this->customer->id,
        ]);

        $this->originPaiton = Origin::create(['region_name' => 'PAITON', 'code' => 'P10T5']);
        $this->originLombok = Origin::create(['region_name' => 'LOMBOK', 'code' => 'P9K5']);

        $this->productType = ProductType::create([
            'code' => 'PT-01',
            'name' => 'PAITON P10T5',
        ]);

        $dn1 = DeliveryNote::create([
            'dn_number' => 'DN-2026-0001',
            'delivery_date' => '2026-08-01',
            'customer_id' => $this->customer->id,
            'origin_id' => $this->originPaiton->id,
            'total_sack' => 50,
            'gross_weight' => 2500.00,
            'net_weight' => 2450.00,
        ]);

        $this->batch1 = Batch::create([
            'batch_code' => 'BCH-2026-0001',
            'customer_id' => $this->customer->id,
            'origin_id' => $this->originPaiton->id,
            'product_type_id' => $this->productType->id,
            'delivery_note_id' => $dn1->id,
            'date_of_receipt' => '2026-08-01',
            'dn_gross_weight' => 2500.00,
            'dn_netto_weight' => 2450.00,
            'dn_total_pack' => 50,
            'mrl_gross_weight' => 2504.00,
            'mrl_tare_weight' => 35.00,
            'mrl_netto_weight' => 2469.00,
            'mrl_total_pack' => 50,
            'separation_product_kg' => 1900.00,
            'separation_bits_stem_kg' => 480.00,
            'separation_dust_kg' => 45.00,
            'separation_waste_kg' => 44.00,
            'yield_product_pct' => 76.95,
            'yield_bits_stem_pct' => 19.44,
            'yield_dust_pct' => 1.82,
            'yield_waste_pct' => 1.78,
            'supervisor_approval_status' => Batch::APPROVAL_APPROVED,
        ]);

        $dn2 = DeliveryNote::create([
            'dn_number' => 'DN-2026-0002',
            'delivery_date' => '2026-08-05',
            'customer_id' => $this->customer->id,
            'origin_id' => $this->originLombok->id,
            'total_sack' => 60,
            'gross_weight' => 3000.00,
            'net_weight' => 2940.00,
        ]);

        $this->batch2 = Batch::create([
            'batch_code' => 'BCH-2026-0002',
            'customer_id' => $this->customer->id,
            'origin_id' => $this->originLombok->id,
            'product_type_id' => $this->productType->id,
            'delivery_note_id' => $dn2->id,
            'date_of_receipt' => '2026-08-05',
            'dn_gross_weight' => 3000.00,
            'dn_netto_weight' => 2940.00,
            'dn_total_pack' => 60,
            'mrl_gross_weight' => 3005.00,
            'mrl_tare_weight' => 42.00,
            'mrl_netto_weight' => 2963.00,
            'mrl_total_pack' => 60,
            'separation_product_kg' => 2300.00,
            'separation_bits_stem_kg' => 550.00,
            'separation_dust_kg' => 55.00,
            'separation_waste_kg' => 58.00,
            'yield_product_pct' => 77.62,
            'supervisor_approval_status' => Batch::APPROVAL_APPROVED,
        ]);

        // Multi-origin batch origins for batch2
        BatchOrigin::create([
            'batch_id' => $this->batch2->id,
            'origin_id' => $this->originPaiton->id,
            'allocated_kg' => 1800.00,
        ]);
        BatchOrigin::create([
            'batch_id' => $this->batch2->id,
            'origin_id' => $this->originLombok->id,
            'allocated_kg' => 1200.00,
        ]);
    }

    public function test_batch_overview_loads_latest_approved_batch_by_default(): void
    {
        Livewire::actingAs($this->customerUser)
            ->test(CustomerDashboard::class)
            ->assertSee('Batch Overview', false)
            ->assertSee('Delivery Note Reconciliation Pipeline', false)
            ->assertSee('Material Receiving Reconciliation', false)
            ->assertSee('DN Receiver', false)
            ->assertSee('Outbound DN Shipment Details (DN Shipped)', false)
            ->assertSee('Receiving Confirmation Status', false)
            ->assertSee('Separation Result by Origin', false)
            ->assertSee('Process Material Balance', false)
            ->assertSet('selectedBatchId', $this->batch2->id);
    }

    public function test_customer_can_navigate_between_batches(): void
    {
        Livewire::actingAs($this->customerUser)
            ->test(CustomerDashboard::class)
            ->assertSet('selectedBatchId', $this->batch2->id)
            ->call('previousBatch')
            ->assertSet('selectedBatchId', $this->batch1->id)
            ->call('nextBatch')
            ->assertSet('selectedBatchId', $this->batch2->id)
            ->call('selectBatch', $this->batch1->id)
            ->assertSet('selectedBatchId', $this->batch1->id);
    }

    public function test_batch_overview_displays_dn_received_and_dn_shipped_accurately(): void
    {
        // Create an outbound DN Shipment for batch 2
        $shipment = DnShipment::create([
            'dn_number' => 'DN-OUT-2026-0099',
            'customer_id' => $this->customer->id,
            'product_type_id' => $this->productType->id,
            'shipment_date' => '2026-08-06',
            'vehicle_number' => 'L 9999 XX',
            'driver_name' => 'Slamet',
            'status' => 'Shipped',
            'total_sacks' => 46,
            'total_gross_kg' => 2332.20,
            'total_tare_kg' => 32.20,
            'total_netto_kg' => 2300.00,
        ]);

        DnShipmentItem::create([
            'dn_shipment_id' => $shipment->id,
            'batch_id' => $this->batch2->id,
            'batch_code' => $this->batch2->batch_code,
            'origin' => 'Lombok',
            'origin_code' => "Lombok'24",
            'material_type' => 'Product',
            'standard_sack_count' => 46,
            'standard_gross_per_sack' => 50.70,
            'standard_tare_per_sack' => 0.70,
            'standard_netto_per_sack' => 50.00,
            'total_sacks' => 46,
            'total_gross_kg' => 2332.20,
            'total_tare_kg' => 32.20,
            'total_netto_kg' => 2300.00,
        ]);

        Livewire::actingAs($this->customerUser)
            ->test(CustomerDashboard::class)
            ->set('selectedBatchId', $this->batch2->id)
            ->assertSee('DN-2026-0002')
            ->assertSee('DN-OUT-2026-0099')
            ->assertSee('DN Receiver')
            ->assertSee('Outbound DN Shipment Details (DN Shipped)')
            ->assertSee('46 Sacks')
            ->assertSee('2,300.00 kg');
    }

    public function test_customer_can_approve_shipment_from_overview_pipeline(): void
    {
        $shipment = DnShipment::create([
            'dn_number' => 'DN-OUT-APPROVE-TEST',
            'customer_id' => $this->customer->id,
            'product_type_id' => $this->productType->id,
            'shipment_date' => '2026-08-06',
            'vehicle_number' => 'N 1234 YY',
            'driver_name' => 'Rahmat',
            'status' => 'Shipped',
            'total_sacks' => 10,
            'total_gross_kg' => 507.00,
            'total_tare_kg' => 7.00,
            'total_netto_kg' => 500.00,
        ]);

        DnShipmentItem::create([
            'dn_shipment_id' => $shipment->id,
            'batch_id' => $this->batch1->id,
            'batch_code' => $this->batch1->batch_code,
            'origin' => 'Paiton',
            'origin_code' => 'P10T5',
            'material_type' => 'Product',
            'standard_sack_count' => 10,
            'standard_gross_per_sack' => 50.70,
            'standard_tare_per_sack' => 0.70,
            'standard_netto_per_sack' => 50.00,
            'total_sacks' => 10,
            'total_gross_kg' => 507.00,
            'total_tare_kg' => 7.00,
            'total_netto_kg' => 500.00,
        ]);

        Livewire::actingAs($this->customerUser)
            ->test(CustomerDashboard::class)
            ->set('selectedBatchId', $this->batch1->id)
            ->call('openApprovalModal', $shipment->id)
            ->assertSet('showApprovalModal', true)
            ->assertSet('approvingShipmentId', $shipment->id)
            ->set('approvalNote', 'Diterima dengan baik dan sesuai.')
            ->call('approveShipment', $shipment->id)
            ->assertSet('showApprovalModal', false);

        $this->assertDatabaseHas('dn_shipments', [
            'id' => $shipment->id,
            'status' => 'Approved',
            'customer_approved_by_user_id' => $this->customerUser->id,
            'customer_approval_note' => 'Diterima dengan baik dan sesuai.',
        ]);
    }

    public function test_customer_can_search_older_batch_manually(): void
    {
        Livewire::actingAs($this->customerUser)
            ->test(CustomerDashboard::class)
            ->set('selectedBatchId', $this->batch2->id)
            ->set('batchSearch', 'BCH-2026-0001')
            ->assertSet('selectedBatchId', $this->batch1->id)
            ->assertSee('BCH-2026-0001')
            ->call('clearBatchSearch')
            ->assertSet('batchSearch', '');
    }

    public function test_batch_dropdown_limits_to_latest_10_batches(): void
    {
        // Create 12 batches
        for ($i = 3; $i <= 14; $i++) {
            Batch::create([
                'batch_code' => sprintf('BCH-2026-%04d', $i),
                'customer_id' => $this->customer->id,
                'delivery_note_id' => $this->batch1->delivery_note_id,
                'origin_id' => $this->originPaiton->id,
                'product_type_id' => $this->productType->id,
                'date_of_receipt' => '2026-08-08',
                'dn_gross_weight' => 2500.00,
                'dn_netto_weight' => 2450.00,
                'dn_total_pack' => 50,
                'mrl_gross_weight' => 2500.00,
                'mrl_netto_weight' => 2450.00,
                'separation_product_kg' => 1900.00,
                'yield_product_pct' => 76.00,
                'supervisor_approval_status' => Batch::APPROVAL_APPROVED,
            ]);
        }

        $component = Livewire::actingAs($this->customerUser)
            ->test(CustomerDashboard::class);

        $overviewBatches = $component->viewData('overviewBatches');
        $this->assertLessThanOrEqual(11, $overviewBatches->count());
        $this->assertTrue($overviewBatches->contains('batch_code', 'BCH-2026-0014'));
    }
}
