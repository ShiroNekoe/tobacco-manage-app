<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_karyawan_cannot_access_admin_batch_management(): void
    {
        $karyawan = User::factory()->create(['role' => 'karyawan']);

        $response = $this->actingAs($karyawan)->get('/admin/batches');

        $response->assertStatus(403);
    }

    public function test_admin_can_access_admin_batch_management(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/batches');

        $response->assertStatus(200);
    }

    public function test_both_karyawan_and_admin_can_access_weighing_sheet(): void
    {
        $karyawan = User::factory()->create(['role' => 'karyawan']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($karyawan)->get('/karyawan/weighing')->assertStatus(200);
        $this->actingAs($admin)->get('/karyawan/weighing')->assertStatus(200);
    }
}
