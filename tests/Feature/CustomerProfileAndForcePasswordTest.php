<?php

namespace Tests\Feature;

use App\Livewire\Auth\ForceChangePasswordModal;
use App\Livewire\Auth\Login;
use App\Livewire\Customer\CustomerDashboard;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerProfileAndForcePasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_with_email_mode_as_default_and_tpms_branding(): void
    {
        Livewire::test(Login::class)
            ->assertSet('mode', 'email')
            ->assertSee('Login')
            ->assertSee('Admin TPMS')
            ->assertDontSee('Masuk ke Sistem MES');
    }

    public function test_force_change_password_modal_triggers_for_user_with_must_change_password(): void
    {
        $user = User::factory()->create([
            'email' => 'newuser@tobacco.com',
            'password' => Hash::make('password'),
            'must_change_password' => true,
        ]);

        $this->actingAs($user);

        Livewire::test(ForceChangePasswordModal::class)
            ->assertSet('showModal', true)
            ->set('newPassword', 'NewSecret123')
            ->set('newPasswordConfirmation', 'NewSecret123')
            ->call('updatePassword')
            ->assertSet('showModal', false);

        $user->refresh();
        $this->assertFalse($user->must_change_password);
        $this->assertNotNull($user->password_changed_at);
        $this->assertTrue(Hash::check('NewSecret123', $user->password));
    }

    public function test_customer_can_view_and_update_profile(): void
    {
        $customer = Customer::create([
            'name' => 'PT Mitra Sejahtera Tbk',
            'code' => 'MITRA-01',
            'contact_person' => 'Budi Santoso',
            'phone' => '08123456789',
            'address' => 'Jl. Industri No. 10, Kudus',
        ]);

        $user = User::factory()->create([
            'name' => 'Budi Mitra',
            'email' => 'budi@mitra.com',
            'role' => 'customer',
            'customer_id' => $customer->id,
            'password' => Hash::make('oldpassword'),
        ]);

        $this->actingAs($user);

        Livewire::test(CustomerDashboard::class)
            ->call('setTab', 'profile')
            ->assertSet('activeTab', 'profile')
            ->assertSet('profileName', 'Budi Mitra')
            ->assertSet('profileEmail', 'budi@mitra.com')
            ->assertSet('profileContactPerson', 'Budi Santoso')
            ->set('profileName', 'Budi Santoso Updated')
            ->set('profilePhone', '08987654321')
            ->set('profileAddress', 'Jl. Raya Pabrik Baru No. 99, Kudus')
            ->call('updateProfile')
            ->assertHasNoErrors();

        $user->refresh();
        $customer->refresh();

        $this->assertEquals('Budi Santoso Updated', $user->name);
        $this->assertEquals('08987654321', $customer->phone);
        $this->assertEquals('Jl. Raya Pabrik Baru No. 99, Kudus', $customer->address);
    }

    public function test_customer_can_update_password_from_profile_tab(): void
    {
        $user = User::factory()->create([
            'name' => 'Akun Customer',
            'email' => 'cust@tobacco.com',
            'role' => 'customer',
            'password' => Hash::make('CurrentPassword123'),
        ]);

        $this->actingAs($user);

        Livewire::test(CustomerDashboard::class)
            ->set('activeTab', 'profile')
            ->set('profileCurrentPassword', 'CurrentPassword123')
            ->set('profileNewPassword', 'BrandNewPassword456')
            ->set('profileNewPasswordConfirmation', 'BrandNewPassword456')
            ->call('updatePassword')
            ->assertHasNoErrors();

        $user->refresh();
        $this->assertTrue(Hash::check('BrandNewPassword456', $user->password));
    }

    public function test_traceability_tab_is_not_a_valid_tab_in_customer_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'customer',
        ]);

        $this->actingAs($user);

        Livewire::test(CustomerDashboard::class)
            ->call('setTab', 'traceability')
            ->assertSet('activeTab', 'batch_overview');
    }
}
