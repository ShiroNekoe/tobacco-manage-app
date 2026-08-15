<?php

namespace Tests\Feature;

use App\Livewire\Admin\StockProduct;
use App\Models\Batch;
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

class StockProductTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $supervisor;
    protected User $karyawan;
    protected Customer $customer;
    protected Origin $origin;
    protected ProductType $productType;
    protected DeliveryNote $deliveryNote;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'password' => bcrypt('password'),
        ]);

        $this->supervisor = User::factory()->create([
            'role' => 'supervisor',
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

        $this->origin = Origin::create([
            'region_name' => 'Lombok',
        ]);

        $this->productType = ProductType::create([
            'code' => 'RAJANGAN',
            'name' => 'Rajangan Tembakau',
        ]);

        $this->deliveryNote = DeliveryNote::create([
            'dn_number' => 'DN-INBOUND-001',
            'customer_id' => $this->customer->id,
            'delivery_date' => '2026-08-11',
            'status' => 'received',
        ]);
    }

    public function test_admin_and_supervisor_can_access_stock_product_page(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.stock'))
            ->assertStatus(200)
            ->assertSee('MANAJEMEN STOCK PRODUK')
            ->assertSee('Sisa Stock Gudang');

        $this->actingAs($this->supervisor)
            ->get(route('admin.stock'))
            ->assertStatus(200)
            ->assertSee('MANAJEMEN STOCK PRODUK');
    }

    public function test_karyawan_cannot_access_stock_product_page(): void
    {
        $this->actingAs($this->karyawan)
            ->get(route('admin.stock'))
            ->assertStatus(403);
    }

    public function test_stock_product_computes_produced_shipped_and_remaining_accurately(): void
    {
        // 1. Create a Batch with production output: 50 sacks, 2500 kg
        $batch = Batch::create([
            'batch_code' => 'B-STOCK-001',
            'delivery_note_id' => $this->deliveryNote->id,
            'customer_id' => $this->customer->id,
            'origin_id' => $this->origin->id,
            'product_type_id' => $this->productType->id,
            'pack_type' => 'Karung',
            'date_of_receipt' => now(),
            'separation_product_sack' => 50,
            'separation_product_kg' => 2500.00,
            'separation_product_gross_kg' => 2535.00,
            'separation_product_tare_kg' => 35.00,
            'status' => 'CLOSED',
        ]);

        // 2. Create a DN Shipment sending 20 sacks, 1000 kg
        $shipment = DnShipment::create([
            'dn_number' => 'SJ-2026-001',
            'shipment_date' => now(),
            'customer_id' => $this->customer->id,
            'total_sacks' => 20,
            'total_gross_kg' => 1014.00,
            'total_tare_kg' => 14.00,
            'total_netto_kg' => 1000.00,
            'status' => 'Shipped',
        ]);

        DnShipmentItem::create([
            'dn_shipment_id' => $shipment->id,
            'batch_id' => $batch->id,
            'batch_code' => $batch->batch_code,
            'item_no' => 1,
            'origin' => 'Lombok',
            'origin_code' => "Lombok'24",
            'material_type' => 'Product',
            'standard_sack_count' => 20,
            'standard_gross_per_sack' => 50.70,
            'standard_tare_per_sack' => 0.70,
            'standard_netto_per_sack' => 50.00,
            'total_sacks' => 20,
            'total_gross_kg' => 1014.00,
            'total_tare_kg' => 14.00,
            'total_netto_kg' => 1000.00,
        ]);

        // 3. Test Stock Metrics Computation
        $stock = StockProduct::computeBatchStock($batch->fresh());

        $this->assertEquals(50, $stock['produced_sacks']);
        $this->assertEquals(2500.00, $stock['produced_netto_kg']);
        $this->assertEquals(20, $stock['shipped_sacks']);
        $this->assertEquals(1000.00, $stock['shipped_netto_kg']);
        $this->assertEquals(30, $stock['remaining_sacks']);
        $this->assertEquals(1500.00, $stock['remaining_netto_kg']);
        $this->assertEquals('partial', $stock['status']);

        // 4. Test Livewire component render
        $this->actingAs($this->admin);
        Livewire::test(StockProduct::class)
            ->assertSee('B-STOCK-001')
            ->assertSee('2.500,00 kg')
            ->assertSee('1.000,00 kg')
            ->assertSee('1.500,00 kg')
            ->assertSee('Sisa Sebagian');
    }

    public function test_stock_product_filtering(): void
    {
        $batchA = Batch::create([
            'batch_code' => 'BATCH-ALPHA',
            'delivery_note_id' => $this->deliveryNote->id,
            'customer_id' => $this->customer->id,
            'origin_id' => $this->origin->id,
            'product_type_id' => $this->productType->id,
            'pack_type' => 'Karung',
            'date_of_receipt' => now(),
            'separation_product_sack' => 10,
            'separation_product_kg' => 500.00,
            'status' => 'CLOSED',
        ]);

        $otherCustomer = Customer::create(['name' => 'PT Sampoerna', 'code' => 'SMP']);
        $dn2 = DeliveryNote::create([
            'dn_number' => 'DN-INBOUND-002',
            'customer_id' => $otherCustomer->id,
            'delivery_date' => '2026-08-11',
            'status' => 'received',
        ]);

        $batchB = Batch::create([
            'batch_code' => 'BATCH-BETA',
            'delivery_note_id' => $dn2->id,
            'customer_id' => $otherCustomer->id,
            'origin_id' => $this->origin->id,
            'product_type_id' => $this->productType->id,
            'pack_type' => 'Bale',
            'date_of_receipt' => now(),
            'separation_product_sack' => 20,
            'separation_product_kg' => 1000.00,
            'status' => 'CLOSED',
        ]);

        $this->actingAs($this->admin);
        Livewire::test(StockProduct::class)
            ->set('search', 'ALPHA')
            ->assertSee('BATCH-ALPHA')
            ->assertDontSee('BATCH-BETA')
            ->set('search', '')
            ->set('filterCustomerId', $otherCustomer->id)
            ->assertSee('BATCH-BETA')
            ->assertDontSee('BATCH-ALPHA');
    }
}
