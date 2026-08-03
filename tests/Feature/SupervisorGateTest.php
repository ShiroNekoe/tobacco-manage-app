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

class SupervisorGateTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_download_unapproved_pdf_certificate(): void
    {
        $customerObj = Customer::create(['name' => 'PT Gate', 'code' => 'CUST-GATE']);
        $prodType = ProductType::create(['code' => 'P-GATE', 'name' => 'PAITON GATE']);
        $origin = Origin::create(['region_name' => 'PAITON']);
        $dn = DeliveryNote::create(['dn_number' => 'DN-GATE', 'customer_id' => $customerObj->id, 'delivery_date' => Carbon::now()]);

        $customerUser = User::create([
            'name' => 'Customer User',
            'email' => 'cust@tobacco.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'customer_id' => $customerObj->id,
        ]);

        $batch = Batch::create([
            'batch_code' => 'BCH-GATE-001',
            'customer_id' => $customerObj->id,
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
            'supervisor_approval_status' => 'PENDING', // Unapproved!
        ]);

        // Customer attempt to download unapproved certificate returns 403
        $response = $this->actingAs($customerUser)->get('/certificate/' . $batch->id . '/pdf');
        $response->assertStatus(403);

        // After Supervisor approves, customer gets 200 OK
        $supervisor = User::create([
            'name' => 'Supervisor QC',
            'email' => 'spv@tobacco.com',
            'password' => bcrypt('password'),
            'role' => 'supervisor',
        ]);
        $batch->approveBySupervisor($supervisor);

        $responseApproved = $this->actingAs($customerUser)->get('/certificate/' . $batch->id . '/pdf');
        $responseApproved->assertStatus(200);
    }
}
