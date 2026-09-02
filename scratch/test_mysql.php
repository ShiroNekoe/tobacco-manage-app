<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $count = App\Models\Batch::count();
    echo "MySQL Database Connection OK! Total Batches in DB: {$count}\n";
    $b3 = App\Models\Batch::where('batch_code', 'BCH-2026-0003')->first();
    if ($b3) {
        echo "BCH-2026-0003 in MySQL DB has " . count($b3->dn_header_details ?? []) . " DN header details.\n";
        echo json_encode($b3->dn_header_details, JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "BCH-2026-0003 not found in MySQL DB.\n";
    }
} catch (\Exception $e) {
    echo "MySQL Error: " . $e->getMessage() . "\n";
}
