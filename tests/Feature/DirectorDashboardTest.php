<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DirectorDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_role_can_access_customer_dashboard(): void
    {
        $customerUser = User::create([
            'name' => 'Customer Demo',
            'email' => 'c1@tobacco.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        $this->actingAs($customerUser)->get('/customer/dashboard')->assertStatus(200);
    }

    public function test_karyawan_role_cannot_access_customer_dashboard(): void
    {
        $karyawan = User::create([
            'name' => 'Karyawan Demo',
            'email' => 'k1@tobacco.com',
            'password' => bcrypt('password'),
            'role' => 'karyawan',
        ]);

        $this->actingAs($karyawan)->get('/customer/dashboard')->assertStatus(403);
    }
}
