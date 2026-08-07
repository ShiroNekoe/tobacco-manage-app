<?php

namespace Tests\Feature;

use App\Livewire\Admin\UserManagement;
use App\Livewire\Auth\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class UserLoginWithoutEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_user_without_email()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        Livewire::actingAs($admin)
            ->test(UserManagement::class)
            ->set('name', 'Karyawan Tanpa Email')
            ->set('email', '')
            ->set('role', 'karyawan')
            ->set('shift', 'Shift 1')
            ->set('group', 'Group A')
            ->set('password', 'password123')
            ->call('saveUser')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'name' => 'Karyawan Tanpa Email',
            'email' => null,
            'role' => 'karyawan',
        ]);
    }

    public function test_karyawan_can_login_using_name_and_password()
    {
        $user = User::create([
            'name' => 'Budi Worker',
            'email' => null,
            'role' => 'karyawan',
            'password' => Hash::make('secret123'),
        ]);

        Livewire::test(Login::class)
            ->set('mode', 'karyawan')
            ->set('name', 'Budi Worker')
            ->set('password', 'secret123')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('karyawan.weighing'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_invalid_password()
    {
        User::create([
            'name' => 'Budi Worker',
            'email' => null,
            'role' => 'karyawan',
            'password' => Hash::make('secret123'),
        ]);

        Livewire::test(Login::class)
            ->set('mode', 'karyawan')
            ->set('name', 'Budi Worker')
            ->set('password', 'wrongpassword')
            ->call('login')
            ->assertHasErrors(['name']);

        $this->assertGuest();
    }
}
