<?php

namespace Database\Seeders;

use App\Imports\ProcessingReportImporter;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $importer = new ProcessingReportImporter();
        $importer->import(true);
    }
}
