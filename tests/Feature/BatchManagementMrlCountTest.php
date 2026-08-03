<?php

namespace Tests\Feature;

use App\Livewire\Admin\BatchManagement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BatchManagementMrlCountTest extends TestCase
{
    use RefreshDatabase;

    public function test_typing_numeric_sack_count_generates_exact_mrl_rows(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(BatchManagement::class)
            ->set('target_sack_count', 32)
            ->assertCount('mrl_items', 32)
            ->set('target_sack_count', '')
            ->assertCount('mrl_items', 32);
    }
}
