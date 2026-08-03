<?php

namespace Tests\Feature;

use App\Livewire\Customer\CustomerDashboard;
use App\Models\Batch;
use App\Models\Customer;
use App\Models\DeliveryNote;
use App\Models\Origin;
use App\Models\ProductType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerPortalPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_open_pdf_preview_modal_on_approved_batch(): void
    {
        $custObj = Customer::create(['name' => 'PT Customer Portal Test', 'code' => 'CUST-PORTAL']);
        $prodType = ProductType::create(['code' => 'P-PORTAL', 'name' => 'PAITON PORTAL']);
        $origin = Origin::create(['region_name' => 'PAITON']);
        $dn = DeliveryNote::create(['dn_number' => 'DN-PORTAL', 'customer_id' => $custObj->id, 'delivery_date' => Carbon::now()]);

        $custUser = User::create([
            'name' => 'Customer Portal User',
            'email' => 'cp@tobacco.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'customer_id' => $custObj->id,
        ]);

        $batch = Batch::create([
            'batch_code' => 'BCH-PORTAL-001',
            'customer_id' => $custObj->id,
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
            'supervisor_approval_status' => 'APPROVED',
        ]);

        Livewire::actingAs($custUser)
            ->test(CustomerDashboard::class)
            ->call('openPreviewModal', $batch->id)
            ->assertSet('showPreviewModal', true)
            ->assertSet('previewBatchId', $batch->id);
    }
}
