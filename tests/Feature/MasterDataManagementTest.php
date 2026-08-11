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

    public function test_admin_can_create_edit_delete_material_type(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(MasterDataManagement::class)
            ->call('setTab', 'materials')
            ->assertSet('activeTab', 'materials')
            ->set('material_code', 'STRIP')
            ->set('material_name', 'Strip Tobacco')
            ->set('material_description', 'Tembakau jenis strip utuh')
            ->set('default_sack_weight', 45.00)
            ->set('default_tare_weight', 0.80)
            ->set('is_active', true)
            ->call('saveMaterial')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('material_types', [
            'code' => 'STRIP',
            'name' => 'Strip Tobacco',
            'default_sack_weight' => 45.00,
        ]);

        $mat = \App\Models\MaterialType::where('code', 'STRIP')->first();

        // Test editing
        Livewire::actingAs($admin)
            ->test(MasterDataManagement::class)
            ->call('openMaterialModal', $mat->id)
            ->set('material_name', 'Strip Tobacco Premium')
            ->call('saveMaterial')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('material_types', [
            'id' => $mat->id,
            'name' => 'Strip Tobacco Premium',
        ]);

        // Test deleting
        Livewire::actingAs($admin)
            ->test(MasterDataManagement::class)
            ->call('deleteMaterial', $mat->id);

        $this->assertDatabaseMissing('material_types', [
            'id' => $mat->id,
        ]);
    }

    public function test_category_menu_renders_all_four_categories(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(MasterDataManagement::class)
            ->assertSee('Pengelolaan Master Data')
            ->assertSee('Pelanggan')
            ->assertSee('Jenis Produk')
            ->assertSee('Asal Tembakau')
            ->assertSee('Jenis Muatan');
    }
}
