<?php

namespace Tests\Feature;

use App\Livewire\Admin\DnShipmentManagement;
use App\Models\Customer;
use App\Models\DnShipment;
use App\Models\DnShipmentItem;
use App\Models\ProductType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DnShipmentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $karyawan;
    protected Customer $customer;
    protected ProductType $productType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'password' => bcrypt('password'),
        ]);

        $this->karyawan = User::factory()->create([
            'role' => 'karyawan',
            'password' => bcrypt('password'),
        ]);

        $this->customer = Customer::create([
            'name' => 'PT Falih Nur Gemilang',
            'code' => 'FNG',
            'address' => 'Jl. Industri Tembakau No. 88, Jawa Timur',
        ]);

        $this->productType = ProductType::create([
            'code' => 'FC',
            'name' => 'Flue-Cured Virginia',
        ]);
    }

    public function test_admin_can_access_dn_shipment_page(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.dn-shipments'))
            ->assertStatus(200)
            ->assertSee('DN SHIPMENT (SURAT JALAN PENGIRIMAN)')
            ->assertSee('Buat DN Pengiriman Baru');
    }

    public function test_karyawan_cannot_access_dn_shipment_page(): void
    {
        $this->actingAs($this->karyawan)
            ->get(route('admin.dn-shipments'))
            ->assertStatus(403);
    }

    public function test_admin_can_create_dn_shipment_with_single_lot_and_automatic_sack_calculation(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(DnShipmentManagement::class)
            ->call('openCreateModal')
            ->set('dn_number', 'DNS-2026-0001')
            ->set('shipment_date', '2026-08-11')
            ->set('customer_id', $this->customer->id)
            ->set('product_type_id', $this->productType->id)
            ->set('vehicle_number', 'N 1234 AB')
            ->set('driver_name', 'Budi Santoso')
            ->set('destination', 'Gudang Pusat Surabaya')
            ->set('items.0.origin', 'Lombok')
            ->set('items.0.origin_code', "Lombok'24")
            ->set('items.0.standard_sack_count', 10)
            ->set('items.0.standard_gross_per_sack', 50.70)
            ->set('items.0.standard_tare_per_sack', 0.70)
            ->set('items.0.has_remnant', false)
            ->call('saveShipment')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('dn_shipments', [
            'dn_number' => 'DNS-2026-0001',
            'total_sacks' => 10,
            'total_gross_kg' => 507.00,
            'total_tare_kg' => 7.00,
            'total_netto_kg' => 500.00,
        ]);

        $this->assertDatabaseHas('dn_shipment_items', [
            'origin' => 'Lombok',
            'origin_code' => "Lombok'24",
            'standard_sack_count' => 10,
            'total_sacks' => 10,
            'total_netto_kg' => 500.00,
        ]);
    }

    public function test_admin_can_create_dn_shipment_with_remnant_sack(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(DnShipmentManagement::class)
            ->call('openCreateModal')
            ->set('dn_number', 'DNS-2026-0002')
            ->set('shipment_date', '2026-08-11')
            ->set('customer_id', $this->customer->id)
            ->set('items.0.origin', 'Paiton')
            ->set('items.0.origin_code', 'P10T5')
            ->set('items.0.standard_sack_count', 10)
            ->set('items.0.standard_gross_per_sack', 50.70)
            ->set('items.0.standard_tare_per_sack', 0.70)
            ->set('items.0.has_remnant', true)
            ->set('items.0.remnant_gross_kg', 24.50)
            ->set('items.0.remnant_tare_kg', 0.70)
            ->call('saveShipment')
            ->assertHasNoErrors();

        // 10 sacks * 50.70 = 507 + 24.50 = 531.50 Gross
        // 10 sacks * 0.70 = 7.00 + 0.70 = 7.70 Tare
        // 10 sacks * 50.00 = 500 + 23.80 = 523.80 Netto
        // 10 standard + 1 remnant = 11 total sacks
        $this->assertDatabaseHas('dn_shipments', [
            'dn_number' => 'DNS-2026-0002',
            'total_sacks' => 11,
            'total_gross_kg' => 531.50,
            'total_tare_kg' => 7.70,
            'total_netto_kg' => 523.80,
        ]);

        $this->assertDatabaseHas('dn_shipment_items', [
            'origin' => 'Paiton',
            'origin_code' => 'P10T5',
            'standard_sack_count' => 10,
            'has_remnant' => true,
            'remnant_gross_kg' => 24.50,
            'remnant_netto_kg' => 23.80,
            'total_sacks' => 11,
            'total_netto_kg' => 523.80,
        ]);
    }

    public function test_admin_can_add_multiple_lots_to_dn_shipment(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(DnShipmentManagement::class)
            ->call('openCreateModal')
            ->set('dn_number', 'DNS-2026-0003')
            ->set('shipment_date', '2026-08-11')
            ->set('customer_id', $this->customer->id)
            // Lot 1: Lombok (10 sacks @ 50kg net)
            ->set('items.0.origin', 'Lombok')
            ->set('items.0.origin_code', "Lombok'24")
            ->set('items.0.standard_sack_count', 10)
            ->set('items.0.standard_gross_per_sack', 50.70)
            ->set('items.0.standard_tare_per_sack', 0.70)
            ->set('items.0.has_remnant', false)
            // Add Lot 2: Madura (5 sacks @ 50kg net + remnant 15.2kg net)
            ->call('addItem')
            ->set('items.1.origin', 'Madura')
            ->set('items.1.origin_code', 'M24A')
            ->set('items.1.standard_sack_count', 5)
            ->set('items.1.standard_gross_per_sack', 50.70)
            ->set('items.1.standard_tare_per_sack', 0.70)
            ->set('items.1.has_remnant', true)
            ->set('items.1.remnant_gross_kg', 15.90)
            ->set('items.1.remnant_tare_kg', 0.70)
            ->call('saveShipment')
            ->assertHasNoErrors();

        // Lot 1: 10 sacks, Netto 500.00 kg
        // Lot 2: 5 sacks * 50 + 15.20 = 265.20 kg Netto (6 sacks total)
        // Grand total: 16 sacks, 765.20 kg Netto
        $this->assertDatabaseHas('dn_shipments', [
            'dn_number' => 'DNS-2026-0003',
            'total_sacks' => 16,
            'total_netto_kg' => 765.20,
        ]);

        $this->assertDatabaseCount('dn_shipment_items', 2);
    }

    public function test_admin_can_preview_and_download_dn_shipment_pdf(): void
    {
        $this->actingAs($this->admin);

        $shipment = DnShipment::create([
            'dn_number' => 'DNS-2026-TEST-PDF',
            'shipment_date' => '2026-08-11',
            'customer_id' => $this->customer->id,
            'total_sacks' => 10,
            'total_gross_kg' => 507.00,
            'total_tare_kg' => 7.00,
            'total_netto_kg' => 500.00,
            'status' => 'Shipped',
        ]);

        DnShipmentItem::create([
            'dn_shipment_id' => $shipment->id,
            'item_no' => 1,
            'origin' => 'Lombok',
            'origin_code' => "Lombok'24",
            'standard_sack_count' => 10,
            'standard_gross_per_sack' => 50.70,
            'standard_tare_per_sack' => 0.70,
            'standard_netto_per_sack' => 50.00,
            'has_remnant' => false,
            'total_sacks' => 10,
            'total_gross_kg' => 507.00,
            'total_tare_kg' => 7.00,
            'total_netto_kg' => 500.00,
        ]);

        // Test HTML preview route
        $this->get(route('dn-shipments.preview', $shipment->id))
            ->assertStatus(200)
            ->assertSee('SURAT JALAN PENGIRIMAN')
            ->assertSee('DNS-2026-TEST-PDF')
            ->assertSee("Lombok'24");

        // Test PDF download route
        $response = $this->get(route('dn-shipments.pdf', $shipment->id));
        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));
    }

    public function test_admin_can_select_batch_to_auto_populate_lot_and_origin(): void
    {
        $this->actingAs($this->admin);

        $origin = \App\Models\Origin::create(['region_name' => "Lombok'24"]);

        $dn = \App\Models\DeliveryNote::create([
            'dn_number' => 'DN-INBOUND-001',
            'customer_id' => $this->customer->id,
            'delivery_date' => '2026-08-11',
            'status' => 'received',
        ]);

        $batch = \App\Models\Batch::create([
            'batch_code' => 'BCH-2026-TEST',
            'delivery_note_id' => $dn->id,
            'date_of_receipt' => '2026-08-11',
            'customer_id' => $this->customer->id,
            'product_type_id' => $this->productType->id,
            'origin_id' => $origin->id,
            'separation_product_sack' => 25,
            'product_tare_per_sack' => 0.70,
            'separation_product_remnant_kg' => 12.30,
            'separation_product_kg' => 1262.30,
            'status' => 'approved',
        ]);

        Livewire::test(DnShipmentManagement::class)
            ->call('openCreateModal')
            ->call('selectBatchForLot', 0, $batch->id)
            ->assertSet('items.0.batch_id', $batch->id)
            ->assertSet('items.0.batch_code', 'BCH-2026-TEST')
            ->assertSet('items.0.origin', 'Lombok')
            ->assertSet('items.0.origin_code', "Lombok'24")
            ->assertSet('items.0.standard_sack_count', 25)
            ->assertSet('items.0.has_remnant', true)
            ->assertSet('items.0.remnant_netto_kg', 12.30);
    }

    public function test_admin_can_change_origin_with_cascading_origin_code(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(DnShipmentManagement::class)
            ->call('openCreateModal')
            // Default is Lombok / Lombok'24
            ->assertSet('items.0.origin', 'Lombok')
            ->assertSet('items.0.origin_code', "Lombok'24")
            // Switch origin to Paiton -> should auto-switch origin code to P10T5
            ->call('selectOriginForLot', 0, 'Paiton')
            ->assertSet('items.0.origin', 'Paiton')
            ->assertSet('items.0.origin_code', 'P10T5')
            // Switch origin to Madura -> should auto-switch origin code to M24A
            ->call('selectOriginForLot', 0, 'Madura')
            ->assertSet('items.0.origin', 'Madura')
            ->assertSet('items.0.origin_code', 'M24A');
    }

    public function test_admin_can_select_temanggung_fn405_batch_with_exact_origin_and_code(): void
    {
        $this->actingAs($this->admin);

        $origin = \App\Models\Origin::create(['region_name' => 'TEMANGGUNG FN405']);

        $dn = \App\Models\DeliveryNote::create([
            'dn_number' => 'DN-INBOUND-002',
            'customer_id' => $this->customer->id,
            'delivery_date' => '2026-08-11',
            'status' => 'received',
        ]);

        $batch = \App\Models\Batch::create([
            'batch_code' => 'BCH-2026-0022',
            'delivery_note_id' => $dn->id,
            'date_of_receipt' => '2026-08-11',
            'customer_id' => $this->customer->id,
            'product_type_id' => $this->productType->id,
            'origin_id' => $origin->id,
            'separation_product_sack' => 48,
            'product_tare_per_sack' => 0.70,
            'separation_product_remnant_kg' => 24.30,
            'separation_product_kg' => 2424.30,
            'status' => 'approved',
        ]);

        Livewire::test(DnShipmentManagement::class)
            ->call('openCreateModal')
            ->call('selectBatchForLot', 0, $batch->id)
            ->assertSet('items.0.batch_id', $batch->id)
            ->assertSet('items.0.batch_code', 'BCH-2026-0022')
            ->assertSet('items.0.origin', 'Temanggung')
            ->assertSet('items.0.origin_code', 'FN405')
            ->assertSet('items.0.standard_sack_count', 48)
            ->assertSet('items.0.has_remnant', true)
            ->assertSet('items.0.remnant_netto_kg', 24.30);
    }

    public function test_admin_can_select_multi_origin_batch_with_automatic_3_lots_generation(): void
    {
        $this->actingAs($this->admin);

        $org1 = \App\Models\Origin::create(['region_name' => 'TEMANGGUNG FN504']);
        $org2 = \App\Models\Origin::create(['region_name' => 'KASTURI FN602']);
        $org3 = \App\Models\Origin::create(['region_name' => 'PAITON P10T5']);

        $dn = \App\Models\DeliveryNote::create([
            'dn_number' => 'DN-INBOUND-003',
            'customer_id' => $this->customer->id,
            'delivery_date' => '2026-08-11',
            'status' => 'received',
        ]);

        $batch = \App\Models\Batch::create([
            'batch_code' => 'BCH-2026-0024',
            'delivery_note_id' => $dn->id,
            'date_of_receipt' => '2026-08-11',
            'customer_id' => $this->customer->id,
            'product_type_id' => $this->productType->id,
            'origin_id' => $org1->id,
            'separation_product_sack' => 60,
            'product_tare_per_sack' => 0.70,
            'separation_product_remnant_kg' => 15.00,
            'separation_product_kg' => 3015.00,
            'status' => 'approved',
        ]);

        \App\Models\BatchOrigin::create([
            'batch_id' => $batch->id,
            'origin_id' => $org1->id,
            'allocated_kg' => 1000.00,
            'processed_kg' => 1000.00,
            'remaining_kg' => 0.00,
        ]);
        \App\Models\BatchOrigin::create([
            'batch_id' => $batch->id,
            'origin_id' => $org2->id,
            'allocated_kg' => 1000.00,
            'processed_kg' => 1000.00,
            'remaining_kg' => 0.00,
        ]);
        \App\Models\BatchOrigin::create([
            'batch_id' => $batch->id,
            'origin_id' => $org3->id,
            'allocated_kg' => 1000.00,
            'processed_kg' => 1000.00,
            'remaining_kg' => 0.00,
        ]);

        // Calling selectBatchForLot on Lot #0 should expand into 3 lots!
        Livewire::test(DnShipmentManagement::class)
            ->call('openCreateModal')
            ->call('selectBatchForLot', 0, $batch->id)
            ->assertCount('items', 3)
            // Lot 1
            ->assertSet('items.0.origin', 'Temanggung')
            ->assertSet('items.0.origin_code', 'FN504')
            ->assertSet('items.0.batch_code', 'BCH-2026-0024')
            // Lot 2
            ->assertSet('items.1.origin', 'Kasturi')
            ->assertSet('items.1.origin_code', 'FN602')
            ->assertSet('items.1.batch_code', 'BCH-2026-0024')
            // Lot 3
            ->assertSet('items.2.origin', 'Paiton')
            ->assertSet('items.2.origin_code', 'P10T5')
            ->assertSet('items.2.batch_code', 'BCH-2026-0024');
    }

    public function test_customer_can_view_and_approve_dn_shipment_changing_status_to_approved(): void
    {
        $customerUser = User::factory()->create([
            'role' => 'customer',
            'customer_id' => $this->customer->id,
            'password' => bcrypt('password'),
        ]);

        $shipment = DnShipment::create([
            'dn_number' => 'DNS-2026-APP-01',
            'shipment_date' => '2026-08-11',
            'customer_id' => $this->customer->id,
            'product_type_id' => $this->productType->id,
            'vehicle_number' => 'N 9999 ZZ',
            'driver_name' => 'Pak Joko',
            'destination' => 'Pabrik Malang',
            'status' => 'Shipped',
            'total_sacks' => 10,
            'total_gross_kg' => 507.00,
            'total_tare_kg' => 7.00,
            'total_netto_kg' => 500.00,
            'created_by' => $this->admin->id,
        ]);

        DnShipmentItem::create([
            'dn_shipment_id' => $shipment->id,
            'item_no' => 1,
            'origin' => 'Temanggung',
            'origin_code' => 'FN405',
            'standard_sack_count' => 10,
            'standard_gross_per_sack' => 50.70,
            'standard_tare_per_sack' => 0.70,
            'standard_netto_per_sack' => 50.00,
            'has_remnant' => false,
            'total_sacks' => 10,
            'total_gross_kg' => 507.00,
            'total_tare_kg' => 7.00,
            'total_netto_kg' => 500.00,
        ]);

        $this->actingAs($customerUser);

        // Test customer dashboard displays shipment and can approve it
        Livewire::test(\App\Livewire\Customer\CustomerDashboard::class)
            ->set('activeTab', 'dn_shipments')
            ->assertSee('DNS-2026-APP-01')
            ->assertSee('Pak Joko')
            ->call('openApprovalModal', $shipment->id)
            ->set('approvalNote', 'Barang diterima lengkap 10 karung.')
            ->call('approveShipment', $shipment->id)
            ->assertSet('showApprovalModal', false);

        $shipment->refresh();
        $this->assertEquals('Approved', $shipment->status);
        $this->assertNotNull($shipment->customer_approved_at);
        $this->assertEquals($customerUser->id, $shipment->customer_approved_by_user_id);
        $this->assertEquals('Barang diterima lengkap 10 karung.', $shipment->customer_approval_note);
    }

    public function test_dn_shipment_pdf_has_vertical_sack_table_and_no_removed_signatures(): void
    {
        $shipment = DnShipment::create([
            'dn_number' => 'DNS-2026-PDF-01',
            'shipment_date' => '2026-08-11',
            'customer_id' => $this->customer->id,
            'product_type_id' => $this->productType->id,
            'vehicle_number' => 'N 1234 AB',
            'driver_name' => 'Budi Santoso',
            'destination' => 'Gudang Pusat Surabaya',
            'status' => 'Shipped',
            'total_sacks' => 2,
            'total_gross_kg' => 101.40,
            'total_tare_kg' => 1.40,
            'total_netto_kg' => 100.00,
            'created_by' => $this->admin->id,
        ]);

        DnShipmentItem::create([
            'dn_shipment_id' => $shipment->id,
            'item_no' => 1,
            'origin' => 'Lombok',
            'origin_code' => "Lombok'24",
            'standard_sack_count' => 2,
            'standard_gross_per_sack' => 50.70,
            'standard_tare_per_sack' => 0.70,
            'standard_netto_per_sack' => 50.00,
            'has_remnant' => false,
            'total_sacks' => 2,
            'total_gross_kg' => 101.40,
            'total_tare_kg' => 1.40,
            'total_netto_kg' => 100.00,
        ]);

        $this->actingAs($this->admin);

        $response = $this->get(route('dn-shipments.preview', $shipment->id));
        $response->assertStatus(200);

        // Assert vertical table headers are present
        $response->assertSee('NO. KRUNG');
        $response->assertSee('TIPE KARUNG');
        $response->assertSee('GROSS (KG)');
        $response->assertSee('TARE (KG)');
        $response->assertSee('NETTO (KG)');
        $response->assertSee('KETERANGAN');

        // Assert removed signatures are NOT present
        $response->assertDontSee('Diserahkan Oleh');
        $response->assertDontSee('Pengemudi / Sopir');
        $response->assertDontSee('Diterima Oleh');
        $response->assertDontSee('Penerima Barang / Customer');

        // Assert Dikeluarkan Oleh is present
        $response->assertSee('Dikeluarkan Oleh');
        $response->assertSee('Bagian Pengiriman / Gudang');
    }

    public function test_admin_can_select_material_type_for_product_bits_stem_or_dust(): void
    {
        $origin = \App\Models\Origin::create([
            'region_name' => 'Lombok',
            'code' => 'LBK',
        ]);

        $dn = \App\Models\DeliveryNote::create([
            'dn_number' => 'DN-INBOUND-MAT-01',
            'customer_id' => $this->customer->id,
            'delivery_date' => '2026-08-11',
            'status' => 'received',
        ]);

        $batch = \App\Models\Batch::create([
            'batch_code' => 'BCH-2026-MAT-01',
            'delivery_note_id' => $dn->id,
            'date_of_receipt' => '2026-08-11',
            'customer_id' => $this->customer->id,
            'product_type_id' => $this->productType->id,
            'origin_id' => $origin->id,
            'material_code' => "Lombok'24",
            'separation_product_sack' => 20,
            'separation_product_kg' => 1000.00,
            'separation_bits_stem_kg' => 125.50,
            'separation_dust_kg' => 45.00,
            'status' => 'approved',
        ]);

        $this->actingAs($this->admin);

        // 1. Test selecting Bits / Stem
        Livewire::test(DnShipmentManagement::class)
            ->call('openCreateModal')
            ->set('dn_number', 'DNS-2026-STEM-01')
            ->set('shipment_date', '2026-08-11')
            ->set('customer_id', $this->customer->id)
            ->call('selectBatchForLot', 0, $batch->id)
            ->call('selectMaterialTypeForLot', 0, 'Bits / Stem')
            ->assertSet('items.0.material_type', 'Bits / Stem')
            ->assertSet('items.0.standard_sack_count', 2) // 125.50 / 50 = 2 sacks + 25.50 remnant
            ->assertSet('items.0.has_remnant', true)
            ->assertSet('items.0.remnant_netto_kg', 25.50)
            ->call('saveShipment')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('dn_shipment_items', [
            'origin' => 'Lombok',
            'origin_code' => "Lombok'24",
            'material_type' => 'Bits / Stem',
            'total_netto_kg' => 125.50,
        ]);
    }
}
