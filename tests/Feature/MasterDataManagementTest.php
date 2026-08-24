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

    public function test_admin_can_create_edit_delete_pack_type(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(MasterDataManagement::class)
            ->call('setTab', 'pack_types')
            ->assertSet('activeTab', 'pack_types')
            ->set('pack_type_code', 'CUSTOM-BOX')
            ->set('pack_type_name', 'Custom Heavy Box')
            ->set('pack_type_description', 'Kemasan box kayu khusus')
            ->set('pack_type_is_active', true)
            ->call('savePackType')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('pack_types', [
            'code' => 'CUSTOM-BOX',
            'name' => 'Custom Heavy Box',
        ]);

        $pt = \App\Models\PackType::where('code', 'CUSTOM-BOX')->first();

        // Test editing
        Livewire::actingAs($admin)
            ->test(MasterDataManagement::class)
            ->call('openPackTypeModal', $pt->id)
            ->set('pack_type_name', 'Custom Heavy Box Premium')
            ->call('savePackType')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('pack_types', [
            'id' => $pt->id,
            'name' => 'Custom Heavy Box Premium',
        ]);

        // Test deleting
        Livewire::actingAs($admin)
            ->test(MasterDataManagement::class)
            ->call('deletePackType', $pt->id);

        $this->assertDatabaseMissing('pack_types', [
            'id' => $pt->id,
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

    public function test_admin_can_create_customer_with_email_and_password_for_portal_access(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(MasterDataManagement::class)
            ->set('customer_code', 'CUST-PORTAL')
            ->set('customer_name', 'PT. Mitra Customer Portal')
            ->set('contact_person', 'Pak Agus')
            ->set('phone', '081999888777')
            ->set('address', 'Jakarta')
            ->set('email', 'customer.portal@example.com')
            ->set('password', 'secret1234')
            ->call('saveCustomer')
            ->assertHasNoErrors();

        $customer = Customer::where('code', 'CUST-PORTAL')->first();
        $this->assertNotNull($customer);
        $this->assertEquals('customer.portal@example.com', $customer->email);

        // Verify User Account was created automatically with role = 'customer' and linked customer_id
        $portalUser = User::where('email', 'customer.portal@example.com')->first();
        $this->assertNotNull($portalUser);
        $this->assertEquals('customer', $portalUser->role);
        $this->assertEquals($customer->id, $portalUser->customer_id);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('secret1234', $portalUser->password));
    }

    public function test_admin_can_update_customer_email_and_password(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $customer = Customer::create([
            'code' => 'CUST-EDIT',
            'name' => 'PT. Initial Name',
            'email' => 'old.email@example.com',
        ]);

        $portalUser = User::create([
            'name' => 'PT. Initial Name',
            'email' => 'old.email@example.com',
            'role' => 'customer',
            'customer_id' => $customer->id,
            'password' => \Illuminate\Support\Facades\Hash::make('oldpassword'),
        ]);

        Livewire::actingAs($admin)
            ->test(MasterDataManagement::class)
            ->call('openCustomerModal', $customer->id)
            ->assertSet('email', 'old.email@example.com')
            ->set('customer_name', 'PT. Updated Name')
            ->set('email', 'new.email@example.com')
            ->set('password', 'newsecret5678')
            ->call('saveCustomer')
            ->assertHasNoErrors();

        $customer->refresh();
        $portalUser->refresh();

        $this->assertEquals('new.email@example.com', $customer->email);
        $this->assertEquals('PT. Updated Name', $customer->name);
        $this->assertEquals('new.email@example.com', $portalUser->email);
        $this->assertEquals('PT. Updated Name', $portalUser->name);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('newsecret5678', $portalUser->password));
    }
}

