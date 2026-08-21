<?php

namespace Database\Seeders;

use App\Imports\ProcessingReportImporter;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        try {
            User::firstOrCreate(
                ['email' => 'itsupport@tobacco.com'],
                [
                    'name' => 'IT Support',
                    'role' => User::ROLE_IT_SUPPORT,
                    'password' => Hash::make('password'),
                    'shift' => 'Shift 1',
                    'group' => 'Group A',
                ]
            );

            $importer = new ProcessingReportImporter();
            $result = $importer->import(false); // reset = false (preserves existing data)
            \Log::info('Seeder completed', $result);
        } catch (\Exception $e) {
            \Log::error('Seeder error: ' . $e->getMessage());
            // Don't throw, just log
        }
    }
}