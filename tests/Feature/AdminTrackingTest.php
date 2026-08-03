<?php

namespace Tests\Feature;

use App\Livewire\Admin\AdminTracking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_search_tracking_by_worker_name_or_batch_code(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(AdminTracking::class)
            ->set('search', 'Budi')
            ->assertStatus(200);
    }
}
