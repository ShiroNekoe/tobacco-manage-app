<?php

namespace Database\Seeders;

use App\imports\ProcessingReportImporter;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        try {
            $importer = new ProcessingReportImporter();
            $result = $importer->import(false); // reset = false (preserves existing data)
            \Log::info('Seeder completed', $result);
        } catch (\Exception $e) {
            \Log::error('Seeder error: ' . $e->getMessage());
            // Don't throw, just log
        }
    }
}