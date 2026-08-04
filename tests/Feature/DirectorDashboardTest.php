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

    public function test_base_origin_extraction_helper(): void
    {
        $this->assertEquals('KASTURI', \App\Livewire\Customer\CustomerDashboard::extractBaseOrigin('KASTURI FN602'));
        $this->assertEquals('LOMBOK', \App\Livewire\Customer\CustomerDashboard::extractBaseOrigin("LOMBOK '24"));
        $this->assertEquals('LOMBOK', \App\Livewire\Customer\CustomerDashboard::extractBaseOrigin('LOMBOK P9K5'));
        $this->assertEquals('MADURA', \App\Livewire\Customer\CustomerDashboard::extractBaseOrigin("MADURA'25"));
        $this->assertEquals('PAITON', \App\Livewire\Customer\CustomerDashboard::extractBaseOrigin('PAITON P10-5'));
    }
}
