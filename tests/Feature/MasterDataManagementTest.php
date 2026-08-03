<?php

namespace Tests\Feature;

use App\Livewire\Admin\MasterDataManagement;
use App\Models\Customer;
use App\Models\Origin;
use App\Models\ProductType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MasterDataManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_master_data_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/master-data');

        $response->assertStatus(200);
        $response->assertSee('Pengelolaan Master Data');
    }

    public function test_admin_can_create_customer(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(MasterDataManagement::class)
            ->set('customer_code', 'CUST-NEW')
            ->set('customer_name', 'PT. Tembakau Baru')
            ->set('contact_person', 'Budi')
            ->set('phone', '0812345')
            ->set('address', 'Surabaya')
            ->call('saveCustomer')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('customers', [
            'code' => 'CUST-NEW',
            'name' => 'PT. Tembakau Baru',
        ]);
    }

    public function test_admin_can_create_product_type_and_origin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(MasterDataManagement::class)
            ->set('product_code', 'PROD-SPECIAL')
            ->set('product_name', 'PAITON SUPER')
            ->call('saveProduct')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('product_types', ['code' => 'PROD-SPECIAL']);

        Livewire::actingAs($admin)
            ->test(MasterDataManagement::class)
            ->set('region_name', 'MADURA')
            ->call('saveOrigin')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('origins', ['region_name' => 'MADURA']);
    }

    public function test_operator_cannot_access_master_data_page(): void
    {
        $operator = User::factory()->create(['role' => 'operator']);

        $response = $this->actingAs($operator)->get('/admin/master-data');

        $response->assertStatus(403);
    }
}
