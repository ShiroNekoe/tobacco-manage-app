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

class OptionalRemarksTest extends TestCase
{
    use RefreshDatabase;

    public function test_custom_remarks_render_in_pdf_sections(): void
    {
        $customer = Customer::create(['name' => 'PT Remark', 'code' => 'CUST-RMK']);
        $prodType = ProductType::create(['code' => 'P-RMK', 'name' => 'PAITON RMK']);
        $origin = Origin::create(['region_name' => 'PAITON']);
        $dn = DeliveryNote::create(['dn_number' => 'DN-RMK', 'customer_id' => $customer->id, 'delivery_date' => Carbon::now()]);

        $admin = User::create([
            'name' => 'Admin MES',
            'email' => 'adm@tobacco.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $batch = Batch::create([
            'batch_code' => 'BCH-REMARK-001',
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
            'status' => 'CLOSED',
            'custom_dn_remark' => 'Kondisi truk basah hujan ringan',
            'custom_mrl_remark' => 'Karung #1 terikat rapi',
            'custom_separation_remark' => 'Produk grade super',
        ]);

        $response = $this->actingAs($admin)->get('/certificate/' . $batch->id);

        $response->assertStatus(200);
        $response->assertSee('Kondisi truk basah hujan ringan');
        $response->assertSee('Karung #1 terikat rapi');
        $response->assertSee('Produk grade super');
    }
}
