<?php

require __DIR__ . '/../vendor/autoload.php';

putenv('DB_CONNECTION=sqlite');
putenv('DB_DATABASE=:memory:');

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

\Illuminate\Support\Facades\Artisan::call('migrate:fresh');

$importer = new App\Imports\ProcessingReportImporter();
$result = $importer->import(true);

$batches = App\Models\Batch::orderBy('id')->get();

foreach ($batches as $b) {
    $dnCount = count($b->dn_header_details ?? []);
    $secCount = count($b->sections_data ?? []);
    if ($dnCount !== $secCount) {
        echo "MISMATCH Batch {$b->batch_code}: DN Header has {$dnCount} items, Sections Data has {$secCount} items.\n";
    }
}
