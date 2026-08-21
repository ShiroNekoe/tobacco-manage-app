<?php

namespace Tests\Feature;

use App\Livewire\Admin\BatchManagement;
use App\Models\Batch;
use App\Models\Customer;
use App\Models\DeliveryNote;
use App\Models\Origin;
use App\Models\ProductType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ItSupportRoleTest extends TestCase
{
    use RefreshDatabase;

    protected User $itSupport;
    protected Customer $customer;
    protected ProductType $productType;
    protected Origin $origin;
    protected DeliveryNote $deliveryNote;

    protected function setUp(): void
    {
        parent::setUp();

        $this->itSupport = User::create([
            'name' => 'IT Support',
            'email' => 'itsupport@tobacco.com',
            'role' => 'it_support',
            'password' => bcrypt('password'),
        ]);

        $this->customer = Customer::create([
            'name' => 'PT Test Customer',
            'code' => 'CUST-TEST',
        ]);

        $this->productType = ProductType::create([
            'code' => 'RAJANGAN',
            'name' => 'RAJANGAN',
        ]);

        $this->origin = Origin::create([
            'region_name' => 'LOMBOK',
        ]);

        $this->deliveryNote = DeliveryNote::create([
            'dn_number' => 'DN-TEST-001',
            'customer_id' => $this->customer->id,
            'delivery_date' => now(),
        ]);
    }

    public function test_it_support_can_login_via_email_and_password(): void
    {
        Livewire::test(\App\Livewire\Auth\Login::class)
            ->set('email', 'itsupport@tobacco.com')
            ->set('password', 'password')
            ->call('login')
            ->assertRedirect(route('admin.batches'));

        $this->assertAuthenticatedAs($this->itSupport);
    }

    public function test_demo_login_does_not_show_it_support_button(): void
    {
        $this->get('/login')
            ->assertStatus(200)
            ->assertDontSee('IT Support Demo')
            ->assertSee('Customer Portal')
            ->assertSee('Admin TPMS')
            ->assertSee('Supervisor QC')
            ->assertSee('Karyawan');
    }

    public function test_it_support_has_full_access_to_all_routes(): void
    {
        $this->actingAs($this->itSupport);

        $this->get(route('admin.batches'))->assertStatus(200);
        $this->get(route('admin.stock'))->assertStatus(200);
        $this->get(route('admin.dn-shipments'))->assertStatus(200);
        $this->get(route('admin.tracking'))->assertStatus(200);
        $this->get(route('admin.master-data'))->assertStatus(200);
        $this->get(route('admin.users'))->assertStatus(200);
        $this->get(route('karyawan.weighing'))->assertStatus(200);
        $this->get(route('customer.dashboard'))->assertStatus(200);
    }

    public function test_it_support_can_change_per_page_and_delete_batches_in_batch_management(): void
    {
        $this->actingAs($this->itSupport);

        $batch1 = Batch::create([
            'batch_code' => 'BCH-TEST-001',
            'customer_id' => $this->customer->id,
            'delivery_note_id' => $this->deliveryNote->id,
            'product_type_id' => $this->productType->id,
            'origin_id' => $this->origin->id,
            'date_of_receipt' => now(),
        ]);

        $batch2 = Batch::create([
            'batch_code' => 'BCH-TEST-002',
            'customer_id' => $this->customer->id,
            'delivery_note_id' => $this->deliveryNote->id,
            'product_type_id' => $this->productType->id,
            'origin_id' => $this->origin->id,
            'date_of_receipt' => now(),
        ]);

        Livewire::test(BatchManagement::class)
            ->set('perPage', 25)
            ->assertSet('perPage', 25)
            ->set('selectedBatches', [(string)$batch1->id, (string)$batch2->id])
            ->call('deleteSelectedBatches');

        $this->assertDatabaseMissing('batches', ['id' => $batch1->id]);
        $this->assertDatabaseMissing('batches', ['id' => $batch2->id]);
    }
}
