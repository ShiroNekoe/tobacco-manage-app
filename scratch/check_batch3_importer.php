<?php

require __DIR__ . '/../vendor/autoload.php';

// Force SQLite in memory for standalone script execution
putenv('DB_CONNECTION=sqlite');
putenv('DB_DATABASE=:memory:');

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Run migrations
\Illuminate\Support\Facades\Artisan::call('migrate:fresh');

$importer = new App\Imports\ProcessingReportImporter();
$result = $importer->import(true);

$b3 = App\Models\Batch::where('batch_code', 'BCH-2026-0003')->first();
echo "=== BATCH 3 (BCH-2026-0003) ===\n";
echo "dn_header_details count: " . count($b3->dn_header_details ?? []) . "\n";
echo json_encode($b3->dn_header_details, JSON_PRETTY_PRINT) . "\n\n";

echo "mrl_header_details count: " . count($b3->mrl_header_details ?? []) . "\n";
echo json_encode($b3->mrl_header_details, JSON_PRETTY_PRINT) . "\n\n";

echo "sections_data count: " . count($b3->sections_data ?? []) . "\n";
foreach ($b3->sections_data as $idx => $sec) {
    echo "Section {$idx}: " . ($sec['raw_origin'] ?? '') . " | clean: " . ($sec['clean_region'] ?? '') . " | sacks count: " . count($sec['sacks'] ?? []) . "\n";
}
