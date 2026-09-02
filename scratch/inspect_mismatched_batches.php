<?php

require __DIR__ . '/../vendor/autoload.php';

putenv('DB_CONNECTION=sqlite');
putenv('DB_DATABASE=:memory:');

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

\Illuminate\Support\Facades\Artisan::call('migrate:fresh');

$importer = new App\Imports\ProcessingReportImporter();
$importer->import(true);

$batchCodes = ['BCH-2026-0003', 'BCH-2026-0005', 'BCH-2026-0006', 'BCH-2026-0007'];

foreach ($batchCodes as $code) {
    $b = App\Models\Batch::where('batch_code', $code)->first();
    echo "========================================\n";
    echo "BATCH: {$code}\n";
    echo "DN HEADER ROWS:\n";
    foreach ($b->dn_header_details as $idx => $dnRow) {
        echo "  [{$idx}] Origin: {$dnRow['raw_origin']} | Clean: {$dnRow['clean_region']} | PackType: {$dnRow['pack_type']} | Packs: {$dnRow['packs']} | Gross: {$dnRow['gross_kg']} | Tare: {$dnRow['tare_kg']} | Netto: {$dnRow['netto_kg']}\n";
    }
    echo "PARSED SECTIONS:\n";
    foreach ($b->sections_data as $idx => $sec) {
        $sackCount = count($sec['sacks'] ?? []);
        echo "  [{$idx}] Raw: {$sec['raw_origin']} | Clean: {$sec['clean_region']} | PackType: {$sec['pack_type']} | SackCount: {$sackCount}\n";
    }
}
